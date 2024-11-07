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
        //o nome do solicitante, o destino, a data de ida, a data de volta e o status (solicitado, aprovado, cancelado).
        Schema::create('viagens', function (Blueprint $table) {
            $table->id();
            $table->string('Nome_do_Solicitante');
            $table->string('Destino');
            $table->date('Data_de_Ida');
            $table->date('Data_de_Volta');
            $table->enum('status', ['solicitado', 'aprovado', 'cancelado'])->default('solicitado');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('viagens');
    }
};
