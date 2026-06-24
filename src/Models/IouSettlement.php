<?php

namespace ME\Erpaccount\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IouSettlement extends Model
{
    protected $table = 'acc_iou_settlements';

    protected $primaryKey = 'settlement_id';

    protected $fillable = [
        'iou_id',
        'settlement_date',
        'settled_amount',
        'settlement_type',
        'offset_account_id',
        'note',
        'approval_status',
        'journal_id',
        'created_by',
    ];

    protected $casts = [
        'settled_amount'  => 'float',
        'settlement_date' => 'date',
    ];

    public function iou(): BelongsTo
    {
        return $this->belongsTo(IouMaster::class, 'iou_id', 'iou_id');
    }

    public function offsetAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'offset_account_id', 'account_id');
    }

    public function journal(): BelongsTo
    {
        return $this->belongsTo(JournalMaster::class, 'journal_id', 'journal_id');
    }
}
