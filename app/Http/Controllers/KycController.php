<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\KycChangeRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class KycController extends Controller
{
    public function show(Request $request): View
    {
        $user = $request->user();
        $submission = $user->kycSubmissions()->latest('id')->first();
        $changeRequest = $user->kycChangeRequests()->latest('id')->first();

        return view('account.kyc', [
            'submission' => $submission,
            'changeRequest' => $changeRequest,
            'canSubmit' => ! $submission || $submission->status === 'rejected'
                || ($submission->status === 'approved' && $changeRequest?->status === 'approved' && ! $changeRequest->fulfilled_at),
            'canRequestChange' => $submission?->status === 'approved'
                && (! $changeRequest || in_array($changeRequest->status, ['rejected', 'fulfilled'], true)),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $latest = $user->kycSubmissions()->latest('id')->first();

        $approvedChangeRequest = $user->kycChangeRequests()->where('status', 'approved')->whereNull('fulfilled_at')->latest('id')->first();
        $canSubmit = ! $latest || $latest->status === 'rejected'
            || ($latest->status === 'approved' && $approvedChangeRequest);
        if (! $canSubmit || ($latest && $latest->status === 'pending')) {
            return back()->with('error', 'Submit KYC documents only when no review is pending, or after an administrator approves your change request.');
        }

        $validated = $request->validate([
            'document_type' => ['required', Rule::in(['passport', 'national_id', 'drivers_license'])],
            'document' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'address_proof' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'face_photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'withdrawal_wallet_address' => ['required', 'string', 'regex:/\AT[1-9A-HJ-NP-Za-km-z]{33}\z/'],
        ]);

        $paths = [];

        try {
            foreach (['document', 'address_proof', 'face_photo'] as $field) {
                $paths[$field] = $validated[$field]->store('kyc/'.$user->id, 'private');
            }

            DB::transaction(function () use ($user, $validated, $paths): void {
                $lockedUser = User::query()->lockForUpdate()->findOrFail($user->id);
                $latest = $lockedUser->kycSubmissions()->latest('id')->first();
                $changeRequest = $lockedUser->kycChangeRequests()->where('status', 'approved')->whereNull('fulfilled_at')->lockForUpdate()->latest('id')->first();
                $canSubmit = ! $latest || $latest->status === 'rejected'
                    || ($latest->status === 'approved' && $changeRequest);

                if (! $canSubmit || ($latest && $latest->status === 'pending')) {
                    abort(409, 'KYC documents cannot be submitted until an administrator authorizes the change.');
                }

                $submission = $lockedUser->kycSubmissions()->create([
                    'document_type' => $validated['document_type'],
                    'document_path' => $paths['document'],
                    'address_proof_path' => $paths['address_proof'],
                    'face_photo_path' => $paths['face_photo'],
                    'withdrawal_wallet_address' => $validated['withdrawal_wallet_address'],
                    'document_disk' => 'private',
                    'status' => 'pending',
                    'submitted_at' => now(),
                ]);
                if ($changeRequest) {
                    $changeRequest->update(['status' => 'fulfilled', 'fulfilled_at' => now()]);
                }
            });
        } catch (Throwable $exception) {
            Storage::disk('private')->delete(array_values($paths));
            throw $exception;
        }

        return redirect()->route('account.kyc')->with('status', 'Your verification application was received and is awaiting review.');
    }

    public function requestChange(Request $request): RedirectResponse
    {
        $validated = $request->validate(['reason' => ['required', 'string', 'min:10', 'max:1000']]);
        $user = $request->user();

        DB::transaction(function () use ($user, $validated): void {
            $lockedUser = User::query()->lockForUpdate()->findOrFail($user->id);
            $latest = $lockedUser->kycSubmissions()->latest('id')->first();
            abort_unless($latest?->status === 'approved', 409, 'Only approved KYC information can be changed by administrator approval.');

            $openRequest = $lockedUser->kycChangeRequests()
                ->where(function ($query): void {
                    $query->where('status', 'pending')
                        ->orWhere(function ($query): void {
                            $query->where('status', 'approved')->whereNull('fulfilled_at');
                        });
                })->exists();
            abort_unless(! $openRequest, 409, 'A KYC change request is already open.');

            $lockedUser->kycChangeRequests()->create([
                'kyc_submission_id' => $latest->id,
                'reason' => trim($validated['reason']),
                'status' => 'pending',
            ]);
        });

        return redirect()->route('account.kyc')->with('status', 'Your KYC change request was sent to the administrator. You can update details only after approval.');
    }
}
