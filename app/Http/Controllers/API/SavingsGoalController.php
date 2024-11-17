<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\SavingsGoal;
use App\Http\Requests\SavingsGoal\StoreSavingsGoalRequest;
use App\Http\Requests\SavingsGoal\UpdateSavingsGoalRequest;
use App\Http\Requests\SavingsGoal\ContributeRequest;
use App\Http\Resources\SavingsGoalResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class SavingsGoalController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $goals = SavingsGoal::where('user_id', auth()->id())
            ->latest()
            ->get();

        return SavingsGoalResource::collection($goals);
    }

    public function store(StoreSavingsGoalRequest $request): JsonResponse
    {
        $goal = SavingsGoal::create([
            'user_id' => auth()->id(),
            'name' => $request->name,
            'target_amount' => $request->target_amount,
            'current_amount' => $request->initial_amount ?? 0,
            'target_date' => $request->target_date,
            'is_completed' => false,
        ]);

        return (new SavingsGoalResource($goal))
            ->response()
            ->setStatusCode(201);
    }

    public function show(SavingsGoal $savingsGoal): SavingsGoalResource
    {
        Gate::authorize('view', $savingsGoal);
        return new SavingsGoalResource($savingsGoal);
    }

    public function update(UpdateSavingsGoalRequest $request, SavingsGoal $savingsGoal): SavingsGoalResource
    {
        Gate::authorize('update', $savingsGoal);

        $savingsGoal->update($request->validated());
        return new SavingsGoalResource($savingsGoal);
    }

    public function contribute(ContributeRequest $request, SavingsGoal $savingsGoal): JsonResponse|SavingsGoalResource
    {
        Gate::authorize('update', $savingsGoal);

        $savingsGoal->updateProgress($request->amount);
        return new SavingsGoalResource($savingsGoal);
    }

    public function destroy(SavingsGoal $savingsGoal): JsonResponse
    {
        Gate::authorize('delete', $savingsGoal);

        $savingsGoal->delete();
        return response()->json(['message' => 'Savings goal deleted successfully']);
    }
}
