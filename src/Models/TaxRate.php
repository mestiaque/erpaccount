<?php

namespace ME\Erpaccount\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxRate extends Model
{
    use HasFactory;

    protected $table = 'acc_tax_rates';

    protected $primaryKey = 'tax_rate_id';

    protected $fillable = [
        'tax_name',
        'percentage',
        'ledger_account_id',
        'is_active',
    ];

    protected $casts = [
        'percentage' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function chartOfAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'ledger_account_id', 'account_id');
    }
}
