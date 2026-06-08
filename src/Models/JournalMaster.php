<?php

namespace ME\Erpaccount\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;

class JournalMaster extends Model
{
    use HasFactory;

    protected static ?bool $supportsVoidColumnsCache = null;

    protected $table = 'acc_journal_masters';

    protected $primaryKey = 'journal_id';

    protected $fillable = [
        'voucher_no',
        'journal_date',
        'source_module',
        'source_reference_id',
        'narration',
        'created_by',
        'is_voided',
        'voided_at',
        'voided_by',
        'void_reason',
    ];

    protected $casts = [
        'journal_date' => 'date',
        'is_voided' => 'boolean',
        'voided_at' => 'datetime',
    ];

    public function scopeActive($query)
    {
        if (!self::supportsVoidColumns()) {
            return $query;
        }

        return $query->where('is_voided', false);
    }

    public static function supportsVoidColumns(): bool
    {
        if (self::$supportsVoidColumnsCache !== null) {
            return self::$supportsVoidColumnsCache;
        }

        self::$supportsVoidColumnsCache = Schema::hasTable('acc_journal_masters')
            && Schema::hasColumn('acc_journal_masters', 'is_voided');

        return self::$supportsVoidColumnsCache;
    }

    public function details(): HasMany
    {
        return $this->hasMany(JournalDetail::class, 'journal_id', 'journal_id');
    }
}
