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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('barcode')->nullable()->unique(); // Para usar lector de barras

            // Relación con Categoría
            $table->foreignId('category_id')->constrained()->onDelete('cascade');

            // Precios (Usamos decimal 10,2 para precisión)
            $table->decimal('cost_price', 10, 2)->default(0); // Lo que te cuesta a ti
            $table->decimal('selling_price', 10, 2);          // Precio al público

            // Stock Global (Opcional, si prefieres manejarlo aquí)
            // Nota: En un sistema multi-sucursal pro, el stock suele ir en una
            // tabla aparte llamada 'product_branch', pero para empezar
            // podemos definir una alerta de stock mínimo aquí.
            $table->decimal('min_stock', 10, 3)->default(1.000);
            $table->decimal('cost_usd', 10, 2);
            $table->decimal('profit_margin', 5, 2)->default(30); // Ejemplo: 30%
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
