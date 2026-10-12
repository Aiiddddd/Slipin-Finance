<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $table = 'expenses';
    public $timestamps = false;
    protected $guarded = [];
    protected $casts = ['expense_date' => 'date:Y-m-d'];

    public function category()
    {
        return $this->belongsTo(ExpenseCategory::class, 'category_id');
    }
}
