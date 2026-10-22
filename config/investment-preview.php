<?php

return [
    // Owner-provided planning assumptions for preview pages. They do
    // not activate investment, wallet, deposit, withdrawal, or payout flows.
    'payout_cap_multiple' => 3,
    'packages' => [
        ['name' => 'Starter', 'amount' => 10, 'daily_rate' => 0.8, 'minimum_withdrawal' => 15],
        ['name' => 'Bronze', 'amount' => 25, 'daily_rate' => 1.0, 'minimum_withdrawal' => 50],
        ['name' => 'Silver', 'amount' => 50, 'daily_rate' => 1.2, 'minimum_withdrawal' => 100],
        ['name' => 'Gold', 'amount' => 100, 'daily_rate' => 1.5, 'minimum_withdrawal' => 200],
        ['name' => 'Platinum', 'amount' => 250, 'daily_rate' => 1.8, 'minimum_withdrawal' => 750],
        ['name' => 'Diamond', 'amount' => 500, 'daily_rate' => 2.0, 'minimum_withdrawal' => 1500],
        ['name' => 'Premium', 'amount' => 1000, 'daily_rate' => 2.5, 'minimum_withdrawal' => 3000],
        ['name' => 'Pro', 'amount' => 2500, 'daily_rate' => 3.0, 'minimum_withdrawal' => 7500],
        ['name' => 'Elite', 'amount' => 5000, 'daily_rate' => 3.5, 'minimum_withdrawal' => 15000],
        ['name' => 'VIP', 'amount' => 10000, 'daily_rate' => 4.0, 'minimum_withdrawal' => 30000],
    ],
];
