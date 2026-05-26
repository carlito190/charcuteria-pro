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
        Schema::create('sales_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->onDelete('cascade'); 
            
            // Método usado en la interfaz: 'Efectivo', 'Divisas', 'Pago Móvil', 'Punto'
            $table->string('payment_method'); 
            
            // Moneda en la que se registra el pago ('VES', 'USD')
            $table->string('currency')->default('VES');

            // El monto que se abonó en esta línea (siempre guardado en la moneda base, ej: Bs)
            $table->decimal('amount', 12, 2); 

            // La tasa cambiaria utilizada para este pago específico.
            // 12 dígitos en total y 4 decimales para máxima precisión (ej: 45.1250)
            $table->decimal('exchange_rate', 12, 4)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_payments');
    }
};
