<?php

namespace ME\Erpaccount\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinancialPeriod extends Model
{
    use HasFactory;

    protected $table = 'acc_financial_periods';

    protected $primaryKey = 'period_id';

    protected $fillable = [
        'period_name',
        'start_date',
        'end_date',
        'is_closed',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_closed' => 'boolean',
    ];
}
