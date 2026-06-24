<?php

namespace ME\Erpaccount\Models;

use Illuminate\Database\Eloquent\Model;

class Debitor extends Model
{
    protected $table = 'acc_debitors';

    protected $primaryKey = 'debitor_id';

    protected $fillable = [
        'name',
        'type',
        'address',
        'phone',
        'email',
        'category',
        'country',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
