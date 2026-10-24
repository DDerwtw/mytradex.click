<?php

namespace App\Console\Commands;

use App\Models\DailyInterestAccrual;
use App\Models\InvestmentAccount;
use App\Models\LedgerAccount;
use App\Services\Ledger\LedgerService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AccrueDailyInterest extends Command
{
    protected $signature = 'interest:accrue';
    protected $description = 'Calculate and accrue daily interest for all active investments';

    public function handle(LedgerService $ledger): void
    {
        $today = Carbon::today()->toDateString();
        
        // අද දවසට පොලිය එකතු කරන්න ඕන සියලුම ඉන්වෙස්ට්මන්ට්ස් ගන්න
        $investments = InvestmentAccount::where('status', 'active')
            ->where('matures_at', '>', now()) // කල්පිරෙන්න තියෙන ඒවා විතරයි
            ->get();

        $this->info("Processing " . $investments->count() . " active investments.");

        foreach ($investments as $investment) {
            // මේ ඉන්වෙස්ට්මන්ට් එකට අද දවසේ පොලිය දැනටමත් එකතු කරලා තියෙනවද බලන්න
            $exists = DailyInterestAccrual::where('investment_account_id', $investment->id)
                ->where('accrued_date', $today)
                ->exists();

            if ($exists) {
                continue; // එහෙම නම් මේක skip කරන්න
            }

            // දිනපතා පොලිය ගණනය කරන්න: (Principal * Daily Rate) / 100
            $dailyInterest = ($investment->principal_usdt * $investment->daily_rate) / 100;

            if ($dailyInterest <= 0) {
                continue;
            }

            DB::transaction(function () use ($investment, $dailyInterest, $today, $ledger) {
                // 1. Database එකේ සටහන් කරන්න (Duplicate එකක් තිබේ නම් skip කරන්න)
                $accrual = DailyInterestAccrual::firstOrCreate(
                    [
                        'investment_account_id' => $investment->id,
                        'accrued_date' => $today,
                    ],
                    [
                        'user_id' => $investment->user_id,
                        'amount_usdt' => $dailyInterest,
                    ]
                );

                // අලුතින් හැදුණේ නැත්නම්, මේක skip කරන්න
                if (! $accrual->wasRecentlyCreated) {
                    return;
                }

                // 2. ලෙජර් එකට දාන්න (මේකෙන් යූසර්ගේ බැලන්ස් එක වැඩි වෙනවා)
                $platformAccount = LedgerAccount::firstOrCreate(
                    ['account_key' => 'platform:interest-expense:USDT'],
                    ['user_id' => null, 'currency' => 'USDT', 'kind' => 'expense']
                );

                $userAccount = LedgerAccount::firstOrCreate(
                    ['account_key' => 'user:'.$investment->user_id.':investment:USDT'],
                    ['user_id' => $investment->user_id, 'currency' => 'USDT', 'kind' => 'payable']
                );

                $ledger->post(
                    'interest-accrual:'.$investment->id.':'.$today,
                    'USDT',
                    'Daily interest for ' . $investment->package_name,
                    [
                        ['account_id' => $platformAccount->id, 'direction' => 'debit', 'amount' => (string) $dailyInterest],
                        ['account_id' => $userAccount->id, 'direction' => 'credit', 'amount' => (string) $dailyInterest],
                    ]
                );

                // 3. User ගේ Wallet Balance එක අප්ඩේට් කරන්න
                $credits = $userAccount->entries()->where('direction', 'credit')->sum('amount');
                $debits = $userAccount->entries()->where('direction', 'debit')->sum('amount');
                $userAccount->update(['balance' => $credits - $debits]);
            });

            $this->info("Accrued $dailyInterest USDT for investment #{$investment->id}");
        }

        $this->info("Interest accrual completed.");
    }
}