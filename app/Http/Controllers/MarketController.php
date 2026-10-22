<?php

namespace App\Http\Controllers;

use App\Services\MarketData\CoinGeckoMarketDataProvider;
use Illuminate\View\View;

class MarketController extends Controller
{
    public function index(CoinGeckoMarketDataProvider $provider): View
    {
        return view('markets.index', [
            'markets' => $provider->markets(),
        ]);
    }
}
