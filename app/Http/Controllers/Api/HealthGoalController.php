<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\StoreHealthGoalRequest;
use App\Http\Controllers\UpdateHealthGoalRequest;
use App\Models\HealthGoal;
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

        abort_unless($accountId, 403, 'Account mapping failed.');

        $goals = HealthGoal::where('account_id', $accountId)
            ->orderByDesc('created_at')
            ->get()
            ->map(function (HealthGoal $goal) {
                return [
                    'goal' => $goal,
                    'progress' => $this->progressService->calculate($goal),
                ];
            });

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

        abort_unless($accountId, 403, 'Account mapping failed.');

        $goal = HealthGoal::create([
            'account_id' => $accountId,
            ...$request->validated(),
        ]);

        return response()->json([
            'goal' => $goal,
            'progress' => $this->progressService->calculate($goal),
        ], 201);
    }

    public function show(Request $request, string $goalId): JsonResponse
    {
        $accountId = $this->resolveAccountId($request);

        abort_unless($accountId, 403, 'Account mapping failed.');

        $goal = HealthGoal::where('account_id', $accountId)
            ->findOrFail($goalId);

        return response()->json([
            'goal' => $goal,
            'progress' => $this->progressService->calculate($goal),
        ]);
    }

    public function update(UpdateHealthGoalRequest $request, string $goalId): JsonResponse
    {
        $accountId = $this->resolveAccountId($request);

        abort_unless($accountId, 403, 'Account mapping failed.');

        $goal = HealthGoal::where('account_id', $accountId)
            ->findOrFail($goalId);

        $goal->update($request->validated());

        return response()->json([
            'goal' => $goal->fresh(),
            'progress' => $this->progressService->calculate($goal->fresh()),
        ]);
    }
}
