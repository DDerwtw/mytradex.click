<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReferralTest extends TestCase
{
    use RefreshDatabase;

    public function test_each_user_receives_a_unique_referral_code(): void
    {
        $first = User::factory()->create();
        $second = User::factory()->create();

        $this->assertNotEmpty($first->referral_code);
        $this->assertNotSame($first->referral_code, $second->referral_code);
        $this->assertDatabaseHas('users', ['id' => $first->id, 'referral_code' => $first->referral_code]);
    }

    public function test_registration_records_a_valid_referrer(): void
    {
        $referrer = User::factory()->create();

        $response = $this->post(route('register.store'), [
            'name' => 'New Referral',
            'email' => 'new-referral@example.test',
            'password' => 'ValidPassword123!',
            'password_confirmation' => 'ValidPassword123!',
            'terms' => '1',
            'referral_code' => $referrer->referral_code,
        ]);

        $response->assertRedirect(route('verification.notice'));
        $newUser = User::query()->where('email', 'new-referral@example.test')->firstOrFail();
        $this->assertSame($referrer->id, $newUser->referred_by_id);
        $this->assertNotEmpty($newUser->referral_code);
    }

    public function test_registration_rejects_an_invalid_referral_code(): void
    {
        $this->post(route('register.store'), [
            'name' => 'Unattributed Referral',
            'email' => 'invalid-referral@example.test',
            'password' => 'ValidPassword123!',
            'password_confirmation' => 'ValidPassword123!',
            'terms' => '1',
            'referral_code' => 'NOT-A-REAL-CODE',
        ])->assertSessionHasErrors('referral_code');

        $this->assertDatabaseMissing('users', ['email' => 'invalid-referral@example.test']);
    }

    public function test_referral_page_shows_real_team_counts_without_member_emails(): void
    {
        $owner = User::factory()->create();
        $direct = User::factory()->create(['referred_by_id' => $owner->id]);
        User::factory()->unverified()->create(['referred_by_id' => $owner->id]);
        $levelTwo = User::factory()->create(['referred_by_id' => $direct->id]);
        User::factory()->create(['referred_by_id' => $levelTwo->id]);

        $this->actingAs($owner)->get(route('account.referrals'))
            ->assertOk()
            ->assertSee($owner->referral_code)
            ->assertSee('Level 1')
            ->assertSee('Level 2')
            ->assertSee('Level 3')
            ->assertDontSee($direct->email)
            ->assertDontSee($levelTwo->email);
    }

    public function test_registration_page_applies_a_valid_referral_link(): void
    {
        $referrer = User::factory()->create();

        $this->get(route('register', ['ref' => $referrer->referral_code]))
            ->assertOk()
            ->assertSee('Referral code applied:')
            ->assertSee('value="'.$referrer->referral_code.'"', false);
    }

    public function test_regular_user_cannot_access_admin_referral_relationships(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('admin.referrals.index'))->assertForbidden();
    }

    public function test_admin_can_search_referral_relationships(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $referrer = User::factory()->create(['email' => 'referrer@example.test']);
        User::factory()->create([
            'email' => 'joined-member@example.test',
            'referred_by_id' => $referrer->id,
        ]);
        $unrelated = User::factory()->create(['email' => 'unrelated@example.test']);

        $this->actingAs($admin)->get(route('admin.referrals.index', ['search' => 'joined-member@example.test']))
            ->assertOk()
            ->assertSee('joined-member@example.test')
            ->assertSee('referrer@example.test')
            ->assertDontSee($unrelated->email);
    }
}
