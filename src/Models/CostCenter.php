<?php

namespace ME\Erpaccount\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CostCenter extends Model
{
    use HasFactory;

    protected $table = 'acc_cost_centers';

    protected $primaryKey = 'cost_center_id';

    protected $fillable = [
        'cost_center_type',
        'reference_id',
        'cost_center_name',
    ];

    public function journalDetails(): HasMany
    {
        return $this->hasMany(JournalDetail::class, 'cost_center_id', 'cost_center_id');
    }
}
