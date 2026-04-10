<?php

use App\Enums\NotificationType;
use App\Models\NotificationTemplate;
use App\Models\User;
use App\Services\Notification\NotificationService;

beforeEach(function () {
    $this->service = app(NotificationService::class);

    // Seed business parameters for retention days
    \App\Models\BusinessParameter::updateOrCreate(
        ['key' => 'notification_retention_days'],
        ['value' => '90', 'type' => 'integer', 'description' => 'Retention'],
    );
});

test('dispatch uses user locale to find template', function () {
    NotificationTemplate::create([
        'key' => 'test.locale',
        'locale' => 'en',
        'title_template' => 'English Title',
        'body_template' => 'English body for {{name}}',
        'type' => NotificationType::Inbox,
        'is_active' => true,
    ]);

    NotificationTemplate::create([
        'key' => 'test.locale',
        'locale' => 'zh',
        'title_template' => 'Chinese Title',
        'body_template' => 'Chinese body for {{name}}',
        'type' => NotificationType::Inbox,
        'is_active' => true,
    ]);

    $user = User::factory()->create(['locale' => 'zh']);

    $notification = $this->service->dispatch($user, 'test.locale', ['name' => 'Alice']);

    expect($notification)->not->toBeNull();
    expect($notification->title)->toBe('Chinese Title');
    expect($notification->body)->toBe('Chinese body for Alice');
    expect($notification->rendered_locale)->toBe('zh');
});

test('dispatch falls back to default locale when user locale template missing', function () {
    NotificationTemplate::create([
        'key' => 'test.fallback',
        'locale' => 'en',
        'title_template' => 'Fallback Title',
        'body_template' => 'Fallback body',
        'type' => NotificationType::Inbox,
        'is_active' => true,
    ]);

    // User has French locale but no French template exists
    $user = User::factory()->create(['locale' => 'fr']);

    $notification = $this->service->dispatch($user, 'test.fallback');

    expect($notification)->not->toBeNull();
    expect($notification->title)->toBe('Fallback Title');
    expect($notification->rendered_locale)->toBe('fr');
});

test('dispatch returns null when no template matches key', function () {
    $user = User::factory()->create();

    $notification = $this->service->dispatch($user, 'nonexistent.key');

    expect($notification)->toBeNull();
});

test('dispatch renders dates using user timezone', function () {
    NotificationTemplate::create([
        'key' => 'test.timezone',
        'locale' => 'en',
        'title_template' => 'Event at {{event_time}}',
        'body_template' => 'Your event is at {{event_time}}',
        'type' => NotificationType::Inbox,
        'is_active' => true,
    ]);

    $user = User::factory()->create(['timezone' => 'Asia/Tokyo']);

    $eventTime = \Carbon\Carbon::parse('2026-06-15 10:00:00', 'UTC');

    $notification = $this->service->dispatch($user, 'test.timezone', [
        'event_time' => $eventTime,
    ]);

    expect($notification)->not->toBeNull();
    // UTC 10:00 = JST 19:00 (Tokyo is UTC+9), rendered as 12-hour format
    expect($notification->title)->toContain('7:00 PM');
    expect($notification->title)->toContain('JST');
    expect($notification->rendered_timezone)->toBe('Asia/Tokyo');
});

test('dispatch stores rendered_timezone from user profile', function () {
    NotificationTemplate::create([
        'key' => 'test.tz.store',
        'locale' => 'en',
        'title_template' => 'Hello',
        'body_template' => 'World',
        'type' => NotificationType::Inbox,
        'is_active' => true,
    ]);

    $user = User::factory()->create(['timezone' => 'America/New_York']);

    $notification = $this->service->dispatch($user, 'test.tz.store');

    expect($notification->rendered_timezone)->toBe('America/New_York');
});

test('dispatch uses UTC for user with default timezone', function () {
    NotificationTemplate::create([
        'key' => 'test.tz.default',
        'locale' => 'en',
        'title_template' => 'Event at {{event_time}}',
        'body_template' => 'Body',
        'type' => NotificationType::Inbox,
        'is_active' => true,
    ]);

    $user = User::factory()->create(['timezone' => 'UTC']);

    $eventTime = \Carbon\Carbon::parse('2026-06-15 10:00:00', 'UTC');
    $notification = $this->service->dispatch($user, 'test.tz.default', [
        'event_time' => $eventTime,
    ]);

    expect($notification->rendered_timezone)->toBe('UTC');
    // UTC time should remain as-is: 10:00 AM
    expect($notification->title)->toContain('10:00 AM');
    expect($notification->title)->toContain('UTC');
});

test('dispatch selects Spanish template for es locale user', function () {
    NotificationTemplate::create([
        'key' => 'test.es',
        'locale' => 'en',
        'title_template' => 'English Title',
        'body_template' => 'English body',
        'type' => NotificationType::Inbox,
        'is_active' => true,
    ]);

    NotificationTemplate::create([
        'key' => 'test.es',
        'locale' => 'es',
        'title_template' => 'Título en Español',
        'body_template' => 'Cuerpo en español',
        'type' => NotificationType::Inbox,
        'is_active' => true,
    ]);

    $user = User::factory()->create(['locale' => 'es']);

    $notification = $this->service->dispatch($user, 'test.es');

    expect($notification->title)->toBe('Título en Español');
    expect($notification->rendered_locale)->toBe('es');
});

test('dispatch falls back from es to en when es template missing', function () {
    NotificationTemplate::create([
        'key' => 'test.es.fallback',
        'locale' => 'en',
        'title_template' => 'English Fallback',
        'body_template' => 'English fallback body',
        'type' => NotificationType::Inbox,
        'is_active' => true,
    ]);

    $user = User::factory()->create(['locale' => 'es']);

    $notification = $this->service->dispatch($user, 'test.es.fallback');

    expect($notification->title)->toBe('English Fallback');
    expect($notification->rendered_locale)->toBe('es');
});
