<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesSession extends Model
{
    protected $table = 'sales_sessions';
    public $timestamps = false;
    protected $guarded = [];
    protected $casts = ['sales_date' => 'date:Y-m-d'];
}
