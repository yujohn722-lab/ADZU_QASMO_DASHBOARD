<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportLog extends Model
{
    protected $fillable = [
        'user_id',
        'module',
        'filters',
        'export_type',
        'generated_at',
    ];

    protected $casts = [
        'filters' => 'array',
        'generated_at' => 'datetime',
    ];
}
