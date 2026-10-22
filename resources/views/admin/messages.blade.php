@extends('layouts.app')

@section('title', 'User dashboard messages')

@section('content')
<section class="page-section"><div class="container dashboard-container model-preview">
    <div class="dashboard-heading mockup-page-heading"><div><div class="eyebrow">Restricted administrator area</div><h1 class="page-title">User dashboard messages</h1><p class="page-lede">Send a message or record an action update for a user's dashboard.</p></div><a class="button button-outline" href="{{ route('admin.dashboard') }}">Back to admin</a></div>
    @if (session('status'))<div class="flash flash-success" role="status">{{ session('status') }}</div>@endif
    @if ($errors->any())<div class="notice-card" role="alert"><span class="notice-icon">!</span><div><strong>Please review the form.</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div></div>@endif
    @if (in_array($mailDriver, ['log', 'array'], true))<div class="notice-card mail-driver-warning"><span class="notice-icon">!</span><div><strong>Email is not being delivered.</strong><p>The current mail driver is <code>{{ $mailDriver }}</code>. Dashboard messages are saved, but actual emails require SMTP or another delivery mailer in the environment settings.</p></div></div>@endif
    <section class="dashboard-panel model-table-panel">
        <div class="dashboard-panel-heading"><div><div class="eyebrow">Compose</div><h2>Send dashboard update</h2></div></div>
        <form class="model-save-form admin-message-form" method="POST" action="{{ route('admin.messages.store') }}">@csrf
            <label>Choose user<select name="user_id" required><option value="">Select a user</option>@foreach ($users as $user)<option value="{{ $user->id }}" @selected(old('user_id') == $user->id)>{{ $user->name }} · {{ $user->email }}</option>@endforeach</select></label>
            <label>Type<select name="category" required><option value="message" @selected(old('category') === 'message')>Message</option><option value="action" @selected(old('category') === 'action')>Action update</option></select></label>
            <label>Title<input name="title" type="text" maxlength="160" value="{{ old('title') }}" required></label>
            <label>Message<textarea name="body" rows="5" maxlength="5000" required>{{ old('body') }}</textarea></label>
            <span class="dashboard-muted">This appears in the selected user's dashboard. It does not send an email.</span>
            <button class="button button-primary" type="submit">Send dashboard update</button>
        </form>
    </section>
    <section class="dashboard-panel model-table-panel">
        <div class="dashboard-panel-heading"><div><div class="eyebrow">History</div><h2>Recent dashboard updates</h2></div><span class="status-pill">{{ $messages->total() }} records</span></div>
        <div class="referral-table-wrap"><table class="referral-table model-table"><thead><tr><th>User</th><th>Type</th><th>Title and message</th><th>Admin</th><th>Sent</th><th>Read</th><th>Email</th></tr></thead><tbody>
            @forelse ($messages as $message)
                <tr><td>{{ $message->user->name }}<br><small>{{ $message->user->email }}</small></td><td>{{ $message->category === 'action' ? 'Action update' : 'Message' }}</td><td><strong>{{ $message->title }}</strong><br>{{ $message->body }}</td><td>{{ $message->admin?->name ?? 'System' }}</td><td>{{ $message->created_at->format('M j, Y H:i') }}</td><td>{{ $message->read_at?->format('M j, Y H:i') ?? 'Unread' }}</td><td>{{ ucfirst($message->email_status) }}@if ($message->email_sent_at)<br><small>{{ $message->email_sent_at->format('M j, H:i') }}</small>@endif</td></tr>
            @empty<tr><td colspan="7">No dashboard messages yet.</td></tr>@endforelse
        </tbody></table></div><div class="model-pagination">{{ $messages->links() }}</div>
    </section>
</div></section>
@endsection
