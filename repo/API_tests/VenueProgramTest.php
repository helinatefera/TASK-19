<?php

use App\Enums\CampaignStatus;
use App\Enums\CampaignVisibility;
use App\Models\User;
use App\Models\VenueProgram;

beforeEach(function () {
    $this->seed();
    $this->moderator = User::where('username', 'mod1')->first();
    $this->admin = User::where('username', 'admin')->first();
    $this->user = User::where('username', 'user1')->first();
});

test('GET /api/programs returns paginated list', function () {
    $response = $this->getJson('/api/programs');

    $response->assertStatus(200)
        ->assertJsonStructure(['data', 'current_page', 'per_page', 'total']);
});

test('GET /api/programs/{id} returns program detail', function () {
    $program = VenueProgram::where('status', CampaignStatus::Published)->first();

    $response = $this->getJson("/api/programs/{$program->id}");

    $response->assertStatus(200)
        ->assertJsonFragment([
            'id' => $program->id,
            'title' => $program->title,
            'status' => 'published',
        ]);
});

test('POST /api/programs by moderator creates program', function () {
    $response = $this->actingAs($this->moderator)->postJson('/api/programs', [
        'title' => 'New Test Program',
        'description' => 'A test venue program',
    ], ['X-Idempotency-Key' => 'program-create-' . uniqid()]);

    $response->assertStatus(201)
        ->assertJsonFragment([
            'title' => 'New Test Program',
            'status' => 'draft',
        ]);
});

test('POST /api/programs by regular user returns 403', function () {
    $response = $this->actingAs($this->user)->postJson('/api/programs', [
        'title' => 'Should Fail',
        'description' => 'A test venue program',
    ], ['X-Idempotency-Key' => 'program-unauth-' . uniqid()]);

    $response->assertStatus(403);
});

test('PUT /api/programs/{id} updates program', function () {
    $program = VenueProgram::create([
        'title' => 'Editable Program',
        'slug' => 'editable-program-' . uniqid(),
        'description' => 'Original',
        'status' => CampaignStatus::Draft,
        'visibility' => CampaignVisibility::Offline,
        'created_by' => $this->moderator->id,
    ]);

    $response = $this->actingAs($this->moderator)->putJson("/api/programs/{$program->id}", [
        'title' => 'Updated Program Title',
    ]);

    $response->assertStatus(200)
        ->assertJsonFragment(['title' => 'Updated Program Title']);
});

test('POST /api/programs/{id}/submit changes status to pending_review', function () {
    $program = VenueProgram::create([
        'title' => 'Program To Submit',
        'slug' => 'program-to-submit-' . uniqid(),
        'description' => 'Description',
        'status' => CampaignStatus::Draft,
        'visibility' => CampaignVisibility::Offline,
        'created_by' => $this->moderator->id,
    ]);

    $response = $this->actingAs($this->moderator)->postJson("/api/programs/{$program->id}/submit");

    $response->assertStatus(200)
        ->assertJsonFragment(['status' => 'pending_review']);
});

test('POST /api/programs/{id}/approve changes status to published', function () {
    $program = VenueProgram::create([
        'title' => 'Program To Approve',
        'slug' => 'program-to-approve-' . uniqid(),
        'description' => 'Description',
        'status' => CampaignStatus::PendingReview,
        'visibility' => CampaignVisibility::Offline,
        'created_by' => $this->moderator->id,
    ]);

    $response = $this->actingAs($this->moderator)->postJson("/api/programs/{$program->id}/approve");

    $response->assertStatus(200)
        ->assertJsonFragment([
            'status' => 'published',
            'visibility' => 'online',
        ]);
});

test('POST /api/programs/{id}/reject changes status back to draft', function () {
    $program = VenueProgram::create([
        'title' => 'Program To Reject',
        'slug' => 'program-to-reject-' . uniqid(),
        'description' => 'Description',
        'status' => CampaignStatus::PendingReview,
        'visibility' => CampaignVisibility::Offline,
        'created_by' => $this->moderator->id,
    ]);

    $response = $this->actingAs($this->moderator)->postJson("/api/programs/{$program->id}/reject", [
        'notes' => 'Needs improvement',
    ]);

    $response->assertStatus(200)
        ->assertJsonFragment(['status' => 'draft']);
});

test('POST /api/programs/{id}/visibility toggles online/offline', function () {
    $program = VenueProgram::where('status', CampaignStatus::Published)->first();

    $response = $this->actingAs($this->moderator)->postJson("/api/programs/{$program->id}/visibility", [
        'visibility' => 'offline',
    ]);

    $response->assertStatus(200)
        ->assertJsonFragment(['visibility' => 'offline']);
});
