<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PatientService;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    private $service;

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
        return response()->json(
            $this->service->createPatient($request->all()),
            201
        );
    }

    public function show($id)
    {
        return response()->json($this->service->getPatient($id));
    }

    public function update(Request $request, $id)
    {
        return response()->json(
            $this->service->updatePatient($id, $request->all())
        );
    }

    public function destroy($id)
    {
        return response()->json(
            $this->service->deletePatient($id)
        );
    }
}
