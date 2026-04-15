<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PatientService;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    private PatientService $service;

    public function __construct(PatientService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        return response()->json($this->service->listPatients());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
        ]);

        return response()->json(
            $this->service->createPatient($validated),
            201
        );
    }

    public function show($id)
    {
        $patient = $this->service->getPatient($id);

        if (!$patient) {
            return response()->json(['message' => 'Patient not found.'], 404);
        }

        return response()->json($patient);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255'],
        ]);

        if (empty($validated)) {
            return response()->json([
                'message' => 'At least one updatable field is required.',
            ], 422);
        }

        $updated = $this->service->updatePatient($id, $validated);

        if (!$updated) {
            return response()->json(['message' => 'Patient not found.'], 404);
        }

        return response()->json(
            $updated
        );
    }

    public function destroy($id)
    {
        return response()->json(
            $this->service->deletePatient($id)
        );
    }
}
