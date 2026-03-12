<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    protected $guarded = [];

    // Relacionamento com as aplicações instaladas
    public function applications()
    {
        return $this->hasMany(DeviceApplication::class, 'device_id');
    }

    // Relacionamento com o histórico de atividade
    public function activityLogs()
    {
        return $this->hasMany(UserActivityLog::class, 'device_id');
    }

    // Acessor para obter o último utilizador ativo nesta máquina
    public function getCurrentUserAttribute()
    {
        $lastLog = $this->activityLogs()->orderBy('event_at', 'desc')->first();
        return $lastLog ? $lastLog->username : 'Sem registo';
    }
}