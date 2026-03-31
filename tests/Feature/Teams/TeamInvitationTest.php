<?php

declare(strict_types = 1);

use App\Enums\ClinicaRole;
use App\Models\Clinica;
use App\Models\ClinicaInvitation;
use App\Models\User;
use Illuminate\Support\Facades\Notification;

test('clinica invitations can be created', function (): void {
    Notification::fake();

    $owner   = User::factory()->create();
    $clinica = Clinica::factory()->create();

    $clinica->members()->attach($owner, ['role' => ClinicaRole::Owner->value]);

    $response = $this
        ->actingAs($owner)
        ->post(route('clinicas.invitations.store', $clinica), [
            'email' => 'invited@example.com',
            'role'  => ClinicaRole::Member->value,
        ]);

    $response->assertRedirect(route('clinicas.edit', $clinica));

    $this->assertDatabaseHas('clinica_invitations', [
        'clinica_id' => $clinica->id,
        'email'      => 'invited@example.com',
        'role'       => ClinicaRole::Member->value,
    ]);
});

test('clinica invitations can be created by admins', function (): void {
    Notification::fake();

    $owner   = User::factory()->create();
    $admin   = User::factory()->create();
    $clinica = Clinica::factory()->create();

    $clinica->members()->attach($owner, ['role' => ClinicaRole::Owner->value]);
    $clinica->members()->attach($admin, ['role' => ClinicaRole::Admin->value]);

    $response = $this
        ->actingAs($admin)
        ->post(route('clinicas.invitations.store', $clinica), [
            'email' => 'invited@example.com',
            'role'  => ClinicaRole::Member->value,
        ]);

    $response->assertRedirect(route('clinicas.edit', $clinica));
});

test('existing clinica members cannot be invited', function (): void {
    Notification::fake();

    $owner   = User::factory()->create();
    $member  = User::factory()->create(['email' => 'member@example.com']);
    $clinica = Clinica::factory()->create();

    $clinica->members()->attach($owner, ['role' => ClinicaRole::Owner->value]);
    $clinica->members()->attach($member, ['role' => ClinicaRole::Member->value]);

    $response = $this
        ->actingAs($owner)
        ->post(route('clinicas.invitations.store', $clinica), [
            'email' => 'member@example.com',
            'role'  => ClinicaRole::Member->value,
        ]);

    $response->assertSessionHasErrors('email');
});

test('duplicate invitations cannot be created', function (): void {
    Notification::fake();

    $owner   = User::factory()->create();
    $clinica = Clinica::factory()->create();
    $clinica->members()->attach($owner, ['role' => ClinicaRole::Owner->value]);

    ClinicaInvitation::factory()->create([
        'clinica_id' => $clinica->id,
        'email'      => 'invited@example.com',
        'invited_by' => $owner->id,
    ]);

    $response = $this
        ->actingAs($owner)
        ->post(route('clinicas.invitations.store', $clinica), [
            'email' => 'invited@example.com',
            'role'  => ClinicaRole::Member->value,
        ]);

    $response->assertSessionHasErrors('email');
});

test('clinica invitations cannot be created by members', function (): void {
    $owner   = User::factory()->create();
    $member  = User::factory()->create();
    $clinica = Clinica::factory()->create();

    $clinica->members()->attach($owner, ['role' => ClinicaRole::Owner->value]);
    $clinica->members()->attach($member, ['role' => ClinicaRole::Member->value]);

    $response = $this
        ->actingAs($member)
        ->post(route('clinicas.invitations.store', $clinica), [
            'email' => 'invited@example.com',
            'role'  => ClinicaRole::Member->value,
        ]);

    $response->assertForbidden();
});

test('clinica invitations can be cancelled by owners', function (): void {
    $owner   = User::factory()->create();
    $clinica = Clinica::factory()->create();

    $clinica->members()->attach($owner, ['role' => ClinicaRole::Owner->value]);

    $invitation = ClinicaInvitation::factory()->create([
        'clinica_id' => $clinica->id,
        'invited_by' => $owner->id,
    ]);

    $response = $this
        ->actingAs($owner)
        ->delete(route('clinicas.invitations.destroy', [$clinica, $invitation]));

    $response->assertRedirect(route('clinicas.edit', $clinica));

    $this->assertDatabaseMissing('clinica_invitations', [
        'id' => $invitation->id,
    ]);
});

test('clinica invitations can be accepted', function (): void {
    $owner       = User::factory()->create();
    $invitedUser = User::factory()->create(['email' => 'invited@example.com']);
    $clinica     = Clinica::factory()->create();

    $clinica->members()->attach($owner, ['role' => ClinicaRole::Owner->value]);

    $invitation = ClinicaInvitation::factory()->create([
        'clinica_id' => $clinica->id,
        'email'      => 'invited@example.com',
        'role'       => ClinicaRole::Member,
        'invited_by' => $owner->id,
    ]);

    $response = $this
        ->actingAs($invitedUser)
        ->get(route('invitations.accept', $invitation));

    $response->assertRedirect(route('dashboard'));

    expect($invitedUser->fresh()->belongsToClinica($clinica))->toBeTrue();
    expect($invitation->fresh()->accepted_at)->not->toBeNull();
});

test('clinica invitations cannot be accepted by uninvited user', function (): void {
    $owner         = User::factory()->create();
    $uninvitedUser = User::factory()->create(['email' => 'uninvited@example.com']);
    $clinica       = Clinica::factory()->create();

    $clinica->members()->attach($owner, ['role' => ClinicaRole::Owner->value]);

    $invitation = ClinicaInvitation::factory()->create([
        'clinica_id' => $clinica->id,
        'email'      => 'invited@example.com',
        'invited_by' => $owner->id,
    ]);

    $response = $this
        ->actingAs($uninvitedUser)
        ->get(route('invitations.accept', $invitation));

    $response->assertSessionHasErrors('invitation');

    expect($uninvitedUser->fresh()->belongsToClinica($clinica))->toBeFalse();
});

test('expired invitations cannot be accepted', function (): void {
    $owner       = User::factory()->create();
    $invitedUser = User::factory()->create(['email' => 'invited@example.com']);
    $clinica     = Clinica::factory()->create();

    $clinica->members()->attach($owner, ['role' => ClinicaRole::Owner->value]);

    $invitation = ClinicaInvitation::factory()->expired()->create([
        'clinica_id' => $clinica->id,
        'email'      => 'invited@example.com',
        'invited_by' => $owner->id,
    ]);

    $response = $this
        ->actingAs($invitedUser)
        ->get(route('invitations.accept', $invitation));

    $response->assertSessionHasErrors('invitation');

    expect($invitedUser->fresh()->belongsToClinica($clinica))->toBeFalse();
});
