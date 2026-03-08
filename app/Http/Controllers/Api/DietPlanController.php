<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DietPlan\StoreDietPlanRequest;
use App\Services\DietPlanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DietPlanController extends Controller
{
    public function __construct(
        private DietPlanService $dietPlanService
    ) {}

    /** GET /api/diet-plans */
    public function index(Request $request): JsonResponse
    {
        $result = $this->dietPlanService->getWeeklyPlan(
            $request->user(),
            (int) $request->query('week', 1)
        );

        return response()->json($result);
    }

    /** POST /api/diet-plans */
    public function store(StoreDietPlanRequest $request): JsonResponse
    {
        $data = $this->dietPlanService->store($request->user(), $request->validated());

        return response()->json([
            'message' => 'Recipe added to diet plan.',
            'data'    => $data,
        ], 201);
    }

    /** DELETE /api/diet-plans/{id} */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $this->dietPlanService->destroy($request->user(), $id);

        return response()->json(['message' => 'Meal removed from diet plan.']);
    }

    /** DELETE /api/diet-plans/clear */
    public function clear(Request $request): JsonResponse
    {
        $deleted = $this->dietPlanService->clear(
            $request->user(),
            (int) $request->query('week', 1)
        );

        return response()->json([
            'message' => "Cleared {$deleted} meal(s) from week {$request->query('week', 1)}.",
            'deleted' => $deleted,
        ]);
    }
}
