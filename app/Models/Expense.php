<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;
    protected $fillable = [
        'amount',
        'note',
        'expense_category_id',
        'expense_date',
        'created_by',
        'status',
    ];

    public function expenseCategory()
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
