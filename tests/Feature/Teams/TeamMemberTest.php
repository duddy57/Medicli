<?php

declare(strict_types = 1);

use App\Enums\ClinicaRole;
use App\Models\Clinica;
use App\Models\User;

test('clinica member roles can be updated by owners', function (): void {
    $owner   = User::factory()->create();
    $member  = User::factory()->create();
    $clinica = Clinica::factory()->create();

    $clinica->members()->attach($owner, ['role' => ClinicaRole::Owner->value]);
    $clinica->members()->attach($member, ['role' => ClinicaRole::Member->value]);

    $response = $this
        ->actingAs($owner)
        ->patch(route('clinicas.members.update', [$clinica, $member]), [
            'role' => ClinicaRole::Admin->value,
        ]);

    $response->assertRedirect(route('clinicas.edit', $clinica));

    expect($clinica->members()->where('user_id', $member->id)->first()->pivot->role->value)->toEqual(ClinicaRole::Admin->value);
});

test('clinica member roles cannot be updated by non owners', function (): void {
    $owner   = User::factory()->create();
    $admin   = User::factory()->create();
    $member  = User::factory()->create();
    $clinica = Clinica::factory()->create();

    $clinica->members()->attach($owner, ['role' => ClinicaRole::Owner->value]);
    $clinica->members()->attach($admin, ['role' => ClinicaRole::Admin->value]);
    $clinica->members()->attach($member, ['role' => ClinicaRole::Member->value]);

    $response = $this
        ->actingAs($admin)
        ->patch(route('clinicas.members.update', [$clinica, $member]), [
            'role' => ClinicaRole::Admin->value,
        ]);

    $response->assertForbidden();
});

test('clinica members can be removed by owners', function (): void {
    $owner   = User::factory()->create();
    $member  = User::factory()->create();
    $clinica = Clinica::factory()->create();

    $clinica->members()->attach($owner, ['role' => ClinicaRole::Owner->value]);
    $clinica->members()->attach($member, ['role' => ClinicaRole::Member->value]);

    $response = $this
        ->actingAs($owner)
        ->delete(route('clinicas.members.destroy', [$clinica, $member]));

    $response->assertRedirect(route('clinicas.edit', $clinica));

    expect($member->fresh()->belongsToClinica($clinica))->toBeFalse();
});

test('clinica members cannot be removed by non owners', function (): void {
    $owner   = User::factory()->create();
    $admin   = User::factory()->create();
    $member  = User::factory()->create();
    $clinica = Clinica::factory()->create();

    $clinica->members()->attach($owner, ['role' => ClinicaRole::Owner->value]);
    $clinica->members()->attach($admin, ['role' => ClinicaRole::Admin->value]);
    $clinica->members()->attach($member, ['role' => ClinicaRole::Member->value]);

    $response = $this
        ->actingAs($admin)
        ->delete(route('clinicas.members.destroy', [$clinica, $member]));

    $response->assertForbidden();
});

test('clinica owner cannot be removed', function (): void {
    $owner   = User::factory()->create();
    $clinica = Clinica::factory()->create();

    $clinica->members()->attach($owner, ['role' => ClinicaRole::Owner->value]);

    $response = $this
        ->actingAs($owner)
        ->delete(route('clinicas.members.destroy', [$clinica, $owner]));

    $response->assertForbidden();

    expect($owner->fresh()->belongsToClinica($clinica))->toBeTrue();
});

test('clinica member role cannot be set to owner', function (): void {
    $owner   = User::factory()->create();
    $member  = User::factory()->create();
    $clinica = Clinica::factory()->create();

    $clinica->members()->attach($owner, ['role' => ClinicaRole::Owner->value]);
    $clinica->members()->attach($member, ['role' => ClinicaRole::Member->value]);

    $response = $this
        ->actingAs($owner)
        ->patch(route('clinicas.members.update', [$clinica, $member]), [
            'role' => ClinicaRole::Owner->value,
        ]);

    $response->assertSessionHasErrors('role');

    expect($clinica->members()->where('user_id', $member->id)->first()->pivot->role->value)->toEqual(ClinicaRole::Member->value);
});

test('removed member current clinica is set to personal clinica', function (): void {
    $owner           = User::factory()->create();
    $member          = User::factory()->create();
    $personalClinica = $member->personalClinica();
    $clinica         = Clinica::factory()->create();

    $clinica->members()->attach($owner, ['role' => ClinicaRole::Owner->value]);
    $clinica->members()->attach($member, ['role' => ClinicaRole::Member->value]);

    $member->update(['current_clinica_id' => $clinica->id]);

    $this
        ->actingAs($owner)
        ->delete(route('clinicas.members.destroy', [$clinica, $member]));

    expect($member->fresh()->current_clinica_id)->toEqual($personalClinica->id);
});
