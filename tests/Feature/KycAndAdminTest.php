<?php

namespace Tests\Feature;

use App\Models\KycReview;
use App\Models\KycSubmission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class KycAndAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_submit_a_document_to_private_storage(): void
    {
        Storage::fake('private');
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('account.kyc.store'), [
            'document_type' => 'passport',
            'document' => UploadedFile::fake()->createWithContent('identity.pdf', "%PDF-1.4\n1 0 obj\n<<>>\nendobj\ntrailer\n<<>>\n%%EOF\n"),
        ]);

        $response->assertRedirect(route('account.kyc'));
        $response->assertSessionHasNoErrors();
        $submission = KycSubmission::firstOrFail();

        $this->assertSame($user->id, $submission->user_id);
        $this->assertSame('pending', $submission->status);
        $this->assertSame('private', $submission->document_disk);
        Storage::disk('private')->assertExists($submission->document_path);
    }

    public function test_user_cannot_create_a_second_pending_kyc_application(): void
    {
        Storage::fake('private');
        $user = User::factory()->create();
        $user->kycSubmissions()->create([
            'document_type' => 'passport',
            'document_path' => 'kyc/old.pdf',
            'document_disk' => 'private',
            'status' => 'pending',
            'submitted_at' => now(),
        ]);

        $this->actingAs($user)->post(route('account.kyc.store'), [
            'document_type' => 'passport',
            'document' => UploadedFile::fake()->createWithContent('identity.pdf', "%PDF-1.4\n1 0 obj\n<<>>\nendobj\ntrailer\n<<>>\n%%EOF\n"),
        ])->assertRedirect();

        $this->assertDatabaseCount('kyc_submissions', 1);
    }

    public function test_regular_user_cannot_open_administrator_pages(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_admin_can_open_overview_and_review_queue(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk()->assertSee('Administrator overview');
        $this->actingAs($admin)->get(route('admin.kyc.index'))->assertOk()->assertSee('Verification queue');
    }

    public function test_only_an_administrator_can_download_a_private_kyc_document(): void
    {
        Storage::fake('private');
        Storage::disk('private')->put('kyc/secure.pdf', 'private test document');
        $owner = User::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);
        $submission = $owner->kycSubmissions()->create([
            'document_type' => 'passport',
            'document_path' => 'kyc/secure.pdf',
            'document_disk' => 'private',
            'status' => 'pending',
            'submitted_at' => now(),
        ]);

        $this->actingAs($owner)->get(route('admin.kyc.document', $submission))->assertForbidden();
        $this->actingAs($admin)->get(route('admin.kyc.document', $submission))->assertDownload('kyc-document.pdf');
    }

    public function test_admin_decision_is_recorded_in_review_history(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $submission = $user->kycSubmissions()->create([
            'document_type' => 'national_id',
            'document_path' => 'kyc/example.pdf',
            'document_disk' => 'private',
            'status' => 'pending',
            'submitted_at' => now(),
        ]);

        $this->actingAs($admin)->post(route('admin.kyc.review', $submission), [
            'decision' => 'rejected',
            'reason' => 'The image is not readable.',
        ])->assertRedirect(route('admin.kyc.show', $submission));

        $this->assertSame('rejected', $submission->fresh()->status);
        $this->assertSame($admin->id, $submission->fresh()->reviewed_by);
        $this->assertDatabaseHas('kyc_reviews', [
            'kyc_submission_id' => $submission->id,
            'reviewer_id' => $admin->id,
            'decision' => 'rejected',
            'reason' => 'The image is not readable.',
        ]);
        $this->assertSame(1, KycReview::count());
    }

    public function test_admin_cannot_reject_a_kyc_submission_without_a_reason(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $submission = $user->kycSubmissions()->create([
            'document_type' => 'passport',
            'document_path' => 'kyc/example.pdf',
            'document_disk' => 'private',
            'status' => 'pending',
            'submitted_at' => now(),
        ]);

        $this->actingAs($admin)->from(route('admin.kyc.show', $submission))
            ->post(route('admin.kyc.review', $submission), ['decision' => 'rejected'])
            ->assertSessionHasErrors('reason');

        $this->assertSame('pending', $submission->fresh()->status);
        $this->assertDatabaseCount('kyc_reviews', 0);
    }
}
