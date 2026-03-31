<?php

declare(strict_types = 1);

use App\Enums\ClinicaRole;
use App\Models\Clinica;
use App\Models\Specialty;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('specialties can be listed', function (): void {
    $user    = User::factory()->create();
    $clinica = Clinica::create([
        'name'     => 'Test Clinica',
        'owner_id' => $user->id,
    ]);

    $clinica->memberships()->create([
        'user_id' => $user->id,
        'role'    => ClinicaRole::Owner,
    ]);

    // Update current_clinica_id directly
    $user->update(['current_clinica_id' => $clinica->id]);
    $user->refresh();

    Specialty::factory()->count(3)->create(['clinica_id' => $clinica->id]);

    $response = $this->actingAs($user)
        ->get(route('specialties.index', $clinica));

    $response->assertStatus(200)
        ->assertInertia(
            fn ($page) => $page
                ->component('clinicas/specialties/index')
                ->has('specialties', 3)
        );
});

test('specialty can be created', function (): void {
    $user    = User::factory()->create();
    $clinica = Clinica::create([
        'name'     => 'Test Clinica',
        'owner_id' => $user->id,
    ]);

    $clinica->memberships()->create([
        'user_id' => $user->id,
        'role'    => ClinicaRole::Owner,
    ]);

    $user->switchClinica($clinica);

    $response = $this->actingAs($user)
        ->post(route('specialties.store', $clinica), [
            'title'       => 'Cardiologia',
            'description' => 'Test Description',
        ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('specialties', [
        'clinica_id' => $clinica->id,
        'title'      => 'Cardiologia',
    ]);
});

test('specialty can be updated', function (): void {
    $user    = User::factory()->create();
    $clinica = Clinica::create([
        'name'     => 'Test Clinica',
        'owner_id' => $user->id,
    ]);

    $clinica->memberships()->create([
        'user_id' => $user->id,
        'role'    => ClinicaRole::Owner,
    ]);

    $user->switchClinica($clinica);

    $specialty = Specialty::create([
        'clinica_id' => $clinica->id,
        'title'      => 'Old Title',
    ]);

    $response = $this->actingAs($user)
        ->patch(route('specialties.update', [$clinica, $specialty]), [
            'title' => 'New Title',
        ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('specialties', [
        'id'    => $specialty->id,
        'title' => 'New Title',
    ]);
});

test('specialty can be deleted', function (): void {
    $user    = User::factory()->create();
    $clinica = Clinica::create([
        'name'     => 'Test Clinica',
        'owner_id' => $user->id,
    ]);

    $clinica->memberships()->create([
        'user_id' => $user->id,
        'role'    => ClinicaRole::Owner,
    ]);

    $user->switchClinica($clinica);

    $specialty = Specialty::create([
        'clinica_id' => $clinica->id,
        'title'      => 'To Delete',
    ]);

    $response = $this->actingAs($user)
        ->delete(route('specialties.destroy', [$clinica, $specialty]));

    $response->assertRedirect();
    $this->assertDatabaseMissing('specialties', [
        'id' => $specialty->id,
    ]);
});
