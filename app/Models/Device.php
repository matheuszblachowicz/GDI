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

    // Relacionamento COMPLETO com o histórico (traz todos os milhares de logs)
    public function activityLogs()
    {
        return $this->hasMany(UserActivityLog::class, 'device_id');
    }

    // NOVO: Relacionamento ultra-rápido que traz APENAS o último log de cada máquina
    public function latestActivityLog()
    {
        return $this->hasOne(UserActivityLog::class, 'device_id')->latestOfMany('event_at');
    }

    // Acessor ATUALIZADO para usar a nova relação sem fazer queries adicionais
    public function getCurrentUserAttribute()
    {
        // Lê diretamente da relação que foi carregada com o 'with()' no Controller
        return $this->latestActivityLog ? $this->latestActivityLog->username : 'Sem registo';
    }
}