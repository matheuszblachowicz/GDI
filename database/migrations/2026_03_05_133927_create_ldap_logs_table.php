<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ldap_logs', function (Blueprint $table) {
            $table->id();
            $table->string('usuario_nome');
            $table->string('samaccountname')->nullable();
            $table->string('email')->nullable();
            $table->string('acao'); 
            $table->string('departamento')->nullable();
            $table->text('detalhes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ldap_logs');
    }
};
