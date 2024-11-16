<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Budget;
use App\Http\Requests\Budget\StoreBudgetRequest;
use App\Http\Requests\Budget\UpdateBudgetRequest;
use App\Http\Resources\BudgetResource;

class BudgetController extends Controller
{
    public function index()
    {
        $budgets = Budget::where('user_id', auth()->id())
            ->where('is_active', true)
            ->latest()
            ->get();

        return BudgetResource::collection($budgets);
    }

    public function store(StoreBudgetRequest $request)
    {
        $budget = Budget::create([
            'user_id' => auth()->id(),
            'name' => $request->name,
            'amount' => $request->amount,
            'period_type' => $request->period_type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'is_active' => true,
        ]);

        return new BudgetResource($budget);
    }

    public function show(Budget $budget)
    {
        $this->authorize('view', $budget);
        return new BudgetResource($budget);
    }

    public function update(UpdateBudgetRequest $request, Budget $budget)
    {
        $this->authorize('update', $budget);
        
        $budget->update($request->validated());
        return new BudgetResource($budget);
    }

    public function destroy(Budget $budget)
    {
        $this->authorize('delete', $budget);
        
        $budget->update(['is_active' => false]);
        return response()->json(['message' => 'Budget deactivated successfully']);
    }
} 