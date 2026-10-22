<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\InvestmentAccount;
use App\Models\LedgerAccount;
use App\Models\ReferralCommission;
use App\Models\ReferralWithdrawalRequest;
use App\Models\WithdrawalRequest;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ReferralController extends Controller
{
    public function show(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        $parentIds = [$user->id];
        $levelCounts = [];

        for ($level = 1; $level <= 5; $level++) {
            $members = User::query()->whereIn('referred_by_id', $parentIds)->get(['id']);
            $levelCounts[$level] = $members->count();
            $parentIds = $members->modelKeys();
        }

        $directReferrals = $user->referrals()
            ->select(['id', 'referred_by_id', 'email_verified_at', 'status', 'created_at'])
            ->withExists(['kycSubmissions as kyc_verified' => fn ($query) => $query->where('status', 'approved')])
            ->latest('created_at')
            ->paginate(10);

        $levelIncome = ReferralCommission::query()
            ->where('user_id', $user->id)
            ->selectRaw('level, SUM(amount_usdt) AS income_usdt')
            ->groupBy('level')
            ->pluck('income_usdt', 'level')
            ->map(fn ($amount): float => (float) $amount)
            ->all();
            
        $levelRates = config('referrals.commission_rates', []);
        $earnedReferralIncome = array_sum($levelIncome);
        $reservedReferralIncome = (float) $user->referralWithdrawalRequests()->where('status', '!=', 'rejected')->sum('gross_amount_usdt');
        $availableReferralIncome = max(0, round($earnedReferralIncome - $reservedReferralIncome, 8));
        $kycSubmission = $user->kycSubmissions()->latest('id')->first();
        $maturedInvestments = $user->investmentAccounts()
            ->where('status', 'active')
            ->where('matures_at', '<=', now())
            ->whereDoesntHave('withdrawalRequests', fn ($query) => $query->whereIn('status', ['pending', 'review_approved']))
            ->latest('matures_at')
            ->get();

        return view('account.referrals', [
            'user' => $user,
            'referralUrl' => route('register', ['ref' => $user->referral_code]),
            'directReferrals' => $directReferrals,
            'qualifiedDirectCount' => $user->referrals()->whereNotNull('email_verified_at')->where('status', 'active')->count(),
            'levelCounts' => $levelCounts,
            'levelIncome' => $levelIncome,
            'levelRates' => $levelRates,
            'earnedReferralIncome' => $earnedReferralIncome,
            'availableReferralIncome' => $availableReferralIncome,
            'reservedReferralIncome' => $reservedReferralIncome,
            'withdrawalFeeRate' => (float) config('referrals.withdrawal_fee_rate', 2.5),
            'referralWithdrawals' => $user->referralWithdrawalRequests()->latest()->limit(10)->get(),
            'maturedInvestments' => $maturedInvestments,
            'kycVerified' => $kycSubmission?->status === 'approved',
            'withdrawalWalletAddress' => $kycSubmission?->status === 'approved' ? $kycSubmission->withdrawal_wallet_address : null,
        ]);
    }

    public function requestWithdrawal(Request $request): RedirectResponse
    {
        $type = $request->validate(['withdrawal_type' => ['required', 'in:maturity,referral']])['withdrawal_type'];
        $validated = $request->validate([
            'amount_usdt' => ['required', 'numeric', 'decimal:0,8', 'min:0.00000001'],
            'investment_account_id' => ['required_if:withdrawal_type,maturity', 'nullable', 'integer'],
        ]);
        $user = $request->user();
        $amount = round((float) $validated['amount_usdt'], 8);
        $feeRate = (float) config('referrals.withdrawal_fee_rate', 2.5);
        $fee = round($amount * $feeRate / 100, 8, PHP_ROUND_HALF_UP);
        $net = round($amount - $fee, 8, PHP_ROUND_HALF_UP);

        if ($type === 'maturity') {
            DB::transaction(function () use ($request, $user, $validated, $amount, $feeRate, $fee, $net): void {
                $investment = InvestmentAccount::query()->lockForUpdate()->findOrFail((int) $validated['investment_account_id']);
                abort_unless($investment->user_id === $user->id && $investment->status === 'active', 404);
                if (! $investment->matures_at->isPast()) {
                    throw ValidationException::withMessages(['investment_account_id' => 'මෙම ආයෝජන කාලය තවම කල් පිරී නැහැ.']);
                }
                if ($user->kycSubmissions()->latest('id')->value('status') !== 'approved') {
                    throw ValidationException::withMessages(['withdrawal_type' => 'Withdrawal review එකක් ඉල්ලීමට අනුමත KYC අවශ්‍යයි.']);
                }
                if ($investment->withdrawalRequests()->whereIn('status', ['pending', 'review_approved'])->exists()) {
                    throw ValidationException::withMessages(['investment_account_id' => 'මෙම ආයෝජනයට දැනටමත් withdrawal review එකක් විවෘතයි.']);
                }
                if ($amount < (float) $investment->minimum_withdrawal_usdt || $amount > $investment->estimatedTotal()) {
                    throw ValidationException::withMessages(['amount_usdt' => 'මුදල package අවම සීමාවට නොඅඩු සහ ඇස්තමේන්තුගත එකතුවට නොවැඩි විය යුතුයි.']);
                }
                $user->withdrawalRequests()->create([
                    'investment_account_id' => $investment->id,
                    'amount_usdt' => number_format($amount, 8, '.', ''),
                    'fee_rate' => number_format($feeRate, 2, '.', ''),
                    'fee_amount_usdt' => number_format($fee, 8, '.', ''),
                    'net_amount_usdt' => number_format($net, 8, '.', ''),
                    'network' => 'TRC20',
                    'status' => 'pending',
                ]);
            });

            return redirect()->route('account.referrals')->with('status', 'Maturity withdrawal review ඉල්ලීම යොමු කළා. 2.5% ගාස්තුව අඩු කළ පසු ලැබෙන ඇස්තමේන්තුගත මුදල admin review එකට පෙන්වයි. මෙය payment එකක් නොවේ.');
        }

        DB::transaction(function () use ($request, $user, $amount, $feeRate, $fee, $net): void {
            $lockedUser = User::query()->lockForUpdate()->findOrFail($user->id);
            $kyc = $lockedUser->kycSubmissions()->latest('id')->first();
            if ($kyc?->status !== 'approved' || ! $kyc->withdrawal_wallet_address) {
                throw ValidationException::withMessages(['withdrawal_type' => 'අනුමත KYC සහ TRC20 withdrawal wallet address එකක් අවශ්‍යයි.']);
            }

            $earned = (float) ReferralCommission::query()->where('user_id', $lockedUser->id)->sum('amount_usdt');
            $reserved = (float) ReferralWithdrawalRequest::query()->where('user_id', $lockedUser->id)->where('status', '!=', 'rejected')->sum('gross_amount_usdt');
            $available = max(0, round($earned - $reserved, 8));
            if ($amount > $available) {
                throw ValidationException::withMessages(['amount_usdt' => 'ඉල්ලූ මුදල ලබාගත හැකි referral income ශේෂයට වඩා වැඩියි.']);
            }

            $lockedUser->referralWithdrawalRequests()->create([
                'gross_amount_usdt' => number_format($amount, 8, '.', ''),
                'fee_rate' => number_format($feeRate, 2, '.', ''),
                'fee_amount_usdt' => number_format($fee, 8, '.', ''),
                'net_amount_usdt' => number_format($net, 8, '.', ''),
                'network' => 'TRC20',
                'wallet_address' => $kyc->withdrawal_wallet_address,
                'scheduled_for' => $this->calculateScheduledFriday(),
                'status' => 'pending',
            ]);
        });

        return redirect()->route('account.referrals')->with('status', 'Referral income withdrawal ඉල්ලීම ඊළඟ සිකුරාදා පරිපාලක සමාලෝචනය සඳහා යොමු කළා. 2.5% ගාස්තුව අඩු කළ පසු පෙන්වන මුදල පමණයි ඉල්ලීමේ net මුදල.');
    }

    public function reviewWithdrawal(Request $request, ReferralWithdrawalRequest $withdrawal, \App\Services\AdminMessages\CreateAdminUserMessage $messageCreator): RedirectResponse
    {
        $validated = $request->validate([
            'decision' => ['required', 'in:review_approved,rejected'],
            'review_note' => ['nullable', 'required_if:decision,rejected', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($request, $withdrawal, $validated, $messageCreator): void {
            $locked = ReferralWithdrawalRequest::query()->lockForUpdate()->findOrFail($withdrawal->id);
            abort_unless($locked->status === 'pending', 409, 'මෙම ඉල්ලීම දැනටමත් සමාලෝචනය කර ඇත.');
            if ($validated['decision'] === 'review_approved' && $locked->scheduled_for->isFuture()) {
                throw ValidationException::withMessages(['decision' => 'මෙම ඉල්ලීම නියමිත සිකුරාදා දිනට පෙර සමාලෝචනය කළ නොහැක.']);
            }
            $note = trim((string) ($validated['review_note'] ?? '')) ?: null;
            $locked->update([
                'status' => $validated['decision'],
                'reviewed_by' => $request->user()->id,
                'review_note' => $note,
                'reviewed_at' => now(),
            ]);
            $messageCreator->create([
                'user_id' => $locked->user_id,
                'admin_id' => $request->user()->id,
                'category' => 'action',
                'title' => $validated['decision'] === 'review_approved' ? 'Referral withdrawal reviewed' : 'Referral withdrawal declined',
                'body' => $validated['decision'] === 'review_approved'
                    ? 'Your referral withdrawal request was reviewed. Net amount: '.$locked->net_amount_usdt.' USDT to your saved TRC20 wallet. This review does not send funds; the transfer must be completed separately and recorded.'
                    : 'Your referral withdrawal request was declined. Administrator note: '.($note ?: 'Please contact support.'),
            ]);
        });

        return redirect()->route('admin.referrals.index')->with('status', 'Referral withdrawal review decision saved. No cryptocurrency was sent.');
    }

    public function markWithdrawalPaid(Request $request, ReferralWithdrawalRequest $withdrawal, \App\Services\Ledger\LedgerService $ledger, \App\Services\AdminMessages\CreateAdminUserMessage $messageCreator): RedirectResponse
    {
        $validated = $request->validate(['payout_transaction_id' => ['required', 'string', 'regex:/\A[a-fA-F0-9]{64}\z/']]);
        DB::transaction(function () use ($request, $withdrawal, $validated, $ledger, $messageCreator): void {
            $locked = ReferralWithdrawalRequest::query()->lockForUpdate()->findOrFail($withdrawal->id);
            abort_unless($locked->status === 'review_approved', 409, 'මෙම ඉල්ලීම admin review අනුමත කර තිබිය යුතුයි.');
            $txId = strtolower($validated['payout_transaction_id']);
            $payable = LedgerAccount::query()->where('account_key', 'user:'.$locked->user_id.':referral:USDT')->firstOrFail();
            $asset = LedgerAccount::firstOrCreate(['account_key' => 'tron:asset:USDT'], ['user_id' => null, 'currency' => 'USDT', 'kind' => 'asset']);
            $chargeIncome = LedgerAccount::firstOrCreate(['account_key' => 'platform:income:referral-withdrawal-charges:USDT'], ['user_id' => null, 'currency' => 'USDT', 'kind' => 'income']);

            $ledger->post('referral-withdrawal:'.$locked->id, 'USDT', 'Manually paid referral withdrawal (TRC20)', [
                ['account_id' => $payable->id, 'direction' => 'debit', 'amount' => (string) $locked->gross_amount_usdt],
                ['account_id' => $asset->id, 'direction' => 'credit', 'amount' => (string) $locked->net_amount_usdt],
                ['account_id' => $chargeIncome->id, 'direction' => 'credit', 'amount' => (string) $locked->fee_amount_usdt],
            ]);
            $locked->update(['status' => 'paid', 'payout_transaction_id' => $txId, 'paid_at' => now()]);
            $messageCreator->create([
                'user_id' => $locked->user_id,
                'admin_id' => $request->user()->id,
                'category' => 'action',
                'title' => 'Referral withdrawal marked paid',
                'body' => 'The administrator recorded an external USDT TRC20 transfer of '.$locked->net_amount_usdt.' USDT to your saved wallet. Transaction hash: '.$txId,
            ]);
        });

        return redirect()->route('admin.referrals.index')->with('status', 'External payout and transaction hash recorded.');
    }

    /**
     * Calculate the scheduled Friday based on the current day of the week.
     * Saturday(6) to Wednesday(3) -> Upcoming Friday.
     * Thursday(4) and Friday(5) -> Next week's Friday.
     */
        private function calculateScheduledFriday(): string
    {
        $now = now();
        $dayOfWeek = (int) $now->dayOfWeekIso; // 1=Monday, 7=Sunday

        // Saturday (6), Sunday (7), Monday (1), Tuesday (2), Wednesday (3)
        if (in_array($dayOfWeek, [6, 7, 1, 2, 3], true)) {
            return $now->copy()->next(Carbon::FRIDAY)->toDateString();
        }

        // Thursday (4)
        if ($dayOfWeek === 4) {
            return $now->copy()->next(Carbon::FRIDAY)->addWeek()->toDateString();
        }

        // Friday (5)
        return $now->copy()->next(Carbon::FRIDAY)->toDateString();
    }
}