<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            CREATE TABLE user_activity_logs (
                id BIGINT UNSIGNED NOT NULL,
                device_id BIGINT UNSIGNED NOT NULL,
                username VARCHAR(255) NOT NULL,
                event_type ENUM('login', 'logout', 'lock', 'unlock', 'idle_start', 'idle_end') NOT NULL,
                active_window_title VARCHAR(255) NULL,
                process_name VARCHAR(255) NULL,
                event_at DATETIME NOT NULL,
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL,
                PRIMARY KEY (id, event_at) -- Obrigatório para particionamento por data
            ) ENGINE=InnoDB
            PARTITION BY RANGE (YEAR(event_at)) (
                PARTITION p2025 VALUES LESS THAN (2026),
                PARTITION p2026 VALUES LESS THAN (2027),
                PARTITION p2027 VALUES LESS THAN (2028),
                PARTITION p_future VALUES LESS THAN MAXVALUE
            );
        ");

      
        DB::statement("ALTER TABLE user_activity_logs MODIFY id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT;");
    }

    public function down(): void
    {
        Schema::dropIfExists('user_activity_logs');
    }
};