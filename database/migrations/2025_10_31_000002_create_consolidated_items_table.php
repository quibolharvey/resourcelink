<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('consolidated_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consolidated_form_id')->constrained('consolidated_forms')->onDelete('cascade');
            $table->string('unit')->nullable();
            $table->string('description')->nullable();
            $table->decimal('price', 12, 2)->default(0);
            $table->unsignedInteger('quantity')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consolidated_items');
    }
};





