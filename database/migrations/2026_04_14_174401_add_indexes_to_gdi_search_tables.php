<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Índices na tabela de Dispositivos
        Schema::table('devices', function (Blueprint $table) {
            $table->index('hostname');
            $table->index('ip_address');
        });

        // 2. Índices na tabela de Logs (CRÍTICO para a velocidade da Subquery)
        Schema::table('user_activity_logs', function (Blueprint $table) {
            $table->index('username');
            $table->index('device_id'); // Acelera o cruzamento (JOIN/EXISTS) entre as tabelas
        });
    }

    public function down()
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->dropIndex(['hostname']);
            $table->dropIndex(['ip_address']);
        });

        Schema::table('user_activity_logs', function (Blueprint $table) {
            $table->dropIndex(['username']);
            $table->dropIndex(['device_id']);
        });
    }
};