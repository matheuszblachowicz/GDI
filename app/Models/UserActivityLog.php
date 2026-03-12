<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserActivityLog extends Model
{
    // Como a PK é composta, desativamos o incremento simples para evitar erros no Eloquent
    protected $primaryKey = ['id', 'event_at'];
    public $incrementing = false; 
    
    protected $fillable = [
        'device_id', 'username', 'event_type', 
        'active_window_title', 'process_name', 'event_at'
    ];
}