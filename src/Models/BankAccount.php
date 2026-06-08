<?php

namespace ME\Erpaccount\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankAccount extends Model
{
    use HasFactory;

    protected $table = 'acc_bank_accounts';

    protected $primaryKey = 'bank_account_id';

    protected $fillable = [
        'account_id',
        'bank_name',
        'branch_name',
        'account_number',
        'account_type',
        'swift_code',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function chartOfAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id', 'account_id');
    }
}
