<?php

namespace App\Http\Controllers;

use App\Models\AdminUserMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AccountMessageController extends Controller
{
    public function markRead(Request $request, AdminUserMessage $message): RedirectResponse
    {
        abort_unless($message->user_id === $request->user()->id, 404);

        if ($message->read_at === null) {
            $message->update(['read_at' => now()]);
        }

        return redirect()->route('dashboard')->with('status', 'Message marked as read.');
    }
}
