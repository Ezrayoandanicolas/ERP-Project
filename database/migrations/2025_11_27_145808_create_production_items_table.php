<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('production_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('production_id')->constrained('productions')->cascadeOnDelete();
            $table->foreignId('ingredient_id')->constrained('ingredients')->cascadeOnDelete();

            $table->float('qty_used');   // jumlah bahan yang terpakai dalam base unit
            $table->float('cost');       // total cost pemakaian bahan itu

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_items');
    }
};
