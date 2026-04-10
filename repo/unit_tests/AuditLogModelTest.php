<?php

use App\Models\AuditLog;
use App\Models\User;

test('creating an AuditLog works', function () {
    $user = User::factory()->create();

    $log = AuditLog::create([
        'actor_id' => $user->id,
        'actor_ip' => '127.0.0.1',
        'action' => 'test.action',
        'auditable_type' => User::class,
        'auditable_id' => $user->id,
        'old_values' => ['key' => 'old'],
        'new_values' => ['key' => 'new'],
    ]);

    expect($log)->toBeInstanceOf(AuditLog::class);
    expect($log->id)->not->toBeNull();
    expect($log->action)->toBe('test.action');
});

test('attempting to update an existing AuditLog throws LogicException', function () {
    $user = User::factory()->create();

    $log = AuditLog::create([
        'actor_id' => $user->id,
        'actor_ip' => '127.0.0.1',
        'action' => 'test.action',
        'auditable_type' => User::class,
        'auditable_id' => $user->id,
        'old_values' => ['key' => 'old'],
        'new_values' => ['key' => 'new'],
    ]);

    $log->action = 'modified.action';
    $log->save();
})->throws(LogicException::class, 'Audit logs are immutable and cannot be updated.');

test('attempting to delete an existing AuditLog throws LogicException', function () {
    $user = User::factory()->create();

    $log = AuditLog::create([
        'actor_id' => $user->id,
        'actor_ip' => '127.0.0.1',
        'action' => 'test.action',
        'auditable_type' => User::class,
        'auditable_id' => $user->id,
        'old_values' => [],
        'new_values' => [],
    ]);

    $log->delete();
})->throws(LogicException::class, 'Audit logs are immutable and cannot be deleted.');
