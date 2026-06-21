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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nombre o Razón Social
            $table->string('id_number')->unique(); // Cédula o RIF (Único para control)
            $table->string('phone')->nullable(); // Teléfono de contacto
            $table->string('email')->nullable(); // Correo electrónico (opcional)
            $table->text('address')->nullable(); // Dirección (útil para cobrar o despachar)

            // Control de Crédito básico
            $table->boolean('allow_credit')->default(false); // ¿Este cliente tiene autorización para crédito?
            $table->decimal('credit_limit', 12, 2)->default(0.00); // Límite máximo en Bolívares o $ base
            $table->decimal('current_balance', 12, 2)->default(0.00); // Lo que debe actualmente

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
