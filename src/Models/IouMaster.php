<?php

namespace ME\Erpaccount\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IouMaster extends Model
{
    protected $table = 'acc_ious';

    protected $primaryKey = 'iou_id';

    protected $fillable = [
        'iou_no',
        'party_type',
        'party_id',
        'party_name',
        'original_amount',
        'issue_date',
        'purpose',
        'iou_account_id',
        'cash_account_id',
        'settled_amount',
        'status',
        'approval_status',
        'approved_by',
        'approved_at',
        'journal_id',
        'created_by',
    ];

    protected $casts = [
        'original_amount' => 'float',
        'settled_amount'  => 'float',
        'issue_date'      => 'date',
        'approved_at'     => 'datetime',
    ];

    public function settlements(): HasMany
    {
        return $this->hasMany(IouSettlement::class, 'iou_id', 'iou_id');
    }

    public function iouAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'iou_account_id', 'account_id');
    }

    public function cashAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'cash_account_id', 'account_id');
    }

    public function journal(): BelongsTo
    {
        return $this->belongsTo(JournalMaster::class, 'journal_id', 'journal_id');
    }

    public function getOutstandingAmountAttribute(): float
    {
        return round($this->original_amount - $this->settled_amount, 2);
    }

    public function recalculateStatus(): void
    {
        $totalSettled = $this->settlements()
            ->where('approval_status', 'approved')
            ->sum('settled_amount');

        $this->settled_amount = round((float) $totalSettled, 2);

        if ($this->settled_amount <= 0) {
            $this->status = 'open';
        } elseif ($this->settled_amount >= $this->original_amount) {
            $this->status = 'closed';
        } else {
            $this->status = 'partial';
        }

        $this->saveQuietly();
    }
}
