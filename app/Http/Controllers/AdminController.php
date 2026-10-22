<?php

namespace App\Http\Controllers;

use App\Models\KycReview;
use App\Models\KycSubmission;
use App\Models\LedgerTransaction;
use App\Models\ReferralCommission;
use App\Models\ReferralWithdrawalRequest;
use App\Models\User;
use App\Services\AdminMessages\CreateAdminUserMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminController extends Controller
{
    public function dashboard(): View
    {
        return view('admin.dashboard', [
            'usersCount' => User::count(),
            'verifiedUsersCount' => User::whereNotNull('email_verified_at')->count(),
            'pendingKycCount' => KycSubmission::where('status', 'pending')->count() + \App\Models\KycChangeRequest::where('status', 'pending')->count(),
            'referralRelationshipsCount' => User::whereNotNull('referred_by_id')->count(),
            'pendingWithdrawalCount' => \App\Models\WithdrawalRequest::where('status', 'pending')->count(),
            'pendingDepositCount' => \App\Models\InvestmentDepositClaim::where('status', 'pending')->count(),
            'latestSubmissions' => KycSubmission::with('user:id,name,email')->latest('submitted_at')->limit(6)->get(),
        ]);
    }

    public function investmentPreview(): View
    {
        return view('admin.investment-preview', [
            'packages' => config('investment-preview.packages'),
            'payoutCapMultiple' => config('investment-preview.payout_cap_multiple'),
        ]);
    }

    public function ledgerIndex(): View
    {
        return view('admin.ledger', [
            'transactions' => LedgerTransaction::with('entries.account')->latest('posted_at')->paginate(30),
        ]);
    }

    public function referralIndex(Request $request): View
    {
        $validated = $request->validate(['search' => ['nullable', 'string', 'max:100']]);
        $search = trim((string) ($validated['search'] ?? ''));
        $query = User::query()
            ->whereNotNull('referred_by_id')
            ->select(['id', 'name', 'email', 'status', 'email_verified_at', 'referral_code', 'referred_by_id', 'created_at'])
            ->withExists(['kycSubmissions as kyc_verified' => fn ($query) => $query->where('status', 'approved')])
            ->with(['referrer' => fn ($query) => $query
                ->select(['id', 'name', 'email', 'referral_code'])
                ->withExists(['kycSubmissions as kyc_verified' => fn ($kycQuery) => $kycQuery->where('status', 'approved')])]);

        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder->where('email', 'like', '%'.$search.'%')
                    ->orWhere('referral_code', 'like', '%'.$search.'%')
                    ->orWhereHas('referrer', function ($referrerQuery) use ($search): void {
                        $referrerQuery->where('email', 'like', '%'.$search.'%')
                            ->orWhere('referral_code', 'like', '%'.$search.'%');
                    });
            });
        }

        return view('admin.referrals', [
            'referralRelationshipsCount' => User::whereNotNull('referred_by_id')->count(),
            'qualifiedReferralCount' => User::whereNotNull('referred_by_id')->whereNotNull('email_verified_at')->where('status', 'active')->count(),
            'referrersCount' => User::whereHas('referrals')->count(),
            'commissionTotal' => (float) ReferralCommission::sum('amount_usdt'),
            'commissionRates' => config('referrals.commission_rates', []),
            'referralWithdrawals' => ReferralWithdrawalRequest::with(['user:id,name,email', 'reviewer:id,name'])
                ->latest()
                ->paginate(25, ['*'], 'referral_withdrawals_page'),
            'pendingReferralWithdrawals' => ReferralWithdrawalRequest::where('status', 'pending')->count(),
            'search' => $search,
            'referrals' => $query->latest('created_at')->paginate(20)->withQueryString(),
        ]);
    }

    public function kycIndex(): View
    {
        return view('admin.kyc-index', [
            'submissions' => KycSubmission::with('user:id,name,email')
                ->where('status', 'pending')
                ->latest('submitted_at')
                ->paginate(20),
            'changeRequests' => \App\Models\KycChangeRequest::with(['user:id,name,email', 'submission:id,user_id,document_type,withdrawal_wallet_address'])
                ->where('status', 'pending')
                ->latest()
                ->paginate(20, ['*'], 'changes_page'),
        ]);
    }

    public function kycShow(KycSubmission $submission): View
    {
        return view('admin.kyc-show', [
            'submission' => $submission->load(['user:id,name,email', 'reviews.reviewer:id,name']),
        ]);
    }

    public function kycDocument(KycSubmission $submission): StreamedResponse
    {
        abort_unless(Storage::disk($submission->document_disk)->exists($submission->document_path), 404);

        $extension = pathinfo($submission->document_path, PATHINFO_EXTENSION);

        return Storage::disk($submission->document_disk)->download($submission->document_path, 'kyc-document'.($extension ? '.'.$extension : ''));
    }

    public function kycAttachment(KycSubmission $submission, string $type): StreamedResponse
    {
        $column = match ($type) {
            'address-proof' => 'address_proof_path',
            'face-photo' => 'face_photo_path',
            default => abort(404),
        };
        $path = $submission->{$column};
        abort_unless($path && Storage::disk($submission->document_disk)->exists($path), 404);
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        return Storage::disk($submission->document_disk)->download($path, $type.($extension ? '.'.$extension : ''));
    }

    public function reviewKyc(Request $request, KycSubmission $submission, CreateAdminUserMessage $messageCreator): RedirectResponse
    {
        $validated = $request->validate([
            'decision' => ['required', Rule::in(['approved', 'rejected'])],
            'reason' => ['nullable', 'required_if:decision,rejected', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($request, $submission, $validated, $messageCreator): void {
            $locked = KycSubmission::query()->lockForUpdate()->findOrFail($submission->id);

            abort_unless($locked->status === 'pending', 409, 'This application has already been reviewed.');

            $reason = trim((string) ($validated['reason'] ?? '')) ?: null;
            $locked->forceFill([
                'status' => $validated['decision'],
                'reviewed_by' => $request->user()->id,
                'rejection_reason' => $validated['decision'] === 'rejected' ? $reason : null,
                'reviewed_at' => now(),
            ])->save();

            KycReview::create([
                'kyc_submission_id' => $locked->id,
                'reviewer_id' => $request->user()->id,
                'decision' => $validated['decision'],
                'reason' => $reason,
                'created_at' => now(),
            ]);

            $messageCreator->create([
                'user_id' => $locked->user_id,
                'admin_id' => $request->user()->id,
                'category' => 'action',
                'title' => $validated['decision'] === 'approved' ? 'Identity verification approved' : 'Identity verification needs attention',
                'body' => $validated['decision'] === 'approved'
                    ? 'Your identity verification was approved.'
                    : 'Your identity verification was not approved. Review note: '.($reason ?: 'Please contact support for more information.'),
            ]);
        });

        return redirect()->route('admin.kyc.show', $submission)->with('status', 'The verification decision was recorded.');
    }
}
