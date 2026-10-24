<?php

namespace App\Http\Controllers;

use App\Models\InvestmentAccount;
use App\Models\InvestmentDepositClaim;
use App\Models\Trc20DepositObservation;
use App\Models\LedgerAccount;
use App\Models\WithdrawalRequest;
use App\Models\ReferralCommission;
use App\Models\User;
use App\Services\Tron\TronDepositHistory;
use App\Services\Ledger\LedgerService;
use App\Services\AdminMessages\CreateAdminUserMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;
use Illuminate\View\View;

class InvestmentController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $packages = collect(config('investment-preview.packages'));
        $validated = $request->validate([
            'package_name' => ['required', 'string', 'in:'.$packages->pluck('name')->implode(',')],
            'maturity_days' => ['required', 'integer', 'between:30,180'],
        ]);
        $maturityDays = (int) $validated['maturity_days'];
        $package = $packages->firstWhere('name', $validated['package_name']);
        $startedAt = now();
        $reward = min(
            $package['amount'] * $package['daily_rate'] / 100 * $maturityDays,
            $package['amount'] * (config('investment-preview.payout_cap_multiple') - 1),
        );

        $request->user()->investmentAccounts()->create([
            'package_name' => $package['name'],
            'principal_usdt' => $package['amount'],
            'daily_rate' => $package['daily_rate'],
            'maturity_days' => $maturityDays,
            'estimated_reward_usdt' => $reward,
            'minimum_withdrawal_usdt' => $package['minimum_withdrawal'],
            'started_at' => $startedAt,
            'matures_at' => $startedAt->copy()->addDays($maturityDays),
            'status' => 'preview',
        ]);

        return redirect()->route('dashboard')->with('status', 'Investment preview saved. No funds were received or invested.');
    }

    public function requestWithdrawal(Request $request, InvestmentAccount $investment): RedirectResponse
    {
        abort_unless($investment->user_id === $request->user()->id, 404);
        $validated = $request->validate([
            'amount_usdt' => ['required', 'numeric', 'decimal:0,8', 'min:0.00000001'],
        ]);
        $amount = (float) $validated['amount_usdt'];
        DB::transaction(function () use ($request, $investment, $amount): void {
            $locked = InvestmentAccount::query()->lockForUpdate()->findOrFail($investment->id);
            abort_unless($locked->user_id === $request->user()->id && $locked->status === 'active', 409);

            if (! $locked->matures_at->isPast()) {
                throw ValidationException::withMessages(['amount_usdt' => 'A review request can only be submitted after the selected preview period ends.']);
            }
            if ($request->user()->kycSubmissions()->latest('id')->value('status') !== 'approved') {
                throw ValidationException::withMessages(['amount_usdt' => 'Verified identity is required before requesting an administrator review.']);
            }
            if ($locked->withdrawalRequests()->whereIn('status', ['pending', 'review_approved'])->exists()) {
                throw ValidationException::withMessages(['amount_usdt' => 'An open review request already exists for this preview.']);
            }
            if ($amount < (float) $locked->minimum_withdrawal_usdt || $amount > $locked->estimatedTotal()) {
                throw ValidationException::withMessages(['amount_usdt' => 'The amount must be at least the package minimum and no greater than its estimate.']);
            }

            $request->user()->withdrawalRequests()->create([
                'investment_account_id' => $locked->id,
                'amount_usdt' => number_format($amount, 8, '.', ''),
                'network' => 'TRC20',
                'status' => 'pending',
            ]);
        });

        return redirect()->route('dashboard')->with('status', 'Review request submitted. Approval does not send funds; transfers are not connected.');
    }

    public function adminIndex(): View
    {
        return view('admin.investments', [
            'investments' => InvestmentAccount::with('user:id,name,email')->latest()->paginate(25),
            'depositClaims' => InvestmentDepositClaim::with(['user:id,name,email', 'investmentAccount:id,package_name,principal_usdt,maturity_days', 'observation:id,transaction_id,source_address,destination_address,amount,provider_status,occurred_at', 'reviewer:id,name'])->latest()->paginate(25, ['*'], 'deposits_page'),
            'withdrawals' => WithdrawalRequest::with(['user:id,name,email', 'investmentAccount:id,package_name,principal_usdt,matures_at', 'reviewer:id,name'])->latest()->paginate(25, ['*'], 'withdrawals_page'),
            'pendingWithdrawals' => WithdrawalRequest::where('status', 'pending')->count(),
            'pendingDeposits' => InvestmentDepositClaim::where('status', 'pending')->count(),
        ]);
    }

    public function depositIndex(): View
    {
        $configured = (bool) config('services.tron.deposit_sync_enabled')
            && (string) config('services.tron.api_key') !== ''
            && (string) config('services.tron.deposit_address') !== '';

        return view('admin.deposits', [
            'configured' => $configured,
            'investmentDepositsEnabled' => (bool) config('services.tron.investment_deposits_enabled'),
            'depositAddress' => (string) config('services.tron.deposit_address'),
            'depositClaims' => InvestmentDepositClaim::with(['user:id,name,email', 'investmentAccount:id,package_name,principal_usdt,maturity_days', 'observation:id,transaction_id,source_address,destination_address,amount,provider_status,occurred_at', 'reviewer:id,name'])->latest()->paginate(25),
            'pendingDeposits' => InvestmentDepositClaim::where('status', 'pending')->count(),
            'deposits' => Trc20DepositObservation::withCount('investmentDepositClaims')->latest('occurred_at')->paginate(30),
        ]);
    }

    public function requestDeposit(Request $request, InvestmentAccount $investment): RedirectResponse
    {
        abort_unless($investment->user_id === $request->user()->id, 404);
        $address = (string) config('services.tron.deposit_address');
        if (! config('services.tron.investment_deposits_enabled') || $address === '') {
            return back()->withErrors(['transaction_id' => 'USDT TRC20 deposits are not enabled yet. Do not transfer funds until the verified address is displayed here.']);
        }

        $validated = $request->validate([
            'transaction_id' => ['required', 'string', 'regex:/\A[a-fA-F0-9]{64}\z/'],
            'source_address' => ['required', 'string', 'regex:/\AT[1-9A-HJ-NP-Za-km-z]{33}\z/'],
            'receipt' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:520'],
        ]);
        $transactionId = strtolower($validated['transaction_id']);
        $sourceAddress = $validated['source_address'];
        $receiptPath = $request->file('receipt')?->store('deposit-receipts/'.$request->user()->id, 'private');

        try {
            DB::transaction(function () use ($request, $investment, $transactionId, $sourceAddress, $receiptPath): void {
                $locked = InvestmentAccount::query()->lockForUpdate()->findOrFail($investment->id);
                abort_unless($locked->user_id === $request->user()->id && $locked->status === 'preview', 409);

                if ($locked->depositClaims()->where('status', 'pending')->exists()) {
                    throw ValidationException::withMessages(['transaction_id' => 'A deposit review is already pending for this saved plan.']);
                }

                $locked->depositClaims()->create([
                    'user_id' => $request->user()->id,
                    'transaction_id' => $transactionId,
                    'source_address' => $sourceAddress,
                    'receipt_path' => $receiptPath,
                    'amount_usdt' => number_format((float) $locked->principal_usdt, 8, '.', ''),
                    'network' => 'TRC20',
                    'status' => 'pending',
                ]);
                $locked->update(['status' => 'awaiting_deposit_review']);
            });
        } catch (Throwable $exception) {
            if ($receiptPath) {
                Storage::disk('private')->delete($receiptPath);
            }

            throw $exception;
        }

        return redirect()->route('dashboard')->with('status', 'Deposit transaction submitted for administrator review. Your plan starts only after the deposit is verified.');
    }

    public function depositReceipt(InvestmentDepositClaim $claim)
    {
        abort_unless($claim->receipt_path && Storage::disk('private')->exists($claim->receipt_path), 404);

        return Storage::disk('private')->response($claim->receipt_path, 'deposit-receipt', [
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ], 'inline');
    }

    public function reviewDeposit(Request $request, InvestmentDepositClaim $claim, LedgerService $ledger, CreateAdminUserMessage $messageCreator): RedirectResponse
    {
        $validated = $request->validate([
            'decision' => ['required', 'in:approved,rejected'],
            'review_note' => ['required', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($request, $claim, $validated, $ledger, $messageCreator): void {
            $lockedClaim = InvestmentDepositClaim::query()->lockForUpdate()->findOrFail($claim->id);
            abort_unless($lockedClaim->status === 'pending', 409, 'This deposit claim has already been reviewed.');
            $investment = InvestmentAccount::query()->lockForUpdate()->findOrFail($lockedClaim->investment_account_id);

            if ($validated['decision'] === 'rejected') {
                $lockedClaim->forceFill([
                    'status' => 'rejected',
                    'reviewed_by' => $request->user()->id,
                    'review_note' => trim($validated['review_note']),
                    'reviewed_at' => now(),
                ])->save();
                if ($investment->status === 'awaiting_deposit_review') {
                    $investment->update(['status' => 'preview']);
                }
                $messageCreator->create([
                    'user_id' => $lockedClaim->user_id,
                    'admin_id' => $request->user()->id,
                    'category' => 'action',
                    'title' => 'Deposit review update',
                    'body' => 'Your deposit claim for the '.$investment->package_name.' plan was not approved. Review note: '.trim($validated['review_note']),
                ]);
                return;
            }

            $observation = Trc20DepositObservation::query()
                ->whereRaw('LOWER(transaction_id) = ?', [$lockedClaim->transaction_id])
                ->where('coin', 'USDT')
                ->where('network', 'TRC20')
                ->where('provider_status', 'confirmed')
                ->where('destination_address', (string) config('services.tron.deposit_address'))
                ->lockForUpdate()
                ->first();

            if (! $observation || (string) $observation->amount !== (string) $lockedClaim->amount_usdt) {
                throw ValidationException::withMessages(['decision' => 'No confirmed USDT TRC20 transfer matches this transaction hash and exact package amount. Sync the TRON history and verify the claim before approving.']);
            }
            if (! $observation->source_address || ! hash_equals($observation->source_address, $lockedClaim->source_address)) {
                throw ValidationException::withMessages(['decision' => 'The sender address does not match the confirmed TRON transfer.']);
            }
            if (InvestmentDepositClaim::query()->where('trc20_deposit_observation_id', $observation->id)->exists()
                || InvestmentDepositClaim::query()->where('transaction_id', $lockedClaim->transaction_id)->where('status', 'approved')->exists()) {
                throw ValidationException::withMessages(['decision' => 'This transfer has already been attributed to an account.']);
            }
            abort_unless($investment->status === 'awaiting_deposit_review', 409);

            $platformAsset = LedgerAccount::firstOrCreate(
                ['account_key' => 'tron:asset:USDT'],
                ['user_id' => null, 'currency' => 'USDT', 'kind' => 'asset'],
            );
            $userInvestment = LedgerAccount::firstOrCreate(
                ['account_key' => 'user:'.$lockedClaim->user_id.':investment:USDT'],
                ['user_id' => $lockedClaim->user_id, 'currency' => 'USDT', 'kind' => 'investment_principal'],
            );
            $ledger->post(
                'trc20-deposit:'.$observation->transaction_id,
                'USDT',
                'Verified USDT TRC20 investment deposit',
                [
                    ['account_id' => $platformAsset->id, 'direction' => 'debit', 'amount' => (string) $observation->amount],
                    ['account_id' => $userInvestment->id, 'direction' => 'credit', 'amount' => (string) $observation->amount],
                ],
            );

            $startedAt = now();
            $investment->update([
                'status' => 'active',
                'started_at' => $startedAt,
                'matures_at' => $startedAt->copy()->addDays((int) $investment->maturity_days),
            ]);
            $lockedClaim->forceFill([
                'status' => 'approved',
                'trc20_deposit_observation_id' => $observation->id,
                'reviewed_by' => $request->user()->id,
                'review_note' => trim($validated['review_note']),
                'reviewed_at' => now(),
            ])->save();
            $this->accrueReferralCommissions($lockedClaim, $ledger);
            $messageCreator->create([
                'user_id' => $lockedClaim->user_id,
                'admin_id' => $request->user()->id,
                'category' => 'action',
                'title' => 'Deposit verified; plan activated',
                'body' => 'Your '.$investment->package_name.' plan deposit was verified. The selected period ends on '.$investment->matures_at->format('M j, Y').'. Administrator note: '.trim($validated['review_note']),
            ]);

            // Send Email to User
            try {
                \Illuminate\Support\Facades\Mail::to($lockedClaim->user->email)->send(
                    new \App\Mail\AccountNotification(
                        'Deposit Approved',
                        'Great news! Your deposit of ' . $lockedClaim->amount_usdt . ' USDT has been approved and your investment is now active.',
                        $lockedClaim->user->name
                    )
                );
            } catch (\Exception $e) {
                // Email යැවීමේදී ප්‍රශ්නයක් ආවත්, අනිත් දේවල් වැඩ කරන්න ඕන නිසා
                \Log::error('Email sending failed: ' . $e->getMessage());
            }
        });

        return redirect()->route('admin.deposits.index')->with('status', 'Deposit review decision recorded. Approved deposits are posted once to the USDT ledger and start the selected preview period.');
    }

    private function accrueReferralCommissions(InvestmentDepositClaim $claim, LedgerService $ledger): void
    {
        $rates = config('referrals.commission_rates', []);
        $referrerId = User::query()->whereKey($claim->user_id)->value('referred_by_id');
        $visited = [(int) $claim->user_id => true];

        for ($level = 1; $level <= 5 && $referrerId; $level++) {
            $referrerId = (int) $referrerId;
            if (isset($visited[$referrerId])) {
                break;
            }
            $visited[$referrerId] = true;
            $rate = (float) ($rates[$level] ?? 0);
            $commission = number_format(round((float) $claim->amount_usdt * $rate / 100, 8, PHP_ROUND_HALF_UP), 8, '.', '');

            if ($rate > 0 && (float) $commission > 0) {
                $expense = LedgerAccount::firstOrCreate(
                    ['account_key' => 'platform:expense:referral-commission:USDT'],
                    ['user_id' => null, 'currency' => 'USDT', 'kind' => 'expense'],
                );
                $payable = LedgerAccount::firstOrCreate(
                    ['account_key' => 'user:'.$referrerId.':referral:USDT'],
                    ['user_id' => $referrerId, 'currency' => 'USDT', 'kind' => 'referral_payable'],
                );
                $transaction = $ledger->post(
                    'referral-commission:deposit-claim:'.$claim->id.':level:'.$level,
                    'USDT',
                    'Accrued level '.$level.' referral commission',
                    [
                        ['account_id' => $expense->id, 'direction' => 'debit', 'amount' => $commission],
                        ['account_id' => $payable->id, 'direction' => 'credit', 'amount' => $commission],
                    ],
                );

                ReferralCommission::create([
                    'user_id' => $referrerId,
                    'referred_user_id' => $claim->user_id,
                    'investment_deposit_claim_id' => $claim->id,
                    'ledger_transaction_id' => $transaction->id,
                    'level' => $level,
                    'commission_rate' => number_format($rate, 2, '.', ''),
                    'base_amount_usdt' => $claim->amount_usdt,
                    'amount_usdt' => $commission,
                ]);
            }

            $referrerId = User::query()->whereKey($referrerId)->value('referred_by_id');
        }
    }

    public function syncDeposits(TronDepositHistory $history): RedirectResponse
    {
        if (! config('services.tron.deposit_sync_enabled') || ! config('services.tron.api_key') || ! config('services.tron.deposit_address')) {
            return back()->withErrors(['sync' => 'TRON deposit history lookup is not configured. Add the TronGrid key and destination address, then enable history sync.']);
        }

        try {
            $count = $history->syncRecent();
        } catch (Throwable) {
            return back()->withErrors(['sync' => 'TRON deposit history could not be refreshed. Check the TronGrid key, address, and network connection.']);
        }

        return redirect()->route('admin.deposits.index')->with('status', $count.' confirmed USDT TRC20 transfers checked. No ledger entry was created.');
    }

    public function reviewWithdrawal(Request $request, WithdrawalRequest $withdrawal, CreateAdminUserMessage $messageCreator): RedirectResponse
    {
        $validated = $request->validate([
            'decision' => ['required', 'in:review_approved,rejected'],
            'review_note' => ['required', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($request, $withdrawal, $validated, $messageCreator): void {
            $locked = WithdrawalRequest::query()->lockForUpdate()->findOrFail($withdrawal->id);
            abort_unless($locked->status === 'pending', 409, 'This request has already been reviewed.');
            $locked->forceFill([
                'status' => $validated['decision'],
                'reviewed_by' => $request->user()->id,
                'review_note' => trim((string) ($validated['review_note'] ?? '')) ?: null,
                'reviewed_at' => now(),
            ])->save();
            $messageCreator->create([
                'user_id' => $locked->user_id,
                'admin_id' => $request->user()->id,
                'category' => 'action',
                'title' => $validated['decision'] === 'rejected' ? 'Withdrawal review update' : 'Withdrawal request reviewed',
                'body' => 'Your withdrawal review request for '.number_format((float) $locked->amount_usdt, 2).' USDT was '.str_replace('_', ' ', $validated['decision']).'. No funds were sent. Administrator note: '.(trim((string) $validated['review_note']) ?: 'No additional note.'),
            ]);
        });

        return redirect()->route('admin.investments.index')->with('status', 'The review decision was recorded. No payment was made.');
    }
}