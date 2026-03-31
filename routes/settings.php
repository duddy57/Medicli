<?php

declare(strict_types = 1);

use App\Http\Controllers\Clinicas\ClinicaController;
use App\Http\Controllers\Clinicas\ClinicaInvitationController;
use App\Http\Controllers\Clinicas\ClinicaMemberController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\SecurityController;
use App\Http\Middleware\EnsureClinicaMembership;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function (): void {
    Route::redirect('settings', '/settings/profile');

    Route::get('settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('settings/profile', [ProfileController::class, 'update'])->name('profile.update');
});

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('settings/security', [SecurityController::class, 'edit'])->name('security.edit');

    Route::put('settings/password', [SecurityController::class, 'update'])
        ->middleware('throttle:6,1')
        ->name('user-password.update');

    Route::inertia('settings/appearance', 'settings/appearance')->name('appearance.edit');

    Route::get('settings/clinicas', [ClinicaController::class, 'index'])->name('clinicas.index');
    Route::post('settings/clinicas', [ClinicaController::class, 'store'])->name('clinicas.store');

    Route::middleware(EnsureClinicaMembership::class)->group(function (): void {
        Route::get('settings/clinicas/{clinica}', [ClinicaController::class, 'edit'])->name('clinicas.edit');
        Route::patch('settings/clinicas/{clinica}', [ClinicaController::class, 'update'])->name('clinicas.update');
        Route::delete('settings/clinicas/{clinica}', [ClinicaController::class, 'destroy'])->name('clinicas.destroy');
        Route::post('settings/clinicas/{clinica}/switch', [ClinicaController::class, 'switch'])->name('clinicas.switch');

        Route::patch('settings/clinicas/{clinica}/members/{user}', [ClinicaMemberController::class, 'update'])->name('clinicas.members.update');
        Route::delete('settings/clinicas/{clinica}/members/{user}', [ClinicaMemberController::class, 'destroy'])->name('clinicas.members.destroy');

        Route::post('settings/clinicas/{clinica}/invitations', [ClinicaInvitationController::class, 'store'])->name('clinicas.invitations.store');
        Route::delete('settings/clinicas/{clinica}/invitations/{invitation}', [ClinicaInvitationController::class, 'destroy'])->name('clinicas.invitations.destroy');
    });
});
