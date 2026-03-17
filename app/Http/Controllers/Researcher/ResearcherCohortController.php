<?php

namespace App\Http\Controllers\Researcher;

use App\Http\Controllers\Controller;
use App\Services\AuditLogger;
use App\Services\CohortFilterBuilder;
use App\Services\KThresholdService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Exceptions\CohortSuppressedException;

class ResearcherCohortController extends Controller
{
    public function store(
        Request $request,
        CohortFilterBuilder $cohortBuilder,
        KThresholdService $threshold
    ) {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'purpose' => ['required', 'string', 'max:500'],

            // demographic / participant filters
            'age_min' => ['sometimes', 'integer', 'min:0', 'max:120'],
            'age_max' => ['sometimes', 'integer', 'min:0', 'max:120', 'gte:age_min'],
            'gender' => ['sometimes', 'string', 'max:50'],

            // participant status / enrollment filters
            'account_status' => ['sometimes', 'in:ACTIVE,DEACTIVATED'],
            'created_from' => ['sometimes', 'date'],
            'created_to' => ['sometimes', 'date', 'after_or_equal:created_from'],
        ]);
        
        $filters = [
            'account_type' => 'User',
            'account_status' => $validated['account_status'] ?? 'ACTIVE',
            'age_min' => $validated['age_min'] ?? null,
            'age_max' => $validated['age_max'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'created_from' => $validated['created_from'] ?? null,
            'created_to' => $validated['created_to'] ?? null,
        ];

        $filters = array_filter($filters, fn ($value) => $value !== null);

        try {
            $cohortQuery = $cohortBuilder->build($filters);

            $accountIds = $cohortQuery->pluck('id')->all();
            $cohortSize = count($accountIds);

            $threshold->enforce($cohortSize, 10);

            $user = $request->user();
            $cohortId = Str::uuid()->toString();

            DB::table('researcher_cohorts')->insert([
                'id' => $cohortId,
                'name' => $validated['name'],
                'purpose' => $validated['purpose'],
                'filters_json' => json_encode($filters),
                'estimated_size' => $cohortSize,
                'version' => 1,
                'created_by' => $user?->account_id ?? $user?->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            AuditLogger::log(
                'researcher_cohort_created',
                ['reporting', 'researcher', 'outcome:success'],
                null,
                [],
                [
                    'cohort_id' => $cohortId,
                    'cohort_size' => $cohortSize,
                    'filter_keys' => array_keys($filters),
                    'version' => 1,
                ]
            );

            return response()->json([
                'message' => 'Cohort created successfully.',
                'data' => [
                    'id' => $cohortId,
                    'name' => $validated['name'],
                    'purpose' => $validated['purpose'],
                    'filters' => $filters,
                    'estimated_cohort_size' => $cohortSize,
                    'minimum_required' => 10,
                    'version' => 1,
                    'saved' => true,
                ],
            ], 201);
        } catch (CohortSuppressedException $e) {
            AuditLogger::log(
                'researcher_cohort_rejected',
                ['reporting', 'researcher', 'outcome:blocked', 'reason:k_threshold'],
                null,
                [],
                [
                    'filter_keys' => array_keys($filters),
                ]
            );

            return response()->json([
                'message' => 'Cohort does not meet minimum anonymity size.',
                'errors' => [
                    'cohort' => [$e->getMessage()],
                ],
            ], 422);
        }
    }
}
