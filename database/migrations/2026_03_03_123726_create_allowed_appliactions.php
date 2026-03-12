<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('allowed_applications', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nome do processo ou app (ex: 'chrome.exe' ou 'Slack')
            $table->boolean('is_mandatory')->default(false); // Se o app PRECISA estar lá
            $table->timestamps();
            
            // Garante que não repetiremos o mesmo app para o mesmo device na whitelist
            
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('allowed_applications');
    }
};