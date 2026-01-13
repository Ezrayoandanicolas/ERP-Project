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

            if (Schema::hasColumn('product_recipe', 'base_qty')) {
                $table->dropColumn('base_qty');
            }

            if (Schema::hasColumn('product_recipe', 'base_unit_id')) {
                $table->dropColumn('base_unit_id');
            }

            if (Schema::hasColumn('product_recipe', 'converted_qty')) {
                $table->dropColumn('converted_qty');
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
