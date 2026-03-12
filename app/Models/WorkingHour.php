<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkingHour extends Model
{
    protected $fillable = [
        'name',
        'start_time',
        'end_time',
        'ad_groups'
    ];

    // Converte automaticamente o JSON do MariaDB para Array no Laravel
    protected $casts = [
        'ad_groups' => 'array',
    ];
}