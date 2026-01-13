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
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('cost_price', 20, 6)->default(0);
            $table->decimal('manual_cost', 20, 6)->nullable();
            $table->decimal('sell_price', 20, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $table->dropColumn('cost_price');
        $table->dropColumn('manual_cost');
        $table->dropColumn('sell_price');
    }
};
