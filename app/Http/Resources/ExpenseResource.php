<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExpenseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'amount' => $this->amount,
            'note' => $this->note,
            'status' => $this->status,
            'created_by' => $this->user->name ?? 'N/A',
            'expense_category_id' => $this->expense_category_id,
            'category' => $this->expenseCategory,
            'expense_date' => Carbon::parse($this->expense_date)->toDateString(),
            'created_at' => Carbon::parse($this->created_at)->toDateString(),
            'updated_at' => Carbon::parse($this->updated_at)->toDateString(),
        ];
    }
}