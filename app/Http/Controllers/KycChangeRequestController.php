<?php

namespace App\Http\Controllers;

use App\Models\KycChangeRequest;
use App\Services\AdminMessages\CreateAdminUserMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class KycChangeRequestController extends Controller
{
    public function review(Request $request, KycChangeRequest $changeRequest, CreateAdminUserMessage $messageCreator): RedirectResponse
    {
        $validated = $request->validate([
            'decision' => ['required', Rule::in(['approved', 'rejected'])],
            'admin_note' => ['nullable', 'required_if:decision,rejected', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($request, $changeRequest, $validated, $messageCreator): void {
            $locked = KycChangeRequest::query()->lockForUpdate()->findOrFail($changeRequest->id);
            abort_unless($locked->status === 'pending', 409, 'This KYC change request has already been reviewed.');
            $submission = $locked->submission()->firstOrFail();
            $latest = $locked->user->kycSubmissions()->latest('id')->first();
            abort_unless($latest?->id === $submission->id && $submission->status === 'approved', 409, 'This request no longer matches the user’s current approved KYC record.');

            $note = trim((string) ($validated['admin_note'] ?? '')) ?: null;
            $locked->update([
                'status' => $validated['decision'],
                'reviewed_by' => $request->user()->id,
                'admin_note' => $note,
                'reviewed_at' => now(),
            ]);

            $messageCreator->create([
                'user_id' => $locked->user_id,
                'admin_id' => $request->user()->id,
                'category' => 'action',
                'title' => $validated['decision'] === 'approved' ? 'KYC change request approved' : 'KYC change request declined',
                'body' => $validated['decision'] === 'approved'
                    ? 'Your request to change KYC details was approved. Submit the complete updated documents and TRC20 withdrawal wallet address through KYC Verification Review.'
                    : 'Your request to change KYC details was declined. Administrator note: '.($note ?: 'Please contact support.'),
            ]);
        });

        return redirect()->route('admin.kyc.index')->with('status', 'KYC change request decision saved.');
    }
}
