<?php

namespace App\Http\Controllers;

use App\Http\Resources\ExpenseResource;
use App\Models\Expense;
use Illuminate\Http\Request;

class ExpenseController extends CommonController
{
    public function index(Request $request)
    {     
        $perPage = $request->input('per_page', 10);  // Default to 10 users per page
        $search = $request->input('search', ''); // Get the search query from request
        $startDate = $request->input('start_date');  // Get start date from the request
        $endDate = $request->input('end_date');  // Get end date from the request
        
        $query = Expense::when($search, function ($query, $search) {
                return $query->where(function ($query) use ($search) {
                    $query->where('amount', 'like', '%' . $search . '%')
                        ->orWhere('note', 'like', '%' . $search . '%');
                });
            })
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                $query->whereBetween('expense_date', [$startDate, $endDate]);
            });

        if ($request->has('sort_by') && isset($request->sort_by) && $request->has('sort_direction')) {
            $query->orderBy($request->sort_by, $request->sort_direction);
        } else {
            $query->orderBy('created_at', 'desc');
        }
        
        $expenses  = $query->paginate($perPage);
        $data = [
            'expenses' => ExpenseResource::collection($expenses),
            'total' => $expenses->total(),
            'per_page' => $expenses->perPage(),
        ];

        return $this->sendResponse($data, "Fetched expenses successfully");
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'note' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
            'expense_date' => 'required|date',
            'expense_category_id' => 'required|exists:expense_categories,id',
        ]);

        $expense = Expense::create(
            [
                ...$validated,
                'created_by' => auth()->user()->id,
            ]
        );

        return $this->sendResponse(new ExpenseResource($expense), "Created expense successfully");
    }

    public function show(Expense $expense)
    {
        return $this->sendResponse(new ExpenseResource($expense), "Fetched expense successfully");
    }

    public function update(Request $request, Expense $expense)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'note' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
            'expense_date' => 'required|date',
            'expense_category_id' => 'required|exists:expense_categories,id',
        ]);

        $expense->update([
            ...$validated,
            'created_by' => auth()->user()->id,
        ]);
        return $this->sendResponse($expense, "Updated expense successfully");
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();
        return $this->sendResponse([], "Deleted expense successfully");
    }
}
