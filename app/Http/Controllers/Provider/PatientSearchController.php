<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Services\AuditLogger;
use Illuminate\Http\Request;

class PatientSearchController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'q' => ['sometimes', 'string', 'max:255'],
            'status' => ['sometimes', 'in:ACTIVE,DEACTIVATED'],
        ]);

        $query = Account::query()
            ->where('account_type', 'User');

        if (!empty($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        if (!empty($validated['q'])) {
            $search = $validated['q'];

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        $patients = $query
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'status', 'account_type']);

        AuditLogger::log(
            'provider_patient_search',
            ['provider', 'resource:patient_search'],
            null,
            [],
            [
                'q' => $validated['q'] ?? null,
                'status' => $validated['status'] ?? null,
            ]
        );

        return response()->json([
            'data' => $patients,
        ]);
    }
}
