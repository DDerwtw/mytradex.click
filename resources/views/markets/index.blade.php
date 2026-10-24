@extends('layouts.app')

@section('title', 'Market data')

@section('content')
<section class="page-section">
    <div class="container market-page">
        <div class="dashboard-heading">
            <div>
                <div class="eyebrow">LIVE MARKET DATA</div>
                <h1 class="page-title">Global Markets Overview</h1>
                <p class="page-lede">Track real-time prices for Forex and US Stock markets.</p>
            </div>
            <span class="status-pill" style="background: rgba(74, 222, 128, 0.1); color: #4ade80; border: 1px solid rgba(74, 222, 128, 0.2);">● LIVE DATA</span>
        </div>
        <!-- Crypto Market Section -->
        <section class="dashboard-panel" style="margin-top: 30px;">
            <div class="dashboard-panel-heading" style="border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 15px; margin-bottom: 20px;">
                <div>
                    <div class="eyebrow" style="color: #f59e0b;">Digital Assets</div>
                    <h2 style="font-size: 1.5rem;">Crypto Market</h2>
                </div>
                <span class="status-pill" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.2);">BTC / USDT</span>
            </div>
            <p class="dashboard-muted">Track major cryptocurrencies like Bitcoin, Ethereum, and Solana in real-time.</p>
            
            <div style="background: #0b1120; border-radius: 16px; border: 1px solid rgba(245, 158, 11, 0.2); overflow: hidden; margin-top: 20px;">
                <div class="tradingview-widget-container" style="height: 500px; width: 100%;">
                    <div id="crypto_chart" style="height: 100%; width: 100%;"></div>
                    <script type="text/javascript" src="https://s3.tradingview.com/tv.js"></script>
                    <script type="text/javascript">
                        new TradingView.widget({
                            "autosize": true,
                            "symbol": "BINANCE:BTCUSDT",
                            "interval": "60",
                            "timezone": "Etc/UTC",
                            "theme": "dark",
                            "style": "1",
                            "locale": "en",
                            "toolbar_bg": "#0b1120",
                            "enable_publishing": false,
                            "withdateranges": true,
                            "hide_side_toolbar": true,
                            "allow_symbol_change": true,
                            "container_id": "crypto_chart"
                        });
                    </script>
                </div>
            </div>
        </section>
        
        <!-- Forex Market Section -->
        <section class="dashboard-panel" style="margin-top: 30px;">
            <div class="dashboard-panel-heading" style="border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 15px; margin-bottom: 20px;">
                <div>
                    <div class="eyebrow" style="color: #38bdf8;">Global Currencies</div>
                    <h2 style="font-size: 1.5rem;">Forex Market</h2>
                </div>
                <span class="status-pill" style="background: rgba(56, 189, 248, 0.1); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.2);">EUR / USD</span>
            </div>
            <p class="dashboard-muted">Track major currency pairs like EUR/USD, GBP/USD, and USD/JPY in real-time.</p>
            
            <div style="background: #0b1120; border-radius: 16px; border: 1px solid rgba(56, 189, 248, 0.2); overflow: hidden; margin-top: 20px;">
                <div class="tradingview-widget-container" style="height: 500px; width: 100%;">
                    <div id="forex_chart" style="height: 100%; width: 100%;"></div>
                    <script type="text/javascript" src="https://s3.tradingview.com/tv.js"></script>
                    <script type="text/javascript">
                        new TradingView.widget({
                            "autosize": true,
                            "symbol": "FX:EURUSD",
                            "interval": "60",
                            "timezone": "Etc/UTC",
                            "theme": "dark",
                            "style": "1",
                            "locale": "en",
                            "toolbar_bg": "#0b1120",
                            "enable_publishing": false,
                            "withdateranges": true,
                            "hide_side_toolbar": true,
                            "allow_symbol_change": true,
                            "container_id": "forex_chart"
                        });
                    </script>
                </div>
            </div>
        </section>

        <!-- US Stock Market Section -->
        <section class="dashboard-panel" style="margin-top: 30px;">
            <div class="dashboard-panel-heading" style="border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 15px; margin-bottom: 20px;">
                <div>
                    <div class="eyebrow" style="color: #a855f7;">US Equities</div>
                    <h2 style="font-size: 1.5rem;">US Stock Market</h2>
                </div>
                <span class="status-pill" style="background: rgba(168, 85, 247, 0.1); color: #a855f7; border: 1px solid rgba(168, 85, 247, 0.2);">AAPL</span>
            </div>
            <p class="dashboard-muted">Track leading US stocks like Apple, Tesla, and Microsoft.</p>
            
            <div style="background: #0b1120; border-radius: 16px; border: 1px solid rgba(168, 85, 247, 0.2); overflow: hidden; margin-top: 20px;">
                <div class="tradingview-widget-container" style="height: 500px; width: 100%;">
                    <div id="stock_chart" style="height: 100%; width: 100%;"></div>
                    <script type="text/javascript" src="https://s3.tradingview.com/tv.js"></script>
                    <script type="text/javascript">
                        new TradingView.widget({
                            "autosize": true,
                            "symbol": "NASDAQ:AAPL",
                            "interval": "60",
                            "timezone": "Etc/UTC",
                            "theme": "dark",
                            "style": "1",
                            "locale": "en",
                            "toolbar_bg": "#0b1120",
                            "enable_publishing": false,
                            "withdateranges": true,
                            "hide_side_toolbar": true,
                            "allow_symbol_change": true,
                            "container_id": "stock_chart"
                        });
                    </script>
                </div>
            </div>
        </section>

        <!-- Info Notice -->
        <div class="notice-card" style="margin-top: 30px;">
            <span class="notice-icon">i</span>
            <div>
                <strong>Market data is for informational purposes only.</strong>
                <p>Prices are provided by TradingView and may be delayed. MyTradeX does not place trades or provide investment advice. Digital assets and leveraged products are volatile.</p>
            </div>
        </div>
    </div>
</section>
@endsection