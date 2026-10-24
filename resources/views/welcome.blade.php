@extends('layouts.app')

@section('title', 'MyTradeX · Explore digital assets')

@section('content')
@php
    $featuredMarket = $markets[0] ?? null;
    $chartValues = $featuredMarket['sparkline'] ?? [];
    $chartPoints = [];
    $chartArea = '';

    if (count($chartValues) > 1) {
        $chartMin = min($chartValues);
        $chartRange = max($chartValues) - $chartMin;
        $chartRange = $chartRange > 0 ? $chartRange : 1;

        foreach ($chartValues as $index => $value) {
            $x = round($index * 420 / (count($chartValues) - 1), 1);
            $y = round(106 - (($value - $chartMin) / $chartRange * 86), 1);
            $chartPoints[] = $x.','.$y;
        }

        $chartArea = 'M 0,124 L '.implode(' L ', $chartPoints).' L 420,124 Z';
    }
@endphp

<div class="mockup-home">
    <section class="mockup-top" id="about">
        <div class="mockup-hero-copy">
            <div class="mockup-eyebrow"><span></span>A clearer way to explore digital assets</div>
            <h1>Explore digital assets.<br><em>Move with clarity.</em></h1>
            <p>MyTradeX brings market context and useful account tools together, with clear information about what is available and what is still being built.</p>
            <div class="mockup-trust-row"><span><b>⬡</b> Clear and transparent</span><span><b>₮</b> USDT transfer preview</span><span><b>◉</b> Account controls</span></div>
            <div class="mockup-hero-links"><a class="mockup-blue-button" href="{{ route('markets') }}">Explore markets <span>→</span></a><a href="{{ route('register') }}">Create your account</a></div>
        </div>

        <div class="mockup-visual" aria-hidden="true">
            <div class="mockup-visual-glow"></div>
            <div class="mockup-visual-grid"></div>
            <div class="mockup-bars"><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i></div>
            <div class="mockup-coin coin-btc">₿</div><div class="mockup-coin coin-eth">◇</div>
            <div class="mockup-orbit"></div><div class="mockup-platform"></div>
            <div class="mockup-usdt-coin"><span>₮</span></div>
            <div class="mockup-arrow">↗</div>
        </div>

        <!-- Mini Market Ticker Section -->
        <div class="mini-market-ticker" style="background: #0b1120; border-radius: 16px; border: 1px solid rgba(56, 189, 248, 0.2); padding: 20px; display: flex; flex-direction: column; height: 100%;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <div>
                    <div class="eyebrow" style="color: #38bdf8;">Market Overview</div>
                    <h2 style="color: #fff; font-size: 1.2rem; margin: 5px 0 0 0;">Live Prices</h2>
                </div>
                <span class="status-pill" style="background: rgba(74, 222, 128, 0.1); color: #4ade80; border: 1px solid rgba(74, 222, 128, 0.2);">● LIVE</span>
            </div>

            <div style="display: flex; flex-direction: column; gap: 15px;">
                <!-- Bitcoin -->
                <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 10px; border-bottom: 1px solid rgba(255,255,255,0.05);">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="font-size: 1.5rem; color: #f59e0b;">₿</span>
                        <div>
                            <strong style="color: #fff; display: block;">Bitcoin</strong>
                            <small style="color: #64748b;">BTC/USDT</small>
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <strong style="color: #fff; display: block;">$80,715.61</strong>
                        <small style="color: #f87171;">-1.24%</small>
                    </div>
                </div>

                <!-- Ethereum -->
                <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 10px; border-bottom: 1px solid rgba(255,255,255,0.05);">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="font-size: 1.5rem; color: #818cf8;">◇</span>
                        <div>
                            <strong style="color: #fff; display: block;">Ethereum</strong>
                            <small style="color: #64748b;">ETH/USDT</small>
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <strong style="color: #fff; display: block;">$2,375.55</strong>
                        <small style="color: #f87171;">-1.11%</small>
                    </div>
                </div>

                <!-- Solana -->
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="font-size: 1.5rem; color: #a855f7;">◎</span>
                        <div>
                            <strong style="color: #fff; display: block;">Solana</strong>
                            <small style="color: #64748b;">SOL/USDT</small>
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <strong style="color: #fff; display: block;">$102.18</strong>
                        <small style="color: #f87171;">-2.14%</small>
                    </div>
                </div>
            </div>
            
            <div style="margin-top: auto; padding-top: 20px;">
                <a href="{{ route('markets') }}" class="button button-outline" style="width: 100%; text-align: center; font-size: 0.9rem;">View All Markets →</a>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section class="how-it-works-section" style="margin-top: 40px; margin-bottom: 40px;">
        <div style="text-align: center; margin-bottom: 40px;">
            <div class="eyebrow" style="color: #38bdf8; justify-content: center;">Simple Process</div>
            <h2 style="font-size: 2rem; color: #fff; margin: 10px 0;">How MyTradeX Works</h2>
            <p style="color: #94a3b8; max-width: 600px; margin: 0 auto;">Start your digital asset journey in three simple steps. No hidden fees, transparent process.</p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px;">
            
            <!-- Step 1 -->
            <div class="dashboard-card" style="padding: 30px; text-align: center; background: linear-gradient(180deg, rgba(56, 189, 248, 0.05) 0%, rgba(11, 17, 32, 0) 100%); border-top: 4px solid #38bdf8;">
                <div style="width: 60px; height: 60px; background: rgba(56, 189, 248, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px auto; font-size: 1.5rem; color: #38bdf8; font-weight: bold;">01</div>
                <h3 style="color: #fff; font-size: 1.2rem; margin-bottom: 10px;">Create an Account</h3>
                <p style="color: #94a3b8; font-size: 0.9rem; line-height: 1.6;">Sign up in minutes. Verify your email address to secure your account and access the dashboard.</p>
            </div>

            <!-- Step 2 -->
            <div class="dashboard-card" style="padding: 30px; text-align: center; background: linear-gradient(180deg, rgba(234, 179, 8, 0.05) 0%, rgba(11, 17, 32, 0) 100%); border-top: 4px solid #eab308;">
                <div style="width: 60px; height: 60px; background: rgba(234, 179, 8, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px auto; font-size: 1.5rem; color: #eab308; font-weight: bold;">02</div>
                <h3 style="color: #fff; font-size: 1.2rem; margin-bottom: 10px;">Verify Identity (KYC)</h3>
                <p style="color: #94a3b8; font-size: 0.9rem; line-height: 1.6;">Complete a quick identity verification. Your documents are stored securely and reviewed by our team.</p>
            </div>

            <!-- Step 3 -->
            <div class="dashboard-card" style="padding: 30px; text-align: center; background: linear-gradient(180deg, rgba(74, 222, 128, 0.05) 0%, rgba(11, 17, 32, 0) 100%); border-top: 4px solid #4ade80;">
                <div style="width: 60px; height: 60px; background: rgba(74, 222, 128, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px auto; font-size: 1.5rem; color: #4ade80; font-weight: bold;">03</div>
                <h3 style="color: #fff; font-size: 1.2rem; margin-bottom: 10px;">Start Investing</h3>
                <p style="color: #94a3b8; font-size: 0.9rem; line-height: 1.6;">Deposit USDT, choose a package, and track your earnings. Withdraw or reinvest your profits easily.</p>
            </div>

        </div>
    </section>

    <section class="mockup-platform-section" id="packages">
        <div class="mockup-platform-heading"><div><h2>Explore the platform</h2><p>Available account and market features, with their current status.</p></div><span class="mockup-outline-label">FEATURE OVERVIEW</span></div>
        <div class="mockup-feature-grid">
            <a class="mockup-feature-card tone-teal" href="{{ route('markets') }}"><span class="feature-gem">◈</span><h3>Markets</h3><strong>Live prices</strong><small>CoinGecko data</small><span class="feature-link">View markets →</span></a>
            <a class="mockup-feature-card tone-coral" href="{{ route('register') }}"><span class="feature-gem">◉</span><h3>Starter</h3><strong>Account access</strong><small>Email verification</small><span class="feature-link">Create account →</span></a>
            <a class="mockup-feature-card tone-blue" href="{{ route('login') }}"><span class="feature-gem">✧</span><h3>Profile</h3><strong>Private account</strong><small>Personal dashboard</small><span class="feature-link">Sign in →</span></a>
            <a class="mockup-feature-card tone-gold" href="{{ route('login') }}"><span class="feature-gem">⬡</span><h3>Verification</h3><strong>Identity review</strong><small>Secure document flow</small><span class="feature-link">Account required</span></a>
            <a class="mockup-feature-card tone-violet" href="{{ route('home') }}#referrals"><span class="feature-gem">◇</span><h3>Referrals</h3><strong>Invite links</strong><small>Deposit-based commissions</small><span class="feature-link">Learn more →</span></a>
            <a class="mockup-feature-card tone-sky" href="{{ route('legal.risk') }}"><span class="feature-gem">◉</span><h3>Risk guide</h3><strong>Read before use</strong><small>Digital assets are volatile</small><span class="feature-link">Read disclosure →</span></a>
            <a class="mockup-feature-card tone-aqua" href="{{ route('home') }}#how-it-works"><span class="feature-gem">▤</span><h3>Transparency</h3><strong>Clear feature status</strong><small>No invented account figures</small><span class="feature-link">How it works →</span></a>
            <a class="mockup-feature-card tone-purple" href="{{ route('legal.terms') }}"><span class="feature-gem">▧</span><h3>Terms</h3><strong>Platform information</strong><small>Read the current terms</small><span class="feature-link">View terms →</span></a>
            <a class="mockup-feature-card tone-amber" href="{{ route('home') }}#faq"><span class="feature-gem">?</span><h3>Help</h3><strong>Common questions</strong><small>Feature availability</small><span class="feature-link">See answers →</span></a>
        </div>
        <div class="mockup-info-strip"><div><i>◈</i><span><strong>Market information</strong><small>Prices may be delayed and are not a trading feature.</small></span></div><div><i>⬡</i><span><strong>Account security</strong><small>Verified access and private document storage.</small></span></div><div><i>▣</i><span><strong>USDT · TRC20</strong><small>Transfer preview only. No funds are handled.</small></span></div><div><i>!</i><span><strong>Important</strong><small>No returns, balances, or rewards are promised.</small></span></div></div>
    </section>

    <!-- New Account Preview & Referral Section -->
    <section class="mockup-bottom-layout" id="how-it-works" style="display: flex; flex-direction: column; gap: 30px; margin-top: 50px;">
        
        <!-- Account Preview (Full Width) -->
        <div class="dashboard-panel" style="padding: 0; overflow: hidden;">
            <div style="padding: 20px 25px; border-bottom: 1px solid rgba(255,255,255,0.05); display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <div class="eyebrow" style="color: #38bdf8;">Account Dashboard</div>
                    <h2 style="font-size: 1.5rem; color: #fff; margin: 5px 0 0 0;">MyTradeX Preview</h2>
                </div>
                <a href="{{ route('login') }}" class="button button-outline" style="padding: 8px 15px; font-size: 0.9rem;">Open Account →</a>
            </div>
            
            <div style="padding: 25px;">
                <div class="mockup-metrics" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px;">
                    <article style="background: rgba(56, 189, 248, 0.05); border: 1px solid rgba(56, 189, 248, 0.2); padding: 20px; border-radius: 12px; display: flex; flex-direction: column; gap: 10px;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <i class="tone-blue" style="font-size: 1.8rem;">◈</i>
                            <small style="color: #94a3b8; font-size: 0.95rem; font-weight: 500;">Market feed</small>
                        </div>
                        <strong style="color: #38bdf8; font-size: 1.2rem;">{{ $markets ? 'Available' : 'Unavailable' }}</strong>
                    </article>
                    
                    <article style="background: rgba(74, 222, 128, 0.05); border: 1px solid rgba(74, 222, 128, 0.2); padding: 20px; border-radius: 12px; display: flex; flex-direction: column; gap: 10px;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <i class="tone-teal" style="font-size: 1.8rem;">◎</i>
                            <small style="color: #94a3b8; font-size: 0.95rem; font-weight: 500;">Account access</small>
                        </div>
                        <strong style="color: #4ade80; font-size: 1.2rem;">Secure Sign in</strong>
                    </article>
                    
                    <article style="background: rgba(168, 85, 247, 0.05); border: 1px solid rgba(168, 85, 247, 0.2); padding: 20px; border-radius: 12px; display: flex; flex-direction: column; gap: 10px;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <i class="tone-purple" style="font-size: 1.8rem;">◇</i>
                            <small style="color: #94a3b8; font-size: 0.95rem; font-weight: 500;">Referral commissions</small>
                        </div>
                        <strong style="color: #a855f7; font-size: 1.2rem;">Deposit based</strong>
                    </article>
                    
                    <article style="background: rgba(234, 179, 8, 0.05); border: 1px solid rgba(234, 179, 8, 0.2); padding: 20px; border-radius: 12px; display: flex; flex-direction: column; gap: 10px;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <i class="tone-gold" style="font-size: 1.8rem;">▣</i>
                            <small style="color: #94a3b8; font-size: 0.95rem; font-weight: 500;">Wallet balances</small>
                        </div>
                        <strong style="color: #eab308; font-size: 1.2rem;">Available on login</strong>
                    </article>
                </div>
            </div>
        </div>

        <!-- Referral & Wallet & CTA (Grid) -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 25px;">
            
            <!-- Referral Programme -->
            <div class="dashboard-panel" style="padding: 25px;">
                <div class="eyebrow" style="color: #a855f7;">Earnings</div>
                <h3 style="font-size: 1.3rem; color: #fff; margin: 5px 0 20px 0;">Referral Programme</h3>
                <div class="mockup-level" style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 10px; border-bottom: 1px solid rgba(255,255,255,0.05); margin-bottom: 10px;">
                    <span style="color: #cbd5e1;"><i style="background: #38bdf8; color: #000; padding: 2px 6px; border-radius: 4px; font-size: 0.7rem; margin-right: 8px;">1</i> Direct referrals</span>
                    <strong style="color: #38bdf8;">10%</strong>
                </div>
                <div class="mockup-level" style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 10px; border-bottom: 1px solid rgba(255,255,255,0.05); margin-bottom: 10px;">
                    <span style="color: #cbd5e1;"><i style="background: #4ade80; color: #000; padding: 2px 6px; border-radius: 4px; font-size: 0.7rem; margin-right: 8px;">2</i> Level 2</span>
                    <strong style="color: #4ade80;">5%</strong>
                </div>
                <div class="mockup-level" style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 10px; border-bottom: 1px solid rgba(255,255,255,0.05); margin-bottom: 10px;">
                    <span style="color: #cbd5e1;"><i style="background: #eab308; color: #000; padding: 2px 6px; border-radius: 4px; font-size: 0.7rem; margin-right: 8px;">3</i> Level 3</span>
                    <strong style="color: #eab308;">3%</strong>
                </div>
                <div class="mockup-level" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <span style="color: #cbd5e1;"><i style="background: #a855f7; color: #000; padding: 2px 6px; border-radius: 4px; font-size: 0.7rem; margin-right: 8px;">4</i> Level 4 & 5</span>
                    <strong style="color: #a855f7;">2% & 1%</strong>
                </div>
                <a href="{{ route('login') }}" class="button button-outline" style="width: 100%; text-align: center; font-size: 0.9rem;">View Referrals →</a>
            </div>

            <!-- Wallet Overview -->
            <div class="dashboard-panel" style="padding: 25px;">
                <div class="eyebrow" style="color: #38bdf8;">Balances</div>
                <h3 style="font-size: 1.3rem; color: #fff; margin: 5px 0 20px 0;">Wallet Overview</h3>
                <div style="display: flex; flex-direction: column; gap: 15px;">
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <i class="tone-blue" style="background: rgba(56, 189, 248, 0.1); padding: 10px; border-radius: 8px; font-size: 1.2rem;">▣</i>
                        <div>
                            <small style="color: #94a3b8; display: block;">Account balance</small>
                            <strong style="color: #38bdf8;">Secure Login Required</strong>
                        </div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <i class="tone-teal" style="background: rgba(74, 222, 128, 0.1); padding: 10px; border-radius: 8px; font-size: 1.2rem;">↗</i>
                        <div>
                            <small style="color: #94a3b8; display: block;">Market data</small>
                            <strong style="color: #4ade80;">{{ $markets ? 'Available' : 'Unavailable' }}</strong>
                        </div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <i class="tone-purple" style="background: rgba(168, 85, 247, 0.1); padding: 10px; border-radius: 8px; font-size: 1.2rem;">◎</i>
                        <div>
                            <small style="color: #94a3b8; display: block;">Referral income</small>
                            <strong style="color: #a855f7;">Tracked on Dashboard</strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CTA Card -->
            <div class="dashboard-panel" style="padding: 25px; display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center; background: linear-gradient(135deg, rgba(2, 132, 199, 0.1) 0%, rgba(2, 132, 199, 0.02) 100%); border: 1px solid rgba(56, 189, 248, 0.3);">
                <div class="mockup-cta-icon" style="font-size: 3rem; color: #38bdf8; margin-bottom: 15px;">⬡</div>
                <h3 style="font-size: 1.5rem; color: #fff; margin-bottom: 10px;">Explore with clarity.</h3>
                <p style="color: #94a3b8; font-size: 0.9rem; margin-bottom: 20px;">Understand the features and risks before creating an account.</p>
                <a href="{{ route('register') }}" class="button button-primary" style="width: 100%; padding: 12px; font-size: 1rem;">Create Account →</a>
            </div>

        </div>
    </section>

    <section class="mockup-faq-row" id="faq"><div><strong>Does MyTradeX place trades?</strong><span>No. Market information is for reference only.</span></div><div><strong>Can I deposit or withdraw USDT?</strong><span>Deposits are verified manually. Withdrawals require administrator review; this app does not send transfers automatically.</span></div><div><strong>Are referral commissions available?</strong><span>Configured commissions accrue after an administrator approves a referred member’s deposit. Withdrawals are reviewed manually.</span></div></section>
    <footer class="mockup-footer"><a href="{{ route('home') }}"><strong>mytradex.click</strong></a><span>© {{ date('Y') }} MyTradeX. Digital assets involve risk.</span><nav><a href="{{ route('legal.terms') }}">Terms</a><a href="{{ route('legal.risk') }}">Risk disclosure</a></nav></footer>
</div>
@endsection