@extends('layouts.app')
@section('title', 'My Wallets')
@section('content')
<section class="page-section"><div class="container dashboard-container">
    <div class="dashboard-heading">
        <div>
            <div class="eyebrow">Financial Dashboard</div>
            <h1 class="page-title">My Wallets</h1>
            <p class="page-lede">Manage your funds. Withdraw, or reinvest to grow your portfolio.</p>
        </div>
        <a class="button button-outline" href="{{ route('dashboard') }}">Back to Dashboard</a>
    </div>

    @if (session('status'))
        <div class="alert alert-success" style="background: rgba(74, 222, 128, 0.1); color: #4ade80; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger" style="background: rgba(239, 68, 68, 0.1); color: #ef4444; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="dashboard-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 25px; margin-top: 30px;">
        
        <!-- Referral Wallet -->
        <div class="dashboard-card" style="padding: 25px; border-top: 4px solid #38bdf8; display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <div class="eyebrow" style="color: #38bdf8; font-size: 0.9rem; letter-spacing: 1px;">REFERRAL INCOME</div>
                <h2 style="font-size: 2.2rem; color: #fff; margin: 10px 0;">{{ number_format($referralBalance, 2) }} <small style="font-size: 1rem; color: #94a3b8;">USDT</small></h2>
                <p class="dashboard-muted" style="font-size: 0.85rem; min-height: 40px;">Earned from direct and indirect referrals. Can be withdrawn or reinvested.</p>
            </div>
            
            <div style="display: flex; flex-direction: column; gap: 10px; margin-top: 20px;">
                <!-- Referral Withdraw (Goes to Referrals Page) -->
                <a href="{{ route('account.referrals') }}" class="button button-primary" style="width: 100%; text-align: center; padding: 10px;">Withdraw</a>
                
                <!-- Referral Reinvest Form (NEW) -->
                <form method="POST" action="{{ route('account.wallets.referral-reinvest') }}" style="display: flex; gap: 5px; flex-wrap: wrap; width: 100%;">
                    @csrf
                    <input type="number" name="amount" step="0.01" min="10" placeholder="Amount" required style="flex: 1; min-width: 80px; padding: 8px; border-radius: 5px; border: 1px solid rgba(255,255,255,0.1); background: rgba(0,0,0,0.2); color: #fff;">
                    <select name="package" required style="flex: 1; min-width: 100px; padding: 8px; border-radius: 5px; border: 1px solid rgba(255,255,255,0.1); background: rgba(0,0,0,0.2); color: #fff;">
                        @foreach(config('investment-preview.packages') as $pkg)
                            <option value="{{ $pkg['name'] }}">{{ $pkg['name'] }}</option>
                        @endforeach
                    </select>
                    <input type="number" name="days" placeholder="Days" min="1" value="30" required style="width: 60px; padding: 8px; border-radius: 5px; border: 1px solid rgba(255,255,255,0.1); background: rgba(0,0,0,0.2); color: #fff;">
                    <button type="submit" class="button button-outline" style="padding: 8px 12px; width: 100%;">Reinvest</button>
                </form>
            </div>
        </div>

        <!-- Maturity Wallet -->
        <div class="dashboard-card" style="padding: 25px; border-top: 4px solid #4ade80; display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <div class="eyebrow" style="color: #4ade80; font-size: 0.9rem; letter-spacing: 1px;">MATURITY INCOME</div>
                <h2 style="font-size: 2.2rem; color: #fff; margin: 10px 0;">{{ number_format($maturityBalance, 2) }} <small style="font-size: 1rem; color: #94a3b8;">USDT</small></h2>
                <p class="dashboard-muted" style="font-size: 0.85rem; min-height: 40px;">Principal and profits from matured investments. Can be withdrawn or reinvested.</p>
            </div>
            
            <div style="display: flex; flex-direction: column; gap: 10px; margin-top: 20px;">
                <!-- Maturity Withdraw Form -->
                <form method="POST" action="{{ route('account.wallets.maturity-withdraw') }}" style="display: flex; gap: 5px;">
                    @csrf
                    <input type="number" name="amount" step="0.01" min="10" placeholder="Amount" required style="flex: 1; padding: 8px; border-radius: 5px; border: 1px solid rgba(255,255,255,0.1); background: rgba(0,0,0,0.2); color: #fff;">
                    <button type="submit" class="button button-primary" style="padding: 8px 12px;">Withdraw</button>
                </form>

                <!-- Maturity Reinvest Form -->
                <form method="POST" action="{{ route('account.wallets.maturity-reinvest') }}" style="display: flex; gap: 5px; flex-wrap: wrap;">
                    @csrf
                    <input type="number" name="amount" step="0.01" min="10" placeholder="Amount" required style="flex: 1; min-width: 80px; padding: 8px; border-radius: 5px; border: 1px solid rgba(255,255,255,0.1); background: rgba(0,0,0,0.2); color: #fff;">
                    <select name="package" required style="flex: 1; min-width: 100px; padding: 8px; border-radius: 5px; border: 1px solid rgba(255,255,255,0.1); background: rgba(0,0,0,0.2); color: #fff;">
                        @foreach(config('investment-preview.packages') as $pkg)
                            <option value="{{ $pkg['name'] }}">{{ $pkg['name'] }}</option>
                        @endforeach
                    </select>
                    <input type="number" name="days" placeholder="Days" min="1" value="30" required style="width: 60px; padding: 8px; border-radius: 5px; border: 1px solid rgba(255,255,255,0.1); background: rgba(0,0,0,0.2); color: #fff;">
                    <button type="submit" class="button button-outline" style="padding: 8px 12px;">Reinvest</button>
                </form>
            </div>
        </div>

        <!-- Bonus Wallet -->
        <div class="dashboard-card" style="padding: 25px; border-top: 4px solid #eab308; display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <div class="eyebrow" style="color: #eab308; font-size: 0.9rem; letter-spacing: 1px;">BONUS INCOME</div>
                <h2 style="font-size: 2.2rem; color: #fff; margin: 10px 0;">{{ number_format($bonusBalance, 2) }} <small style="font-size: 1rem; color: #94a3b8;">USDT</small></h2>
                <p class="dashboard-muted" style="font-size: 0.85rem; min-height: 40px;">Special bonuses. Cannot be withdrawn directly. Must be invested.</p>
            </div>
            
            <div style="display: flex; flex-direction: column; gap: 10px; margin-top: 20px;">
                <!-- Bonus Invest Form -->
                <form method="POST" action="{{ route('account.wallets.bonus-invest') }}" style="display: flex; gap: 5px; flex-wrap: wrap;">
                    @csrf
                    <input type="number" name="amount" step="0.01" min="10" placeholder="Amount" required style="flex: 1; min-width: 80px; padding: 8px; border-radius: 5px; border: 1px solid rgba(255,255,255,0.1); background: rgba(0,0,0,0.2); color: #fff;">
                    <select name="package" required style="flex: 1; min-width: 100px; padding: 8px; border-radius: 5px; border: 1px solid rgba(255,255,255,0.1); background: rgba(0,0,0,0.2); color: #fff;">
                        @foreach(config('investment-preview.packages') as $pkg)
                            <option value="{{ $pkg['name'] }}">{{ $pkg['name'] }}</option>
                        @endforeach
                    </select>
                    <input type="number" name="days" placeholder="Days" min="1" value="30" required style="width: 60px; padding: 8px; border-radius: 5px; border: 1px solid rgba(255,255,255,0.1); background: rgba(0,0,0,0.2); color: #fff;">
                    <button type="submit" class="button button-primary" style="width: 100%; padding: 10px; background: #eab308; color: #000; font-weight: bold;">Invest Bonus</button>
                </form>
            </div>
        </div>

    </div>
    
    <!-- Package Comparison Section -->
    <section class="dashboard-panel" style="margin-top: 40px; margin-bottom: 20px;">
        <div class="dashboard-panel-heading">
            <div>
                <div class="eyebrow">Investment Options</div>
                <h2>Package Comparison</h2>
            </div>
            <span class="status-pill">{{ count(config('investment-preview.packages')) }} entries</span>
        </div>
        <p class="dashboard-muted" style="margin-bottom: 20px;">These are the available daily-rate assumptions for each package. Minimum withdrawal amounts apply.</p>
        
        <div class="referral-table-wrap" style="overflow-x: auto;">
            <table class="referral-table model-table" style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.1); color: #64748b; font-size: 0.85rem;">
                        <th style="padding: 12px 10px;">Package</th>
                        <th style="padding: 12px 10px;">Amount (USDT)</th>
                        <th style="padding: 12px 10px;">Daily Rate Assumption</th>
                        <th style="padding: 12px 10px;">Minimum Withdrawal Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(config('investment-preview.packages') as $pkg)
                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                            <td style="padding: 12px 10px; font-weight: 600; color: #e2e8f0;">{{ $pkg['name'] }}</td>
                            <td style="padding: 12px 10px; color: #94a3b8;">{{ number_format($pkg['amount']) }} USDT</td>
                            <td style="padding: 12px 10px; color: #38bdf8;">{{ number_format($pkg['daily_rate'], 1) }}%</td>
                            <td style="padding: 12px 10px; color: #94a3b8;">{{ number_format($pkg['minimum_withdrawal']) }} USDT</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

</div></section>
@endsection