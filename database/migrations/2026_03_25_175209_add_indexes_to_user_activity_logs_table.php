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
        Schema::table('user_activity_logs', function (Blueprint $table) {
            // Cria os índices compostos para eliminar o gargalo de ordenação (filesort) na memória
            
            // Otimiza a busca geral do histórico ordenado por data
            $table->index(['device_id', 'event_at'], 'idx_device_event');
            
            // Otimiza a busca do histórico web (filtrando browsers específicos)
            $table->index(['device_id', 'process_name', 'event_at'], 'idx_device_process_event');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_activity_logs', function (Blueprint $table) {
            // Remove os índices caso precises de fazer rollback da migration
            $table->dropIndex('idx_device_event');
            $table->dropIndex('idx_device_process_event');
        });
    }
};