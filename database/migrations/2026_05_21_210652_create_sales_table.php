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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique(); // Ej: V-000001
            $table->foreignId('branch_id')->constrained()->onDelete('cascade'); // Sede actual
            $table->foreignId('user_id')->constrained(); // Cajero
            $table->string('client_name')->nullable()->default('Cliente Frecuente');
            $table->string('client_id_number')->nullable(); // Cédula o RIF
            $table->decimal('total', 12, 2)->default(0.00); // Monto total de la venta
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
