<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('purchase_cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_cart_id')->constrained('purchase_carts')->onDelete('cascade');
            $table->foreignId('inventory_item_id')->nullable()->constrained('inventories')->onDelete('set null');
            $table->string('unit');
            $table->string('description');
            $table->integer('quantity');
            $table->decimal('price', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_cart_items');
    }
}; 