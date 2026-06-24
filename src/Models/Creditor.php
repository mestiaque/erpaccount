<?php

namespace ME\Erpaccount\Models;

use Illuminate\Database\Eloquent\Model;

class Creditor extends Model
{
    protected $table = 'acc_creditors';

    protected $primaryKey = 'creditor_id';

    protected $fillable = [
        'name',
        'type',
        'address',
        'phone',
        'email',
        'category',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
