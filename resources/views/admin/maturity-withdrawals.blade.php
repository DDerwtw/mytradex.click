@extends('layouts.app')

@section('title', 'Maturity Withdrawals')

@section('content')
<section class="page-section"><div class="container dashboard-container">
    <div class="dashboard-heading"><div><div class="eyebrow">Admin Panel</div><h1 class="page-title">Maturity Withdrawal Requests</h1></div><a class="button button-outline" href="{{ route('admin.dashboard') }}">Admin overview</a></div>

    @if (session('status'))
        <div class="alert alert-success" style="background: rgba(74, 222, 128, 0.1); color: #4ade80; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger" style="background: rgba(239, 68, 68, 0.1); color: #ef4444; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <ul style="margin: 0; padding-left: 20px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="dashboard-panel">
        <div class="dashboard-panel-heading">
            <div><div class="eyebrow">Pending requests</div><h2>Maturity Withdrawals</h2></div>
            <span class="status-pill">{{ $pendingCount }} pending</span>
        </div>
        
        <div class="referral-table-wrap">
            <table class="referral-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Amount</th>
                        <th>Requested At</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $req)
                        <tr>
                            <td>{{ $req->user->name }}<br><small>{{ $req->user->email }}</small></td>
                            <td>{{ number_format($req->amount_usdt, 2) }} USDT</td>
                            <td>{{ $req->created_at->format('M j, Y H:i') }}</td>
                            <td>{{ ucfirst($req->status) }}</td>
                            <td>
                                @if($req->status === 'pending')
                                    <!-- Approve / Reject Form -->
                                    <form method="POST" action="{{ route('admin.maturity-withdrawals.review', $req) }}" style="display: flex; gap: 5px; flex-wrap: wrap;">
                                        @csrf
                                        <input type="text" name="review_note" placeholder="Note (optional)" style="padding: 5px; border-radius: 4px; border: 1px solid rgba(255,255,255,0.1); background: rgba(0,0,0,0.2); color: #fff; width: 100px; font-size: 0.8rem;">
                                        <button type="submit" name="decision" value="approved" class="button button-primary" style="padding: 5px 10px; font-size: 0.8rem;">Approve</button>
                                        <button type="submit" name="decision" value="rejected" class="button button-outline" style="padding: 5px 10px; font-size: 0.8rem;">Reject</button>
                                    </form>
                                @elseif($req->status === 'approved')
                                    <!-- Mark as Paid Form -->
                                    <form method="POST" action="{{ route('admin.maturity-withdrawals.paid', $req) }}" style="display: flex; gap: 5px; flex-wrap: wrap;">
                                        @csrf
                                        <input type="text" name="payout_transaction_id" placeholder="TRON Tx Hash" required style="padding: 5px; border-radius: 4px; border: 1px solid rgba(255,255,255,0.1); background: rgba(0,0,0,0.2); color: #fff; width: 140px; font-size: 0.8rem;">
                                        <button type="submit" class="button button-primary" style="padding: 5px 10px; font-size: 0.8rem; background: #4ade80; color: #000;">Mark as Paid</button>
                                    </form>
                                @else
                                    <span class="dashboard-muted">No action available</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5">No requests found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $requests->links() }}
    </section>
</div></section>
@endsection