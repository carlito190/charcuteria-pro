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
        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained();
            $table->decimal('quantity', 10, 3); // 3 decimales: sirve para 1.000 (un refresco) o 0.350 (350gr de queso)
            $table->string('unit_type')->default('UND'); // 'KG' para charcutería, 'UND' para refrescos/secos, etc.
            $table->decimal('price', 12, 2); // Precio unitario o por Kg al momento de la venta
            $table->decimal('subtotal', 12, 2); // cantidad * precio
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_items');
    }
};
