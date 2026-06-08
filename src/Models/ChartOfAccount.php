<?php

namespace ME\Erpaccount\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChartOfAccount extends Model
{
    use HasFactory;

    protected $table = 'acc_chart_of_accounts';

    protected $primaryKey = 'account_id';

    protected $fillable = [
        'account_code',
        'account_name',
        'parent_id',
        'account_type',
        'is_reconcilable',
        'is_active',
    ];

    protected $casts = [
        'is_reconcilable' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id', 'account_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id', 'account_id')->orderBy('account_code');
    }

    public function childrenRecursive(): HasMany
    {
        return $this->children()->with('childrenRecursive');
    }

    public function bankAccounts(): HasMany
    {
        return $this->hasMany(BankAccount::class, 'account_id', 'account_id');
    }

    public function taxRates(): HasMany
    {
        return $this->hasMany(TaxRate::class, 'ledger_account_id', 'account_id');
    }
}
