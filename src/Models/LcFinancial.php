<?php

namespace ME\Erpaccount\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LcFinancial extends Model
{
    use HasFactory;

    protected $table = 'acc_lc_financials';

    protected $primaryKey = 'lc_finance_id';

    protected $fillable = [
        'lc_type',
        'lc_id_reference',
        'total_lc_value',
        'currency',
        'exchange_rate',
        'bank_margin_percentage',
        'bank_margin_limit',
        'bank_margin_used',
        'bank_commission_paid',
        'acceptance_cost_paid',
        'outstanding_liability',
        'customs_clearing_cost',
        'freight_cost',
        'posting_status',
    ];

    protected $casts = [
        'total_lc_value' => 'decimal:2',
        'exchange_rate' => 'decimal:4',
        'bank_margin_percentage' => 'decimal:2',
        'bank_margin_limit' => 'decimal:2',
        'bank_margin_used' => 'decimal:2',
        'bank_commission_paid' => 'decimal:2',
        'acceptance_cost_paid' => 'decimal:2',
        'outstanding_liability' => 'decimal:2',
        'customs_clearing_cost' => 'decimal:2',
        'freight_cost' => 'decimal:2',
    ];
}
