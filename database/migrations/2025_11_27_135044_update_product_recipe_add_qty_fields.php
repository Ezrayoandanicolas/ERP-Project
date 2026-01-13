<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('product_recipe', function (Blueprint $table) {
            if (!Schema::hasColumn('product_recipe', 'qty')) {
                $table->decimal('qty', 15, 3)->after('ingredient_id');
            }

            if (!Schema::hasColumn('product_recipe', 'unit_id')) {
                $table->unsignedBigInteger('unit_id')->after('qty');
            }

            if (!Schema::hasColumn('product_recipe', 'yield_qty')) {
                $table->decimal('yield_qty', 15, 3)->nullable()->after('unit_id');
            }

            if (!Schema::hasColumn('product_recipe', 'yield_unit_id')) {
                $table->unsignedBigInteger('yield_unit_id')->nullable()->after('yield_qty');
            }
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
