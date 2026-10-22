<?php

namespace App\Http\Controllers;

use App\Models\AdminUserMessage;
use App\Models\User;
use App\Services\AdminMessages\CreateAdminUserMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminMessageController extends Controller
{
    public function index(): View
    {
        return view('admin.messages', [
            'users' => User::query()->orderBy('name')->get(['id', 'name', 'email']),
            'messages' => AdminUserMessage::with(['user:id,name,email', 'admin:id,name'])
                ->latest()->paginate(30),
            'mailDriver' => config('mail.default'),
        ]);
    }

    public function store(Request $request, CreateAdminUserMessage $messageCreator): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'category' => ['required', 'in:message,action'],
            'title' => ['required', 'string', 'max:160'],
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $messageCreator->create([
            ...$validated,
            'admin_id' => $request->user()->id,
        ]);

        return redirect()->route('admin.messages.index')->with('status', 'The dashboard message was saved. Email delivery status is shown in the message history.');
    }
}
