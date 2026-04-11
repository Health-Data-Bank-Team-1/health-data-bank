<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\StoreHealthGoalRequest;
use App\Http\Controllers\UpdateHealthGoalRequest;
use App\Models\HealthGoal;
use App\Services\AuditLogger;
use App\Services\GoalProgressService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HealthGoalController extends Controller
{
    public function __construct(
        private GoalProgressService $progressService
    ) {
    }

    private function resolveAccountId(Request $request): ?string
    {
        return DB::table('accounts')
            ->where('email', $request->user()->email)
            ->value('id');
    }

    public function index(Request $request): JsonResponse
    {
        $accountId = $this->resolveAccountId($request);

        if (!$accountId) {
            AuditLogger::log(
                'health_goal_index_blocked',
                ['goals', 'resource:health_goal', 'outcome:blocked'],
                null,
                [],
                ['reason_code' => 'account_mapping_failed']
            );

            abort(403, 'Account mapping failed.');
        }

        $goals = HealthGoal::where('account_id', $accountId)
            ->orderByDesc('created_at')
            ->get()
            ->map(function (HealthGoal $goal) {
                return [
                    'goal' => $goal,
                    'progress' => $this->progressService->calculate($goal),
                ];
            });

        AuditLogger::log(
            'health_goal_index_viewed',
            ['goals', 'resource:health_goal', 'outcome:success'],
            null,
            [],
            ['goal_count' => $goals->count()]
        );

        return response()->json($goals);
        return ApiResponse::success(
        [
            'goal' => $goal,
            'progress' => $this->progressService->calculate($goal),
        ],
        'Goal created successfully.',
        201
    );
    }

    public function store(StoreHealthGoalRequest $request): JsonResponse
    {
        $accountId = $this->resolveAccountId($request);

        if (!$accountId) {
            AuditLogger::log(
                'health_goal_create_blocked',
                ['goals', 'resource:health_goal', 'outcome:blocked'],
                null,
                [],
                ['reason_code' => 'account_mapping_failed']
            );

            abort(403, 'Account mapping failed.');
        }

        $goal = HealthGoal::create([
            'account_id' => $accountId,
            ...$request->validated(),
        ]);

        AuditLogger::log(
            'health_goal_created',
            ['goals', 'resource:health_goal', 'outcome:success'],
            $goal,
            [],
            [
                'goal_id' => $goal->id,
                'metric_key' => $goal->metric_key,
                'timeframe' => $goal->timeframe,
                'status' => $goal->status,
            ]
        );

        return response()->json([
            'goal' => $goal,
            'progress' => $this->progressService->calculate($goal),
        ], 201);
    }

    public function show(Request $request, string $goalId): JsonResponse
    {
        $accountId = $this->resolveAccountId($request);

        if (!$accountId) {
            AuditLogger::log(
                'health_goal_view_blocked',
                ['goals', 'resource:health_goal', 'outcome:blocked'],
                null,
                [],
                ['reason_code' => 'account_mapping_failed']
            );

            abort(403, 'Account mapping failed.');
        }

        $goal = HealthGoal::where('account_id', $accountId)
            ->findOrFail($goalId);

        AuditLogger::log(
            'health_goal_viewed',
            ['goals', 'resource:health_goal', 'outcome:success'],
            $goal,
            [],
            [
                'goal_id' => $goal->id,
                'metric_key' => $goal->metric_key,
                'timeframe' => $goal->timeframe,
                'status' => $goal->status,
            ]
        );

        return response()->json([
            'goal' => $goal,
            'progress' => $this->progressService->calculate($goal),
        ]);
    }

    public function update(UpdateHealthGoalRequest $request, string $goalId): JsonResponse
    {
        $accountId = $this->resolveAccountId($request);

        if (!$accountId) {
            AuditLogger::log(
                'health_goal_update_blocked',
                ['goals', 'resource:health_goal', 'outcome:blocked'],
                null,
                [],
                ['reason_code' => 'account_mapping_failed']
            );

            abort(403, 'Account mapping failed.');
        }

        $goal = HealthGoal::where('account_id', $accountId)
            ->findOrFail($goalId);

        $goal->update($request->validated());

        $freshGoal = $goal->fresh();

        AuditLogger::log(
            'health_goal_updated',
            ['goals', 'resource:health_goal', 'outcome:success'],
            $freshGoal,
            [],
            [
                'goal_id' => $freshGoal->id,
                'metric_key' => $freshGoal->metric_key,
                'timeframe' => $freshGoal->timeframe,
                'status' => $freshGoal->status,
            ]
        );

        return response()->json([
            'goal' => $freshGoal,
            'progress' => $this->progressService->calculate($freshGoal),
        ]);
    }
}
