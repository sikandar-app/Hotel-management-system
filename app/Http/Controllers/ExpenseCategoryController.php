<?php

namespace App\Http\Controllers;

use App\Http\Resources\ExpenseCategoryResource;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;

class ExpenseCategoryController extends CommonController
{
    public function index(Request $request)
    {     
        $expenseCategories = ExpenseCategory::get();
        return $this->sendResponse(ExpenseCategoryResource::collection($expenseCategories), "Fetched expense categories successfully");
    }

    public function show(ExpenseCategory $expenseCategory)
    {
        return $this->sendResponse(new ExpenseCategoryResource($expenseCategory), "Fetched expense category successfully");
    }

    public function update(Request $request, ExpenseCategory $expenseCategory)
    {
        $expenseCategory->update($request->all());
        return $this->sendResponse($expenseCategory, "Updated expense category successfully");
    }
}
