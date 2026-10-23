<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AccountMessageController;
use App\Http\Controllers\AdminMessageController;
use App\Http\Controllers\KycController;
use App\Http\Controllers\KycChangeRequestController;
use App\Http\Controllers\InvestmentController;
use App\Http\Controllers\MarketController;
use App\Http\Controllers\ReferralController;
use App\Services\MarketData\CoinGeckoMarketDataProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;

Route::get('/', function (CoinGeckoMarketDataProvider $provider): View {
    return view('welcome', ['markets' => $provider->markets()]);
})->name('home');
Route::get('/markets', [MarketController::class, 'index'])->middleware('throttle:30,1')->name('markets');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('login.store');

    Route::get('/register', [AuthController::class, 'showRegistration'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1')->name('register.store');

    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->middleware('throttle:5,1')->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:5,1')->name('password.update');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/email/verify', [AuthController::class, 'verificationNotice'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
    Route::post('/email/verification-notification', [AuthController::class, 'resendVerification'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('/dashboard', [AuthController::class, 'dashboard'])
        ->middleware(['verified', 'account.active'])
        ->name('dashboard');

    Route::middleware(['verified', 'account.active'])->prefix('account')->name('account.')->group(function (): void {
        Route::get('/verification', [KycController::class, 'show'])->name('kyc');
        Route::post('/verification', [KycController::class, 'store'])->middleware('throttle:3,10')->name('kyc.store');
        Route::post('/verification/change-requests', [KycController::class, 'requestChange'])->middleware('throttle:3,10')->name('kyc.change-requests.store');
        Route::get('/referrals', [ReferralController::class, 'show'])->name('referrals');
        Route::post('/referral-withdrawals', [ReferralController::class, 'requestWithdrawal'])->middleware('throttle:5,10')->name('referral-withdrawals.store');
        Route::post('/investments', [InvestmentController::class, 'store'])->middleware('throttle:10,1')->name('investments.store');
        Route::post('/investments/{investment}/deposit-claims', [InvestmentController::class, 'requestDeposit'])->middleware('throttle:5,10')->name('investments.deposits.store');
        Route::post('/investments/{investment}/withdrawal-requests', [InvestmentController::class, 'requestWithdrawal'])->middleware('throttle:5,10')->name('investments.withdrawals.store');
        Route::post('/messages/{message}/read', [AccountMessageController::class, 'markRead'])->middleware('throttle:30,1')->name('messages.read');
        
        // Wallets Page
        Route::get('/wallets', [AuthController::class, 'wallets'])->name('wallets');
        
        // Wallet Actions (Withdraw / Invest / Reinvest)
        Route::post('/wallets/maturity-withdraw', [AuthController::class, 'requestMaturityWithdrawal'])->name('wallets.maturity-withdraw');
        Route::post('/wallets/maturity-reinvest', [AuthController::class, 'reinvestMaturity'])->name('wallets.maturity-reinvest');
        Route::post('/wallets/bonus-invest', [AuthController::class, 'investBonus'])->name('wallets.bonus-invest');
        Route::post('/wallets/referral-reinvest', [AuthController::class, 'reinvestReferral'])->name('wallets.referral-reinvest');
    });

    Route::middleware(['verified', 'account.active', 'admin'])->prefix('admin')->name('admin.')->group(function (): void {
        Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/investment-preview', [AdminController::class, 'investmentPreview'])->name('investment-preview');
        Route::get('/messages', [AdminMessageController::class, 'index'])->name('messages.index');
        Route::post('/messages', [AdminMessageController::class, 'store'])->middleware('throttle:20,1')->name('messages.store');
        Route::get('/investments', [InvestmentController::class, 'adminIndex'])->name('investments.index');
        Route::get('/ledger', [AdminController::class, 'ledgerIndex'])->name('ledger.index');
        Route::get('/deposits', [InvestmentController::class, 'depositIndex'])->name('deposits.index');
        Route::post('/deposits/sync', [InvestmentController::class, 'syncDeposits'])->middleware('throttle:5,1')->name('deposits.sync');
        Route::get('/deposits/{claim}/receipt', [InvestmentController::class, 'depositReceipt'])->name('deposits.receipt');
        Route::post('/deposits/{claim}/review', [InvestmentController::class, 'reviewDeposit'])->middleware('throttle:20,1')->name('deposits.review');
        Route::post('/withdrawals/{withdrawal}/review', [InvestmentController::class, 'reviewWithdrawal'])->middleware('throttle:20,1')->name('withdrawals.review');
        Route::get('/referrals', [AdminController::class, 'referralIndex'])->name('referrals.index');
        
        Route::post('/users/{user}/adjust-balance', [AdminController::class, 'adjustBalance'])->middleware('throttle:20,1')->name('users.adjust-balance');
        
        Route::post('/referrals/withdrawals/{withdrawal}/review', [ReferralController::class, 'reviewWithdrawal'])->middleware('throttle:20,1')->name('referrals.withdrawals.review');
        Route::post('/referrals/withdrawals/{withdrawal}/paid', [ReferralController::class, 'markWithdrawalPaid'])->middleware('throttle:20,1')->name('referrals.withdrawals.paid');
        Route::get('/kyc', [AdminController::class, 'kycIndex'])->name('kyc.index');
        Route::get('/kyc/{submission}', [AdminController::class, 'kycShow'])->name('kyc.show');
        Route::get('/kyc/{submission}/document', [AdminController::class, 'kycDocument'])->name('kyc.document');
        Route::get('/kyc/{submission}/attachment/{type}', [AdminController::class, 'kycAttachment'])->name('kyc.attachment');
        Route::post('/kyc/{submission}/decision', [AdminController::class, 'reviewKyc'])->middleware('throttle:20,1')->name('kyc.review');
        Route::post('/kyc/change-requests/{changeRequest}/decision', [KycChangeRequestController::class, 'review'])->middleware('throttle:20,1')->name('kyc.change-requests.review');
        
        // Maturity Withdrawals (Admin)
        Route::get('/maturity-withdrawals', [AdminController::class, 'maturityWithdrawals'])->name('maturity-withdrawals.index');
        Route::post('/maturity-withdrawals/{withdrawal}/review', [AdminController::class, 'reviewMaturityWithdrawal'])->name('maturity-withdrawals.review');
        Route::post('/maturity-withdrawals/{withdrawal}/paid', [AdminController::class, 'markMaturityWithdrawalPaid'])->name('maturity-withdrawals.paid');
    });
});

Route::view('/terms', 'pages.terms')->name('legal.terms');
Route::view('/risk-disclosure', 'pages.risk')->name('legal.risk');