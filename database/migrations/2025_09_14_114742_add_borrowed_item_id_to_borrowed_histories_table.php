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
        Schema::table('borrowed_histories', function (Blueprint $table) {
            $table->foreignId('borrowed_item_id')->nullable()->constrained('borrowed_items')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('borrowed_histories', function (Blueprint $table) {
            $table->dropForeign(['borrowed_item_id']);
            $table->dropColumn('borrowed_item_id');
        });
    }
};
