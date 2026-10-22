<?php

namespace App\Services\AdminMessages;

use App\Mail\AdminDashboardMessage;
use App\Models\AdminUserMessage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class CreateAdminUserMessage
{
    public function create(array $attributes): AdminUserMessage
    {
        return DB::transaction(function () use ($attributes): AdminUserMessage {
            $message = AdminUserMessage::create($attributes + ['email_status' => 'pending']);

            DB::afterCommit(fn () => $this->deliver($message->id));

            return $message;
        });
    }

    private function deliver(int $messageId): void
    {
        $message = AdminUserMessage::with('user')->find($messageId);
        if (! $message || ! $message->user?->email) {
            return;
        }

        try {
            Mail::to($message->user->email)->send(new AdminDashboardMessage($message));
            $message->forceFill([
                'email_status' => in_array(config('mail.default'), ['log', 'array'], true) ? 'logged' : 'sent',
                'email_sent_at' => now(),
            ])->save();
        } catch (Throwable $exception) {
            $message->forceFill(['email_status' => 'failed'])->save();
            Log::warning('Dashboard message email could not be delivered.', [
                'message_id' => $message->id,
                'exception' => $exception::class,
            ]);
        }
    }
}
