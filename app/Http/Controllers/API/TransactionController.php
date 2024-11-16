<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Http\Requests\Transaction\StoreTransactionRequest;
use App\Http\Requests\Transaction\UpdateTransactionRequest;
use App\Http\Resources\TransactionResource;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $transactions = Transaction::query()
            ->where('user_id', auth()->id())
            ->when($request->category_id, fn($q) => $q->forCategory($request->category_id))
            ->when($request->type, fn($q) => $q->ofType($request->type))
            ->when($request->is_recurring, fn($q) => $q->recurring())
            ->when($request->date_from, fn($q, $date) => $q->where('transaction_date', '>=', $date))
            ->when($request->date_to, fn($q, $date) => $q->where('transaction_date', '<=', $date))
            ->with('category')
            ->latest('transaction_date')
            ->paginate($request->per_page ?? 15);

        return TransactionResource::collection($transactions);
    }

    public function store(StoreTransactionRequest $request)
    {
        $transaction = Transaction::create([
            'user_id' => auth()->id(),
            'category_id' => $request->category_id,
            'amount' => $request->amount,
            'type' => $request->type,
            'description' => $request->description,
            'transaction_date' => $request->transaction_date,
            'is_recurring' => $request->is_recurring,
            'recurring_frequency' => $request->recurring_frequency,
        ]);

        return new TransactionResource($transaction);
    }

    public function show(Transaction $transaction)
    {
        $this->authorize('view', $transaction);
        return new TransactionResource($transaction);
    }

    public function update(UpdateTransactionRequest $request, Transaction $transaction)
    {
        $this->authorize('update', $transaction);
        
        $transaction->update($request->validated());
        return new TransactionResource($transaction);
    }

    public function destroy(Transaction $transaction)
    {
        $this->authorize('delete', $transaction);
        
        $transaction->delete();
        return response()->json(['message' => 'Transaction deleted successfully']);
    }

    public function summary(Request $request)
    {
        $month = $request->month ?? now()->month;
        $year = $request->year ?? now()->year;

        return response()->json([
            'income' => Transaction::getMonthlyTotal('income', $month, $year),
            'expense' => Transaction::getMonthlyTotal('expense', $month, $year),
            'categories' => $this->getCategorySummary($month, $year),
        ]);
    }

    private function getCategorySummary($month, $year)
    {
        return Transaction::query()
            ->where('user_id', auth()->id())
            ->whereMonth('transaction_date', $month)
            ->whereYear('transaction_date', $year)
            ->with('category')
            ->get()
            ->groupBy('category.name')
            ->map(fn($transactions) => $transactions->sum('amount'));
    }
} 