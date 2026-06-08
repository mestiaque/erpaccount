<?php

namespace ME\Erpaccount\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JournalDetail extends Model
{
    use HasFactory;

    protected $table = 'acc_journal_details';

    protected $primaryKey = 'detail_id';

    protected $fillable = [
        'journal_id',
        'account_id',
        'cost_center_id',
        'party_type',
        'party_id',
        'debit_amount',
        'credit_amount',
        'is_reconciled',
        'reconciled_at',
        'matched_statement_id',
        'reconciliation_note',
    ];

    protected $casts = [
        'debit_amount' => 'decimal:2',
        'credit_amount' => 'decimal:2',
        'is_reconciled' => 'boolean',
        'reconciled_at' => 'datetime',
    ];

    public function journalMaster(): BelongsTo
    {
        return $this->belongsTo(JournalMaster::class, 'journal_id', 'journal_id');
    }

    public function chartOfAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id', 'account_id');
    }

    public function costCenter(): BelongsTo
    {
        return $this->belongsTo(CostCenter::class, 'cost_center_id', 'cost_center_id');
    }
}
