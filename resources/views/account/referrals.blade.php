@extends('layouts.app')

@section('title', 'Referrals')

@section('content')
<section class="page-section">
    <div class="container dashboard-container">
        <div class="dashboard-heading">
            <div><div class="eyebrow">MyTradeX community</div><h1 class="page-title">Referral overview</h1><p class="page-lede">Share your link and view referral relationships connected to your account.</p></div>
            <a class="button button-outline" href="{{ route('dashboard') }}">Back to dashboard</a>
        </div>

        <!-- Total Balance Section -->
        <section class="dashboard-panel referral-balance-panel" style="display: flex; flex-direction: column; gap: 20px; padding: 24px; background: linear-gradient(135deg, rgba(2, 132, 199, 0.1) 0%, rgba(2, 132, 199, 0.02) 100%); border-left: 4px solid #0284c7;">
            <div>
                <div class="eyebrow" style="color: #38bdf8; text-transform: uppercase; letter-spacing: 1px; font-weight: 600; font-size: 0.85rem; margin-bottom: 5px;">Total Referral Income Balance</div>
                <div style="display: flex; align-items: baseline; gap: 10px;">
                    <strong style="font-size: 2.5rem; color: #ffffff; line-height: 1;">{{ number_format($availableReferralIncome, 2) }}</strong>
                    <span style="font-size: 1.2rem; color: #38bdf8; font-weight: 600;">USDT</span>
                </div>
                <div style="font-size: 0.85rem; color: #94a3b8; margin-top: 5px; font-weight: 500;">Available to request</div>
            </div>
            
            <div style="display: flex; gap: 30px; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 15px;">
                <div style="display: flex; flex-direction: column; gap: 4px;">
                    <span style="font-size: 0.75rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Total Earned</span>
                    <strong style="color: #e2e8f0; font-size: 1.1rem;">{{ number_format($earnedReferralIncome, 2) }} <span style="font-size: 0.8rem; color: #94a3b8;">USDT</span></strong>
                </div>
                <div style="display: flex; flex-direction: column; gap: 4px;">
                    <span style="font-size: 0.75rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Reserved for Requests</span>
                    <strong style="color: #e2e8f0; font-size: 1.1rem;">{{ number_format($reservedReferralIncome, 2) }} <span style="font-size: 0.8rem; color: #94a3b8;">USDT</span></strong>
                </div>
            </div>
        </section>

        <!-- Referral Link Section -->
        <div class="referral-share-panel dashboard-panel">
            <div class="eyebrow">Your referral link</div>
            <h2>Invite someone to MyTradeX</h2>
            <p class="dashboard-muted">Your personal code is <strong>{{ $user->referral_code }}</strong>. A referral is recorded only when a new account is created through this link.</p>
            <div class="referral-copy-row">
                <input type="text" readonly aria-label="Your referral link" value="{{ $referralUrl }}" id="referral-link">
                <button class="button button-primary" type="button" data-copy-referral="{{ $referralUrl }}">Copy link</button>
            </div>
            <p class="referral-copy-status" role="status" aria-live="polite" data-copy-status></p>
        </div>

        <!-- Level Stats Section -->
        <div class="dashboard-grid referral-stats">
            <article class="dashboard-card"><div class="dashboard-card-top"><span class="dashboard-icon">1</span></div><h2>Level 1 · Direct referrals</h2><p class="dashboard-metric">{{ number_format($levelCounts[1]) }}</p><div class="dashboard-detail"><span>Commission rate</span><strong>{{ number_format((float) ($levelRates[1] ?? 0), 2) }}%</strong></div><div class="dashboard-detail"><span>Referral income</span><strong>{{ number_format((float) ($levelIncome[1] ?? 0), 2) }} USDT</strong></div><div class="dashboard-detail"><span>Verified and active</span><strong>{{ number_format($qualifiedDirectCount) }}</strong></div></article>
            <article class="dashboard-card"><div class="dashboard-card-top"><span class="dashboard-icon">2</span></div><h2>Level 2</h2><p class="dashboard-metric">{{ number_format($levelCounts[2]) }}</p><div class="dashboard-detail"><span>Commission rate</span><strong>{{ number_format((float) ($levelRates[2] ?? 0), 2) }}%</strong></div><div class="dashboard-detail"><span>Referral income</span><strong>{{ number_format((float) ($levelIncome[2] ?? 0), 2) }} USDT</strong></div><div class="dashboard-detail"><span>Relationship count</span><strong>{{ number_format($levelCounts[2]) }}</strong></div></article>
            <article class="dashboard-card"><div class="dashboard-card-top"><span class="dashboard-icon">3</span></div><h2>Level 3</h2><p class="dashboard-metric">{{ number_format($levelCounts[3]) }}</p><div class="dashboard-detail"><span>Commission rate</span><strong>{{ number_format((float) ($levelRates[3] ?? 0), 2) }}%</strong></div><div class="dashboard-detail"><span>Referral income</span><strong>{{ number_format((float) ($levelIncome[3] ?? 0), 2) }} USDT</strong></div><div class="dashboard-detail"><span>Relationship count</span><strong>{{ number_format($levelCounts[3]) }}</strong></div></article>
            <article class="dashboard-card"><div class="dashboard-card-top"><span class="dashboard-icon">4</span></div><h2>Level 4</h2><p class="dashboard-metric">{{ number_format($levelCounts[4]) }}</p><div class="dashboard-detail"><span>Commission rate</span><strong>{{ number_format((float) ($levelRates[4] ?? 0), 2) }}%</strong></div><div class="dashboard-detail"><span>Referral income</span><strong>{{ number_format((float) ($levelIncome[4] ?? 0), 2) }} USDT</strong></div><div class="dashboard-detail"><span>Relationship count</span><strong>{{ number_format($levelCounts[4]) }}</strong></div></article>
            <article class="dashboard-card"><div class="dashboard-card-top"><span class="dashboard-icon">5</span></div><h2>Level 5</h2><p class="dashboard-metric">{{ number_format($levelCounts[5]) }}</p><div class="dashboard-detail"><span>Commission rate</span><strong>{{ number_format((float) ($levelRates[5] ?? 0), 2) }}%</strong></div><div class="dashboard-detail"><span>Referral income</span><strong>{{ number_format((float) ($levelIncome[5] ?? 0), 2) }} USDT</strong></div><div class="dashboard-detail"><span>Relationship count</span><strong>{{ number_format($levelCounts[5]) }}</strong></div></article>
        </div>

        <!-- Referral Network Tree (NEW) -->
        <section class="dashboard-panel" style="margin-top: 30px;">
            <div class="dashboard-panel-heading">
                <div>
                    <div class="eyebrow">Your Network</div>
                    <h2>Referral Tree</h2>
                </div>
                <span class="status-pill">{{ array_sum($levelCounts) }} total connections</span>
            </div>
            <p class="dashboard-muted">This tree shows the structure of your referral network up to 5 levels deep.</p>

            <div class="referral-tree-container" style="padding: 20px; overflow-x: auto;">
                @php
                    $user = auth()->user();
                    $level1 = $user->referrals()->get();
                    $level2 = collect();
                    $level3 = collect();
                    $level4 = collect();
                    $level5 = collect();

                    foreach($level1 as $l1) {
                        $l2 = $l1->referrals()->get();
                        $level2 = $level2->merge($l2);
                        foreach($l2 as $l2item) {
                            $l3 = $l2item->referrals()->get();
                            $level3 = $level3->merge($l3);
                            foreach($l3 as $l3item) {
                                $l4 = $l3item->referrals()->get();
                                $level4 = $level4->merge($l4);
                                foreach($l4 as $l4item) {
                                    $l5 = $l4item->referrals()->get();
                                    $level5 = $level5->merge($l5);
                                }
                            }
                        }
                    }
                @endphp

                <div style="display: flex; flex-direction: column; gap: 30px;">
                    
                    <!-- Level 1 -->
                    <div>
                        <div class="eyebrow" style="color: #38bdf8;">Level 1 (Direct)</div>
                        <div style="display: flex; gap: 10px; flex-wrap: wrap; margin-top: 10px;">
                            @forelse($level1 as $member)
                                <div class="tree-node" style="background: rgba(56, 189, 248, 0.1); border: 1px solid rgba(56, 189, 248, 0.3); padding: 10px; border-radius: 8px; min-width: 150px;">
                                    <strong style="color: #fff;">{{ $member->name }}</strong>
                                    <div style="font-size: 0.75rem; color: #94a3b8;">{{ $member->email }}</div>
                                </div>
                            @empty
                                <span class="dashboard-muted">No direct referrals yet.</span>
                            @endforelse
                        </div>
                    </div>

                    <!-- Level 2 -->
                    <div>
                        <div class="eyebrow" style="color: #4ade80;">Level 2</div>
                        <div style="display: flex; gap: 10px; flex-wrap: wrap; margin-top: 10px;">
                            @forelse($level2 as $member)
                                <div class="tree-node" style="background: rgba(74, 222, 128, 0.1); border: 1px solid rgba(74, 222, 128, 0.3); padding: 10px; border-radius: 8px; min-width: 150px;">
                                    <strong style="color: #fff;">{{ $member->name }}</strong>
                                    <div style="font-size: 0.75rem; color: #94a3b8;">{{ $member->email }}</div>
                                </div>
                            @empty
                                <span class="dashboard-muted">No Level 2 referrals yet.</span>
                            @endforelse
                        </div>
                    </div>

                    <!-- Level 3 -->
                    <div>
                        <div class="eyebrow" style="color: #eab308;">Level 3</div>
                        <div style="display: flex; gap: 10px; flex-wrap: wrap; margin-top: 10px;">
                            @forelse($level3 as $member)
                                <div class="tree-node" style="background: rgba(234, 179, 8, 0.1); border: 1px solid rgba(234, 179, 8, 0.3); padding: 10px; border-radius: 8px; min-width: 150px;">
                                    <strong style="color: #fff;">{{ $member->name }}</strong>
                                    <div style="font-size: 0.75rem; color: #94a3b8;">{{ $member->email }}</div>
                                </div>
                            @empty
                                <span class="dashboard-muted">No Level 3 referrals yet.</span>
                            @endforelse
                        </div>
                    </div>

                    <!-- Level 4 & 5 -->
                    <div style="display: flex; gap: 30px; flex-wrap: wrap;">
                        <div>
                            <div class="eyebrow" style="color: #a855f7;">Level 4</div>
                            <div style="display: flex; gap: 10px; flex-wrap: wrap; margin-top: 10px;">
                                @forelse($level4 as $member)
                                    <div class="tree-node" style="background: rgba(168, 85, 247, 0.1); border: 1px solid rgba(168, 85, 247, 0.3); padding: 10px; border-radius: 8px; min-width: 120px;">
                                        <strong style="color: #fff;">{{ $member->name }}</strong>
                                    </div>
                                @empty
                                    <span class="dashboard-muted">None</span>
                                @endforelse
                            </div>
                        </div>
                        <div>
                            <div class="eyebrow" style="color: #ec4899;">Level 5</div>
                            <div style="display: flex; gap: 10px; flex-wrap: wrap; margin-top: 10px;">
                                @forelse($level5 as $member)
                                    <div class="tree-node" style="background: rgba(236, 72, 153, 0.1); border: 1px solid rgba(236, 72, 153, 0.3); padding: 10px; border-radius: 8px; min-width: 120px;">
                                        <strong style="color: #fff;">{{ $member->name }}</strong>
                                    </div>
                                @empty
                                    <span class="dashboard-muted">None</span>
                                @endforelse
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        <!-- Withdrawal Section (Only Referral) -->
        <section class="dashboard-panel referral-withdraw-panel" style="margin-top: 30px;">
            <div class="dashboard-panel-heading" style="border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 15px; margin-bottom: 20px;">
                <div>
                    <div class="eyebrow" style="color: #38bdf8;">Referral Income</div>
                    <h2 style="font-size: 1.5rem; margin-top: 5px;">Withdraw Earnings</h2>
                </div>
                <span class="status-pill" style="background: rgba(56, 189, 248, 0.1); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.2);">TRC20 Network</span>
            </div>

            @if (! $kycVerified || ! $withdrawalWalletAddress)
                <div class="draft-banner" style="background: rgba(234, 179, 8, 0.1); border-left: 4px solid #eab308; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <strong style="color: #eab308;">Approved KYC and a saved TRC20 wallet are required.</strong> 
                    <a class="text-link" href="{{ route('account.kyc') }}" style="color: #eab308; text-decoration: underline; margin-left: 5px;">Open KYC Verification Review →</a>
                </div>
            @else
                <div style="margin-bottom: 20px; font-size: 0.9rem; color: #94a3b8;">
                    Saved payout wallet: <code style="background: rgba(255,255,255,0.05); padding: 4px 8px; border-radius: 4px; color: #e2e8f0; font-family: monospace;">{{ $withdrawalWalletAddress }}</code>
                </div>
            @endif

            @if ($errors->any())
                <div class="form-alert" role="alert" style="background: rgba(239, 68, 68, 0.1); border-left: 4px solid #ef4444; padding: 15px; border-radius: 8px; margin-bottom: 20px; color: #fca5a5;">
                    <ul style="margin: 0; padding-left: 20px;">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <form class="model-save-form referral-withdraw-form" method="POST" action="{{ route('account.referral-withdrawals.store') }}" style="display: flex; flex-direction: column; gap: 20px;">
                @csrf
                <input type="hidden" name="withdrawal_type" value="referral">

                <div style="max-width: 400px;">
                    <label for="withdrawal-amount" style="display: block; font-size: 0.9rem; color: #cbd5e1; margin-bottom: 8px; font-weight: 500;">Amount to Withdraw (USDT)</label>
                    <input name="amount_usdt" id="withdrawal-amount" type="number" min="0.00000001" max="{{ number_format($availableReferralIncome, 8, '.', '') }}" step="0.00000001" required value="{{ old('amount_usdt') }}" 
                           style="width: 100%; padding: 12px 16px; background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(56, 189, 248, 0.3); border-radius: 8px; color: #fff; font-size: 1.1rem; transition: border-color 0.3s;"
                           placeholder="0.00">
                    <small style="display: block; margin-top: 8px; color: #64748b;">Available to withdraw: <strong style="color: #38bdf8;">{{ number_format($availableReferralIncome, 2) }} USDT</strong></small>
                </div>

                <!-- Fee Preview Box -->
                <div style="background: rgba(15, 23, 42, 0.8); border-radius: 12px; padding: 20px; display: flex; flex-wrap: wrap; gap: 30px; border: 1px solid rgba(255,255,255,0.05);">
                    <div>
                        <div style="font-size: 0.8rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 5px;">Tax & Service Charge (2.5%)</div>
                        <div style="font-size: 1.2rem; color: #f87171; font-weight: 600;"><span id="withdrawal-fee">0.00</span> USDT</div>
                    </div>
                    <div style="width: 1px; background: rgba(255,255,255,0.1);"></div>
                    <div>
                        <div style="font-size: 0.8rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 5px;">Estimated Net Amount</div>
                        <div style="font-size: 1.2rem; color: #4ade80; font-weight: 600;"><span id="withdrawal-net">0.00</span> USDT</div>
                    </div>
                </div>

                <div style="background: rgba(56, 189, 248, 0.05); border-radius: 8px; padding: 15px; font-size: 0.85rem; color: #94a3b8; border-left: 3px solid #38bdf8;">
                    <strong>Please Note:</strong> Referral income requests are scheduled for the next Friday. Requests submitted Thursday or Friday go to the following Friday. An administrator reviews them; this app does not send funds automatically.
                </div>

                <div>
                    <button class="button button-primary" type="submit" @if (! $kycVerified || ! $withdrawalWalletAddress) disabled @endif style="padding: 12px 24px; font-weight: 600; font-size: 1rem;">Submit Withdrawal Request</button>
                </div>
            </form>
        </section>

        <!-- Withdrawal History Section -->
        @if ($referralWithdrawals->isNotEmpty())
            <section class="dashboard-panel" style="margin-top: 30px;">
                <div class="dashboard-panel-heading">
                    <div>
                        <div class="eyebrow" style="color: #38bdf8;">Transaction Log</div>
                        <h2>Withdrawal History</h2>
                    </div>
                </div>
                
                <div class="referral-table-wrap" style="overflow-x: auto;">
                    <table class="referral-table" style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem;">
                        <thead>
                            <tr style="border-bottom: 1px solid rgba(255,255,255,0.1); color: #64748b; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px;">
                                <th style="padding: 15px 10px; font-weight: 600;">Date Requested</th>
                                <th style="padding: 15px 10px; font-weight: 600;">Scheduled</th>
                                <th style="padding: 15px 10px; font-weight: 600;">Gross</th>
                                <th style="padding: 15px 10px; font-weight: 600;">Fee & Net</th>
                                <th style="padding: 15px 10px; font-weight: 600;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($referralWithdrawals as $withdrawal)
                                <tr style="border-bottom: 1px solid rgba(255,255,255,0.05); transition: background 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.02)'" onmouseout="this.style.background='transparent'">
                                    <td style="padding: 15px 10px; color: #e2e8f0;">{{ $withdrawal->created_at->format('M j, Y') }}</td>
                                    <td style="padding: 15px 10px; color: #94a3b8;">{{ $withdrawal->scheduled_for->format('M j, Y') }}</td>
                                    <td style="padding: 15px 10px; color: #e2e8f0; font-weight: 500;">{{ number_format((float) $withdrawal->gross_amount_usdt, 2) }} USDT</td>
                                    <td style="padding: 15px 10px; color: #94a3b8;">
                                        <span style="color: #f87171;">-{{ number_format((float) $withdrawal->fee_amount_usdt, 2) }}</span> | 
                                        <span style="color: #4ade80;">{{ number_format((float) $withdrawal->net_amount_usdt, 2) }}</span>
                                    </td>
                                    <td style="padding: 15px 10px;">
                                        @php
                                            $statusColors = [
                                                'pending' => ['bg' => 'rgba(234, 179, 8, 0.1)', 'color' => '#eab308', 'border' => 'rgba(234, 179, 8, 0.2)'],
                                                'review_approved' => ['bg' => 'rgba(56, 189, 248, 0.1)', 'color' => '#38bdf8', 'border' => 'rgba(56, 189, 248, 0.2)'],
                                                'paid' => ['bg' => 'rgba(74, 222, 128, 0.1)', 'color' => '#4ade80', 'border' => 'rgba(74, 222, 128, 0.2)'],
                                                'rejected' => ['bg' => 'rgba(239, 68, 68, 0.1)', 'color' => '#ef4444', 'border' => 'rgba(239, 68, 68, 0.2)'],
                                            ];
                                            $color = $statusColors[$withdrawal->status] ?? $statusColors['pending'];
                                        @endphp
                                        <span style="background: {{ $color['bg'] }}; color: {{ $color['color'] }}; border: 1px solid {{ $color['border'] }}; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; text-transform: capitalize;">
                                            {{ ucfirst(str_replace('_', ' ', $withdrawal->status)) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        @endif

        <div class="notice-card"><span class="notice-icon">i</span><div><strong>How referral commission is calculated</strong><p>Commission is accrued only after an administrator approves a referred member’s USDT deposit, using the level rate shown above and the approved deposit amount. Withdrawal estimates deduct the displayed 2.5% charge. Requests are reviewed by an administrator; no transfer is sent automatically.</p></div></div>
    </div>
</section>
<script>
    document.querySelector('[data-copy-referral]')?.addEventListener('click', async (event) => {
        const button = event.currentTarget;
        const status = document.querySelector('[data-copy-status]');
        try {
            await navigator.clipboard.writeText(button.dataset.copyReferral);
            status.textContent = 'Referral link copied.';
        } catch {
            const field = document.querySelector('#referral-link');
            field.select();
            status.textContent = 'Select and copy the link from the field above.';
        }
    });

    const amountInput = document.getElementById('withdrawal-amount');
    const feeDisplay = document.getElementById('withdrawal-fee');
    const netDisplay = document.getElementById('withdrawal-net');
    const feeRate = 2.5;

    if (amountInput) {
        amountInput.addEventListener('input', function() {
            const amount = parseFloat(this.value) || 0;
            const fee = (amount * feeRate) / 100;
            const net = amount - fee;

            feeDisplay.textContent = fee.toFixed(2);
            netDisplay.textContent = net.toFixed(2);
        });
    }
</script>
@endsection