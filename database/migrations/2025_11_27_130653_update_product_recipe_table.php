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
        Schema::table('product_recipe', function (Blueprint $table) {
            $table->unsignedBigInteger('unit_id')->nullable();
            $table->decimal('yield_qty', 20, 4)->nullable();
            $table->unsignedBigInteger('yield_unit_id')->nullable();

            $table->foreign('unit_id')->references('id')->on('units');
            $table->foreign('yield_unit_id')->references('id')->on('units');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $table->dropColumn('unit_id');
        $table->dropColumn('yield_qty');
        $table->dropColumn('yield_unit_id');
    }
};
