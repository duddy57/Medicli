<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Clinicas;

use App\Http\Controllers\Controller;
use App\Models\Clinica;
use App\Models\Specialty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class SpecialtyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, Clinica $currentClinica)
    {
        Gate::authorize('view', $currentClinica);

        return Inertia::render('clinicas/specialties/index', [
            'clinica'     => $currentClinica,
            'specialties' => $currentClinica->specialties()->latest()->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Clinica $currentClinica)
    {
        Gate::authorize('update', $currentClinica);

        $validated = $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $currentClinica->specialties()->create($validated);

        return back()->with('success', 'Especialidade criada com sucesso.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Clinica $currentClinica, Specialty $specialty)
    {
        Gate::authorize('update', $currentClinica);
        abort_unless($specialty->clinica_id === $currentClinica->id, 404);

        $validated = $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $specialty->update($validated);

        return back()->with('success', 'Especialidade atualizada com sucesso.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Clinica $currentClinica, Specialty $specialty)
    {
        Gate::authorize('update', $currentClinica);
        abort_unless($specialty->clinica_id === $currentClinica->id, 404);

        $specialty->delete();

        return back()->with('success', 'Especialidade excluída com sucesso.');
    }
}
