<?php

namespace ME\Erpaccount\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankStatementEntry extends Model
{
    use HasFactory;

    protected $table = 'acc_bank_statement_entries';

    protected $primaryKey = 'statement_id';

    protected $fillable = [
        'bank_account_id',
        'statement_date',
        'reference_no',
        'description',
        'debit_amount',
        'credit_amount',
        'closing_balance',
        'is_reconciled',
        'reconciled_at',
        'matched_detail_id',
    ];

    protected $casts = [
        'statement_date' => 'date',
        'debit_amount' => 'decimal:2',
        'credit_amount' => 'decimal:2',
        'closing_balance' => 'decimal:2',
        'is_reconciled' => 'boolean',
        'reconciled_at' => 'datetime',
    ];

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class, 'bank_account_id', 'bank_account_id');
    }
}
