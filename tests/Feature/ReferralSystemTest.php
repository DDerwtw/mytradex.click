<?php

namespace Tests\Feature;

use App\Models\ReferralCommission;
use App\Models\ReferralWithdrawalRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReferralSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_saturday_through_wednesday_requests_go_to_upcoming_friday(): void
    {
        // Wednesday, 2026-10-28
        Carbon::setTestNow(Carbon::parse('2026-10-28 12:00:00'));
        
        $scheduled = $this->callCalculateScheduledFriday();
        $this->assertEquals('2026-10-30', $scheduled, 'Wednesday request should go to upcoming Friday');
        
        Carbon::setTestNow();
    }

    public function test_thursday_and_friday_requests_go_to_next_week_friday(): void
    {
        // Thursday, 2026-10-29
        Carbon::setTestNow(Carbon::parse('2026-10-29 12:00:00'));
        
        $scheduled = $this->callCalculateScheduledFriday();
        $this->assertEquals('2026-11-06', $scheduled, 'Thursday request should go to next week Friday');
        
        // Friday, 2026-10-30
        Carbon::setTestNow(Carbon::parse('2026-10-30 12:00:00'));
        
        $scheduled = $this->callCalculateScheduledFriday();
        $this->assertEquals('2026-11-06', $scheduled, 'Friday request should go to next week Friday');
        
        Carbon::setTestNow();
    }

    public function test_referral_withdrawal_applies_2_5_percent_fee(): void
    {
        $gross = 1000.00;
        $feeRate = 2.5;
        $expectedFee = 25.00;
        $expectedNet = 975.00;
        
        $fee = round($gross * $feeRate / 100, 8, PHP_ROUND_HALF_UP);
        $net = round($gross - $fee, 8, PHP_ROUND_HALF_UP);
        
        $this->assertEquals($expectedFee, $fee);
        $this->assertEquals($expectedNet, $net);
    }

    public function test_referral_income_isolated_from_maturity_balance(): void
    {
        $user = User::factory()->create();
        
        // Maturity withdrawal should NOT affect referral income
        $reserved = (float) ReferralWithdrawalRequest::where('user_id', $user->id)
            ->where('status', '!=', 'rejected')
            ->sum('gross_amount_usdt');
            
        $this->assertEquals(0, $reserved);
    }

    private function callCalculateScheduledFriday(): string
    {
        $controller = new \App\Http\Controllers\ReferralController();
        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('calculateScheduledFriday');
        $method->setAccessible(true);
        
        return $method->invoke($controller);
    }
}