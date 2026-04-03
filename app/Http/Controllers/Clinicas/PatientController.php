<?php

declare(strict_types=1);

namespace App\Http\Controllers\Clinicas;

use App\Http\Controllers\Controller;
use App\Models\Clinica;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class PatientController extends Controller
{
    public function index(Request $request, Clinica $currentClinica)
    {
        Gate::authorize('view', $currentClinica);

        return Inertia::render('clinicas/patients/index', [
            'clinica'  => $currentClinica,
            'patients' => $currentClinica->patients()->latest()->get(),
        ]);
    }

    public function store(Request $request, Clinica $currentClinica)
    {
        Gate::authorize('update', $currentClinica);

        $validated = $request->validate([
            'name'    => ['required', 'string', 'max:255'],
            'email'   => ['required', 'email', 'max:255'],
            'phone'   => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
            'age'     => ['nullable', 'integer'],
            'gender'  => ['nullable', 'string'],
        ]);

        $currentClinica->patients()->create($validated);

        return back()->with('success', 'Paciente criado com sucesso.');
    }

    public function update(Request $request, Clinica $currentClinica, Patient $patient)
    {
        Gate::authorize('update', $currentClinica);
        abort_unless($patient->clinica_id === $currentClinica->id, 404);

        $validated = $request->validate([
            'name'    => ['required', 'string', 'max:255'],
            'email'   => ['required', 'email', 'max:255'],
            'phone'   => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
            'age'     => ['nullable', 'integer'],
            'gender'  => ['nullable', 'string'],
        ]);

        $patient->update($validated);

        return back()->with('success', 'Paciente atualizado com sucesso.');
    }

    public function destroy(Clinica $currentClinica, Patient $patient)
    {
        Gate::authorize('update', $currentClinica);
        abort_unless($patient->clinica_id === $currentClinica->id, 404);

        $patient->delete();

        return back()->with('success', 'Paciente excluído com sucesso.');
    }
}