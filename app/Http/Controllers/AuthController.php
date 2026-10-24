<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $request->merge(['email' => Str::lower(trim((string) $request->input('email')))]);

        $credentials = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string'],
            'remember' => ['sometimes', 'boolean'],
        ]);

        if (! Auth::attempt(
            ['email' => $credentials['email'], 'password' => $credentials['password']],
            $request->boolean('remember'),
        )) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        if ($request->user()->status !== 'active') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function showRegistration(): View
    {
        $requestedCode = trim((string) request()->query('ref', ''));
        $referrer = $requestedCode === '' ? null : User::query()->where('referral_code', $requestedCode)->first();

        return view('auth.register', [
            'referralCode' => $referrer?->referral_code,
            'invalidReferralLink' => $requestedCode !== '' && $referrer === null,
        ]);
    }

    public function register(Request $request): RedirectResponse
    {
        $request->merge(['email' => Str::lower(trim((string) $request->input('email')))]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', PasswordRule::min(12)],
            'terms' => ['accepted'],
            'referral_code' => ['nullable', 'string', 'max:16', 'exists:users,referral_code'],
        ]);

        $user = DB::transaction(function () use ($validated): User {
            $referrer = null;

            if (! empty($validated['referral_code'])) {
                $referrer = User::query()
                    ->where('referral_code', $validated['referral_code'])
                    ->lockForUpdate()
                    ->first();

                if (! $referrer || Str::lower($referrer->email) === $validated['email']) {
                    throw ValidationException::withMessages(['referral_code' => 'This referral code is not valid.']);
                }
            }

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
            ]);
            $user->forceFill([
                'terms_accepted_at' => now(),
                'referred_by_id' => $referrer?->id,
            ])->save();

            return $user;
        });

        event(new Registered($user));
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('verification.notice');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    public function showForgotPassword(): View
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $status = Password::sendResetLink($validated);

        return back()->with($status === Password::RESET_LINK_SENT ? 'status' : 'error', __($status));
    }

    public function showResetPassword(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'confirmed', PasswordRule::min(12)],
        ]);

        $status = Password::reset($validated, function (User $user, string $password): void {
            $user->forceFill([
                'password' => Hash::make($password),
                'remember_token' => Str::random(60),
            ])->save();

            event(new PasswordReset($user));
        });

        if ($status !== Password::PASSWORD_RESET) {
            return back()->withErrors(['email' => __($status)])->onlyInput('email');
        }

        return redirect()->route('login')->with('status', __($status));
    }

    public function verificationNotice(Request $request): View|RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }

        return view('auth.verify-email');
    }

    public function verifyEmail(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }

        $request->fulfill();

        return redirect()->route('dashboard')->with('status', 'Email verified.');
    }

    public function resendVerification(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'If email delivery is configured, a verification message has been queued for your address.');
    }

    public function dashboard(Request $request): View
    {
        $user = $request->user();
        $kycSubmission = $user->kycSubmissions()->latest('id')->first();

        // අලුත් Daily Interest ගණනය කිරීම්
        $totalDailyInterest = \App\Models\DailyInterestAccrual::where('user_id', $user->id)->sum('amount_usdt');
        $todayInterest = \App\Models\DailyInterestAccrual::where('user_id', $user->id)->where('accrued_date', now()->toDateString())->sum('amount_usdt');

        // පසුගිය දින 7 ට අදාළ Interest Income
        $interestChartData = [];
        $referralChartData = [];
        $chartLabels = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $chartLabels[] = $date->format('M j'); // උදා: Oct 23

            // එදිනට අදාළ Interest Income එක
            $dailyInterest = \App\Models\DailyInterestAccrual::where('user_id', $user->id)
                ->whereDate('accrued_date', $date)
                ->sum('amount_usdt');
            $interestChartData[] = (float) $dailyInterest;

            // එදිනට අදාළ Referral Income එක
            $dailyReferral = \App\Models\ReferralCommission::where('user_id', $user->id)
                ->whereDate('created_at', $date)
                ->sum('amount_usdt');
            $referralChartData[] = (float) $dailyReferral;
        }

        return view('dashboard', [
            'kycSubmission' => $kycSubmission,
            'kycVerified' => $kycSubmission?->status === 'approved',
            'adminMessages' => $user->adminMessages()->with('admin:id,name')->latest()->limit(20)->get(),
            'unreadAdminMessages' => $user->adminMessages()->whereNull('read_at')->count(),
            'referralCount' => $user->referrals()->count(),
            'investments' => $user->investmentAccounts()->with(['withdrawalRequests', 'depositClaims'])->latest()->get(),
            'packages' => config('investment-preview.packages'),
            'payoutCapMultiple' => config('investment-preview.payout_cap_multiple'),
            'depositAddress' => (string) config('services.tron.deposit_address'),
            'depositFlowReady' => (bool) config('services.tron.investment_deposits_enabled')
                && (bool) config('services.tron.deposit_sync_enabled')
                && (string) config('services.tron.api_key') !== ''
                && (string) config('services.tron.deposit_address') !== '',
            'totalDailyInterest' => $totalDailyInterest,
            'todayInterest' => $todayInterest,
            'chartLabels' => $chartLabels,
            'interestChartData' => $interestChartData,
            'referralChartData' => $referralChartData,
        ]);
    }

    // ==========================================
    // WALLETS PAGE (NEW FEATURE)
    // ==========================================
    public function wallets(Request $request): View
    {
        $user = $request->user();
        
        $referralAccount = \App\Models\LedgerAccount::where('account_key', 'user:'.$user->id.':referral:USDT')->first();
        $maturityAccount = \App\Models\LedgerAccount::where('account_key', 'user:'.$user->id.':investment:USDT')->first();
        $bonusAccount = \App\Models\LedgerAccount::where('account_key', 'user:'.$user->id.':bonus:USDT')->first();

        $referralBalance = 0;
        if ($referralAccount) {
            $credits = $referralAccount->entries()->where('direction', 'credit')->sum('amount');
            $debits = $referralAccount->entries()->where('direction', 'debit')->sum('amount');
            $referralBalance = $credits - $debits;
        }

        $maturityBalance = 0;
        if ($maturityAccount) {
            $credits = $maturityAccount->entries()->where('direction', 'credit')->sum('amount');
            $debits = $maturityAccount->entries()->where('direction', 'debit')->sum('amount');
            $maturityBalance = $credits - $debits;
        }

        $bonusBalance = 0;
        if ($bonusAccount) {
            $credits = $bonusAccount->entries()->where('direction', 'credit')->sum('amount');
            $debits = $bonusAccount->entries()->where('direction', 'debit')->sum('amount');
            $bonusBalance = $credits - $debits;
        }

        return view('account.wallets', [
            'referralBalance' => $referralBalance,
            'maturityBalance' => $maturityBalance,
            'bonusBalance' => $bonusBalance,
        ]);
    }

    // 1. Maturity Withdraw (ඉල්ලීමක් යැවීම)
    public function requestMaturityWithdrawal(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:10'], // අවම 10 USDT
        ]);

        $user = $request->user();
        $maturityAccount = \App\Models\LedgerAccount::where('account_key', 'user:'.$user->id.':investment:USDT')->first();
        
        if (!$maturityAccount) {
            return back()->withErrors(['amount' => 'Maturity account not found.']);
        }

        $credits = $maturityAccount->entries()->where('direction', 'credit')->sum('amount');
        $debits = $maturityAccount->entries()->where('direction', 'debit')->sum('amount');
        $balance = $credits - $debits;

        if ($validated['amount'] > $balance) {
            return back()->withErrors(['amount' => 'Insufficient maturity balance.']);
        }

        // අලුත් Withdrawal Request එක Database එකට දානවා
        \App\Models\MaturityWithdrawalRequest::create([
            'user_id' => $user->id,
            'amount_usdt' => $validated['amount'],
            'status' => 'pending',
        ]);

        return back()->with('status', 'Maturity withdrawal request for ' . $validated['amount'] . ' USDT submitted for admin review.');
    }

    // 2. Maturity Reinvest (නැවත Invest කිරීම)
    public function reinvestMaturity(Request $request, \App\Services\Ledger\LedgerService $ledger): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:10'],
            'package' => ['required', 'string'],
            'days' => ['required', 'integer', 'min:1'],
        ]);

        $user = $request->user();
        $maturityAccount = \App\Models\LedgerAccount::where('account_key', 'user:'.$user->id.':investment:USDT')->first();
        
        if (!$maturityAccount) {
            return back()->withErrors(['amount' => 'Maturity account not found.']);
        }

        $credits = $maturityAccount->entries()->where('direction', 'credit')->sum('amount');
        $debits = $maturityAccount->entries()->where('direction', 'debit')->sum('amount');
        $balance = $credits - $debits;

        if ($validated['amount'] > $balance) {
            return back()->withErrors(['amount' => 'Insufficient maturity balance.']);
        }

        DB::transaction(function () use ($user, $validated, $maturityAccount, $ledger) {
            // 1. ලෙජර් එකෙන් සල්ලි අඩු කිරීම (Debit)
            $platformAccount = \App\Models\LedgerAccount::firstOrCreate(
                ['account_key' => 'platform:reinvestments:USDT'],
                ['user_id' => null, 'currency' => 'USDT', 'kind' => 'income']
            );

            $ledger->post(
                'reinvest:'.uniqid(),
                'USDT',
                'Reinvested ' . $validated['amount'] . ' USDT into ' . $validated['package'],
                [
                    ['account_id' => $maturityAccount->id, 'direction' => 'debit', 'amount' => (string) $validated['amount']],
                    ['account_id' => $platformAccount->id, 'direction' => 'credit', 'amount' => (string) $validated['amount']],
                ]
            );

            // 2. අලුත් Investment Account එකක් හදනවා (සියලුම අවශ්‍ය දේවල් සමඟ)
            $packageConfig = collect(config('investment-preview.packages'))->firstWhere('name', $validated['package']);
            $dailyRate = $packageConfig ? $packageConfig['daily_rate'] : 1.5;
            $minWithdrawal = $packageConfig ? $packageConfig['minimum_withdrawal'] : 10;
            $estimatedReward = ($validated['amount'] * $dailyRate * (int) $validated['days']) / 100;

            \App\Models\InvestmentAccount::create([
                'user_id' => $user->id,
                'package_name' => $validated['package'],
                'principal_usdt' => $validated['amount'],
                'maturity_days' => (int) $validated['days'],
                'daily_rate' => $dailyRate,
                'estimated_reward_usdt' => $estimatedReward,
                'minimum_withdrawal_usdt' => $minWithdrawal,
                'started_at' => now(),
                'status' => 'active',
                'matures_at' => now()->addDays((int) $validated['days']),
            ]);

            // 3. Balance එක Update කරනවා
            $newCredits = $maturityAccount->entries()->where('direction', 'credit')->sum('amount');
            $newDebits = $maturityAccount->entries()->where('direction', 'debit')->sum('amount');
            $maturityAccount->update(['balance' => $newCredits - $newDebits]);
        });

        return back()->with('status', 'Successfully reinvested ' . $validated['amount'] . ' USDT into ' . $validated['package'] . '.');
    }

    // 3. Bonus Invest (Bonus සල්ලි Invest කිරීම)
    public function investBonus(Request $request, \App\Services\Ledger\LedgerService $ledger): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:10'],
            'package' => ['required', 'string'],
            'days' => ['required', 'integer', 'min:1'],
        ]);

        $user = $request->user();
        $bonusAccount = \App\Models\LedgerAccount::where('account_key', 'user:'.$user->id.':bonus:USDT')->first();
        
        if (!$bonusAccount) {
            return back()->withErrors(['amount' => 'Bonus account not found.']);
        }

        $credits = $bonusAccount->entries()->where('direction', 'credit')->sum('amount');
        $debits = $bonusAccount->entries()->where('direction', 'debit')->sum('amount');
        $balance = $credits - $debits;

        if ($validated['amount'] > $balance) {
            return back()->withErrors(['amount' => 'Insufficient bonus balance.']);
        }

        DB::transaction(function () use ($user, $validated, $bonusAccount, $ledger) {
            // 1. ලෙජර් එකෙන් Bonus සල්ලි අඩු කිරීම (Debit)
            $platformAccount = \App\Models\LedgerAccount::firstOrCreate(
                ['account_key' => 'platform:bonus-investments:USDT'],
                ['user_id' => null, 'currency' => 'USDT', 'kind' => 'income']
            );

            $ledger->post(
                'bonus-invest:'.uniqid(),
                'USDT',
                'Invested Bonus ' . $validated['amount'] . ' USDT into ' . $validated['package'],
                [
                    ['account_id' => $bonusAccount->id, 'direction' => 'debit', 'amount' => (string) $validated['amount']],
                    ['account_id' => $platformAccount->id, 'direction' => 'credit', 'amount' => (string) $validated['amount']],
                ]
            );

            // 2. අලුත් Investment Account එකක් හදනවා (සියලුම අවශ්‍ය දේවල් සමඟ)
            $packageConfig = collect(config('investment-preview.packages'))->firstWhere('name', $validated['package']);
            $dailyRate = $packageConfig ? $packageConfig['daily_rate'] : 1.5;
            $minWithdrawal = $packageConfig ? $packageConfig['minimum_withdrawal'] : 10;
            $estimatedReward = ($validated['amount'] * $dailyRate * (int) $validated['days']) / 100;

            \App\Models\InvestmentAccount::create([
                'user_id' => $user->id,
                'package_name' => $validated['package'],
                'principal_usdt' => $validated['amount'],
                'maturity_days' => (int) $validated['days'],
                'daily_rate' => $dailyRate,
                'estimated_reward_usdt' => $estimatedReward,
                'minimum_withdrawal_usdt' => $minWithdrawal,
                'started_at' => now(),
                'status' => 'active',
                'matures_at' => now()->addDays((int) $validated['days']),
            ]);

            // 3. Balance එක Update කරනවා
            $newCredits = $bonusAccount->entries()->where('direction', 'credit')->sum('amount');
            $newDebits = $bonusAccount->entries()->where('direction', 'debit')->sum('amount');
            $bonusAccount->update(['balance' => $newCredits - $newDebits]);
        });

        return back()->with('status', 'Successfully invested ' . $validated['amount'] . ' USDT bonus into ' . $validated['package'] . '.');
    }

    // 4. Referral Reinvest (Referral සල්ලි නැවත Invest කිරීම)
    public function reinvestReferral(Request $request, \App\Services\Ledger\LedgerService $ledger): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:10'],
            'package' => ['required', 'string'],
            'days' => ['required', 'integer', 'min:1'],
        ]);

        $user = $request->user();
        $referralAccount = \App\Models\LedgerAccount::where('account_key', 'user:'.$user->id.':referral:USDT')->first();
        
        if (!$referralAccount) {
            return back()->withErrors(['amount' => 'Referral account not found.']);
        }

        $credits = $referralAccount->entries()->where('direction', 'credit')->sum('amount');
        $debits = $referralAccount->entries()->where('direction', 'debit')->sum('amount');
        $balance = $credits - $debits;

        if ($validated['amount'] > $balance) {
            return back()->withErrors(['amount' => 'Insufficient referral balance.']);
        }

        DB::transaction(function () use ($user, $validated, $referralAccount, $ledger) {
            // 1. ලෙජර් එකෙන් Referral සල්ලි අඩු කිරීම (Debit)
            $platformAccount = \App\Models\LedgerAccount::firstOrCreate(
                ['account_key' => 'platform:referral-reinvestments:USDT'],
                ['user_id' => null, 'currency' => 'USDT', 'kind' => 'income']
            );

            $ledger->post(
                'referral-reinvest:'.uniqid(),
                'USDT',
                'Reinvested Referral ' . $validated['amount'] . ' USDT into ' . $validated['package'],
                [
                    ['account_id' => $referralAccount->id, 'direction' => 'debit', 'amount' => (string) $validated['amount']],
                    ['account_id' => $platformAccount->id, 'direction' => 'credit', 'amount' => (string) $validated['amount']],
                ]
            );

            // 2. අලුත් Investment Account එකක් හදනවා
            $packageConfig = collect(config('investment-preview.packages'))->firstWhere('name', $validated['package']);
            $dailyRate = $packageConfig ? $packageConfig['daily_rate'] : 1.5;
            $minWithdrawal = $packageConfig ? $packageConfig['minimum_withdrawal'] : 10;
            $estimatedReward = ($validated['amount'] * $dailyRate * (int) $validated['days']) / 100;

            \App\Models\InvestmentAccount::create([
                'user_id' => $user->id,
                'package_name' => $validated['package'],
                'principal_usdt' => $validated['amount'],
                'maturity_days' => (int) $validated['days'],
                'daily_rate' => $dailyRate,
                'estimated_reward_usdt' => $estimatedReward,
                'minimum_withdrawal_usdt' => $minWithdrawal,
                'started_at' => now(),
                'status' => 'active',
                'matures_at' => now()->addDays((int) $validated['days']),
            ]);

            // 3. Balance එක Update කරනවා
            $newCredits = $referralAccount->entries()->where('direction', 'credit')->sum('amount');
            $newDebits = $referralAccount->entries()->where('direction', 'debit')->sum('amount');
            $referralAccount->update(['balance' => $newCredits - $newDebits]);
        });

        return back()->with('status', 'Successfully reinvested ' . $validated['amount'] . ' USDT from Referral into ' . $validated['package'] . '.');
    }
}