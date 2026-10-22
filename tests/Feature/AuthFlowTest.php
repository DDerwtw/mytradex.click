<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class AuthFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_records_terms_and_requires_email_verification(): void
    {
        $response = $this->post(route('register.store'), [
            'name' => 'Asha Perera',
            'email' => 'ASHA@example.com',
            'password' => 'a-long-safe-password',
            'password_confirmation' => 'a-long-safe-password',
            'terms' => '1',
        ]);

        $user = User::where('email', 'asha@example.com')->firstOrFail();

        $response->assertRedirect(route('verification.notice'));
        $this->assertAuthenticatedAs($user);
        $this->assertNull($user->email_verified_at);
        $this->assertNotNull($user->terms_accepted_at);
        $this->assertSame('active', $user->status);
        $this->get(route('dashboard'))->assertRedirect(route('verification.notice'));
    }

    public function test_registration_rejects_missing_terms_acceptance(): void
    {
        $response = $this->from(route('register'))->post(route('register.store'), [
            'name' => 'Asha Perera',
            'email' => 'asha@example.com',
            'password' => 'a-long-safe-password',
            'password_confirmation' => 'a-long-safe-password',
        ]);

        $response->assertRedirect(route('register'));
        $response->assertSessionHasErrors('terms');
        $this->assertDatabaseCount('users', 0);
    }

    public function test_verified_active_user_can_log_in_and_open_dashboard(): void
    {
        $user = User::factory()->create([
            'email' => 'asha@example.com',
            'password' => 'a-long-safe-password',
        ]);

        $this->post(route('login.store'), [
            'email' => 'ASHA@example.com',
            'password' => 'a-long-safe-password',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->get(route('dashboard'))->assertOk()->assertSee('asha@example.com');
    }

    public function test_suspended_user_cannot_log_in(): void
    {
        User::factory()->create([
            'email' => 'suspended@example.com',
            'password' => 'a-long-safe-password',
            'status' => 'suspended',
        ]);

        $this->from(route('login'))->post(route('login.store'), [
            'email' => 'suspended@example.com',
            'password' => 'a-long-safe-password',
        ])->assertRedirect(route('login'))->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_signed_email_verification_activates_dashboard_access(): void
    {
        $user = User::factory()->unverified()->create();
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(30),
            ['id' => $user->getKey(), 'hash' => sha1($user->getEmailForVerification())],
        );

        $this->actingAs($user)->get($verificationUrl)
            ->assertRedirect(route('dashboard'));

        $verifiedUser = $user->fresh();
        $this->assertNotNull($verifiedUser->email_verified_at);
        $this->actingAs($verifiedUser)->get(route('dashboard'))->assertOk();
    }

    public function test_suspended_account_with_an_existing_session_is_signed_out(): void
    {
        $user = User::factory()->create(['status' => 'suspended']);

        $this->actingAs($user)->get(route('dashboard'))
            ->assertRedirect(route('login'))
            ->assertSessionHas('error');

        $this->assertGuest();
    }
}
