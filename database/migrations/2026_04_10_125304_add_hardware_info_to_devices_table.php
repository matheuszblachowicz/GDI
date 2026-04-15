<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * O que acontece quando você roda: php artisan migrate
     */
    public function up()
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->string('cpu')->nullable()->after('os_version');
            $table->string('ram')->nullable()->after('cpu');
            $table->string('storage')->nullable()->after('ram');
        });
    }

    /**
     * O que acontece quando você desfaz a migration: php artisan migrate:rollback
     */
    public function down()
    {
        Schema::table('devices', function (Blueprint $table) {
            // Se revertermos a migration, o Laravel apaga estas colunas para deixar a tabela como era antes
            $table->dropColumn(['cpu', 'ram', 'storage']);
        });
    }
};