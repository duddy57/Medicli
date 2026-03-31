<?php

declare(strict_types = 1);

use App\Enums\ClinicaRole;
use App\Models\Clinica;
use App\Models\User;

test('the clinicas index page can be rendered', function (): void {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('clinicas.index'));

    $response->assertOk();
});

test('clinicas can be created', function (): void {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->post(route('clinicas.store'), [
            'name' => 'Test Clinica',
        ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('clinicas', [
        'name'        => 'Test Clinica',
        'is_personal' => false,
    ]);
});

test('clinica slug uses next available suffix', function (): void {
    $user = User::factory()->create();

    Clinica::factory()->create(['name' => 'Acme', 'slug' => 'acme']);
    Clinica::factory()->create(['name' => 'Acme One', 'slug' => 'acme-1']);
    Clinica::factory()->create(['name' => 'Acme Ten', 'slug' => 'acme-10']);

    $this
        ->actingAs($user)
        ->post(route('clinicas.store'), [
            'name' => 'Acme',
        ]);

    $this->assertDatabaseHas('clinicas', [
        'name' => 'Acme',
        'slug' => 'acme-11',
    ]);
});

test('the clinica edit page can be rendered', function (): void {
    $user    = User::factory()->create();
    $clinica = Clinica::factory()->create();

    $clinica->members()->attach($user, ['role' => ClinicaRole::Owner->value]);

    $response = $this
        ->actingAs($user)
        ->get(route('clinicas.edit', $clinica));

    $response->assertOk();
});

test('clinicas can be updated by owners', function (): void {
    $user    = User::factory()->create();
    $clinica = Clinica::factory()->create(['name' => 'Original Name']);

    $clinica->members()->attach($user, ['role' => ClinicaRole::Owner->value]);

    $response = $this
        ->actingAs($user)
        ->patch(route('clinicas.update', $clinica), [
            'name' => 'Updated Name',
        ]);

    $response->assertRedirect(route('clinicas.edit', $clinica->fresh()));

    $this->assertDatabaseHas('clinicas', [
        'id'   => $clinica->id,
        'name' => 'Updated Name',
    ]);
});

test('clinicas cannot be updated by members', function (): void {
    $owner   = User::factory()->create();
    $member  = User::factory()->create();
    $clinica = Clinica::factory()->create();

    $clinica->members()->attach($owner, ['role' => ClinicaRole::Owner->value]);
    $clinica->members()->attach($member, ['role' => ClinicaRole::Member->value]);

    $response = $this
        ->actingAs($member)
        ->patch(route('clinicas.update', $clinica), [
            'name' => 'Updated Name',
        ]);

    $response->assertForbidden();
});

test('clinicas can be deleted by owners', function (): void {
    $user    = User::factory()->create();
    $clinica = Clinica::factory()->create();

    $clinica->members()->attach($user, ['role' => ClinicaRole::Owner->value]);

    $response = $this
        ->actingAs($user)
        ->delete(route('clinicas.destroy', $clinica), [
            'name' => $clinica->name,
        ]);

    $response->assertRedirect();

    $this->assertSoftDeleted('clinicas', [
        'id' => $clinica->id,
    ]);
});

test('clinica deletion requires name confirmation', function (): void {
    $user    = User::factory()->create();
    $clinica = Clinica::factory()->create();

    $clinica->members()->attach($user, ['role' => ClinicaRole::Owner->value]);

    $response = $this
        ->actingAs($user)
        ->delete(route('clinicas.destroy', $clinica), [
            'name' => 'Wrong Name',
        ]);

    $response->assertSessionHasErrors('name');

    $this->assertDatabaseHas('clinicas', [
        'id'         => $clinica->id,
        'deleted_at' => null,
    ]);
});

test('deleting current clinica switches to alphabetically first remaining clinica', function (): void {
    $user = User::factory()->create(['name' => 'Mike']);

    $zuluClinica = Clinica::factory()->create(['name' => 'Zulu Clinica']);
    $zuluClinica->members()->attach($user, ['role' => ClinicaRole::Owner->value]);

    $alphaClinica = Clinica::factory()->create(['name' => 'Alpha Clinica']);
    $alphaClinica->members()->attach($user, ['role' => ClinicaRole::Owner->value]);

    $betaClinica = Clinica::factory()->create(['name' => 'Beta Clinica']);
    $betaClinica->members()->attach($user, ['role' => ClinicaRole::Owner->value]);

    $user->update(['current_clinica_id' => $zuluClinica->id]);

    $response = $this
        ->actingAs($user)
        ->delete(route('clinicas.destroy', $zuluClinica), [
            'name' => $zuluClinica->name,
        ]);

    $response->assertRedirect();

    $this->assertSoftDeleted('clinicas', [
        'id' => $zuluClinica->id,
    ]);

    expect($user->fresh()->current_clinica_id)->toEqual($alphaClinica->id);
});

test('deleting current clinica falls back to personal clinica when alphabetically first', function (): void {
    $user            = User::factory()->create();
    $personalClinica = $user->personalClinica();
    $clinica         = Clinica::factory()->create(['name' => 'Zulu Clinica']);
    $clinica->members()->attach($user, ['role' => ClinicaRole::Owner->value]);

    $user->update(['current_clinica_id' => $clinica->id]);

    $response = $this
        ->actingAs($user)
        ->delete(route('clinicas.destroy', $clinica), [
            'name' => $clinica->name,
        ]);

    $response->assertRedirect();

    $this->assertSoftDeleted('clinicas', [
        'id' => $clinica->id,
    ]);

    expect($user->fresh()->current_clinica_id)->toEqual($personalClinica->id);
});

test('deleting non current clinica leaves current clinica unchanged', function (): void {
    $user            = User::factory()->create();
    $personalClinica = $user->personalClinica();
    $clinica         = Clinica::factory()->create();
    $clinica->members()->attach($user, ['role' => ClinicaRole::Owner->value]);

    $user->update(['current_clinica_id' => $personalClinica->id]);

    $response = $this
        ->actingAs($user)
        ->delete(route('clinicas.destroy', $clinica), [
            'name' => $clinica->name,
        ]);

    $response->assertRedirect();

    $this->assertSoftDeleted('clinicas', [
        'id' => $clinica->id,
    ]);

    expect($user->fresh()->current_clinica_id)->toEqual($personalClinica->id);
});

test('deleting clinica switches other affected users to their personal clinica', function (): void {
    $owner  = User::factory()->create();
    $member = User::factory()->create();

    $clinica = Clinica::factory()->create();
    $clinica->members()->attach($owner, ['role' => ClinicaRole::Owner->value]);
    $clinica->members()->attach($member, ['role' => ClinicaRole::Member->value]);

    $owner->update(['current_clinica_id' => $clinica->id]);
    $member->update(['current_clinica_id' => $clinica->id]);

    $response = $this
        ->actingAs($owner)
        ->delete(route('clinicas.destroy', $clinica), [
            'name' => $clinica->name,
        ]);

    $response->assertRedirect();

    expect($member->fresh()->current_clinica_id)->toEqual($member->personalClinica()->id);
});

test('personal clinicas cannot be deleted', function (): void {
    $user = User::factory()->create();

    $personalClinica = $user->personalClinica();

    $response = $this
        ->actingAs($user)
        ->delete(route('clinicas.destroy', $personalClinica), [
            'name' => $personalClinica->name,
        ]);

    $response->assertForbidden();

    $this->assertDatabaseHas('clinicas', [
        'id'         => $personalClinica->id,
        'deleted_at' => null,
    ]);
});

test('clinicas cannot be deleted by non owners', function (): void {
    $owner   = User::factory()->create();
    $member  = User::factory()->create();
    $clinica = Clinica::factory()->create();

    $clinica->members()->attach($owner, ['role' => ClinicaRole::Owner->value]);
    $clinica->members()->attach($member, ['role' => ClinicaRole::Member->value]);

    $response = $this
        ->actingAs($member)
        ->delete(route('clinicas.destroy', $clinica), [
            'name' => $clinica->name,
        ]);

    $response->assertForbidden();
});

test('users can switch clinicas', function (): void {
    $user    = User::factory()->create();
    $clinica = Clinica::factory()->create();

    $clinica->members()->attach($user, ['role' => ClinicaRole::Member->value]);

    $response = $this
        ->actingAs($user)
        ->post(route('clinicas.switch', $clinica));

    $response->assertRedirect();

    expect($user->fresh()->current_clinica_id)->toEqual($clinica->id);
});

test('users cannot switch to clinica they dont belong to', function (): void {
    $user    = User::factory()->create();
    $clinica = Clinica::factory()->create();

    $response = $this
        ->actingAs($user)
        ->post(route('clinicas.switch', $clinica));

    $response->assertForbidden();
});

test('guests cannot access clinicas', function (): void {
    $response = $this->get(route('clinicas.index'));

    $response->assertRedirect(route('login'));
});
