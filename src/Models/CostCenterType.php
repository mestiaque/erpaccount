<?php

namespace ME\Erpaccount\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CostCenterType extends Model
{
    use HasFactory;

    protected $table = 'acc_cost_center_types';

    protected $primaryKey = 'cost_center_type_id';

    protected $fillable = [
        'type_name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
