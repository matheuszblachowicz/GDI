<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserActivityLog extends Model
{
    // Removemos a indicação da chave composta. O Laravel assumirá 
    // automaticamente que a PK é 'id' (o padrão do sistema).
    // O particionamento por ano continuará a operar de forma invisível no MySQL.
    
    protected $fillable = [
        'device_id', 'username', 'event_type', 
        'active_window_title', 'process_name', 'event_at'
    ];

    public function device()
    {
        return $this->belongsTo(Device::class);
    }

}