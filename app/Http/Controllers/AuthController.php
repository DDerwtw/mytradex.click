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
        ]);
    }
}
