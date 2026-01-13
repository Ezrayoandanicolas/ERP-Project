<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'yield_qty')) {
                $table->float('yield_qty')->nullable()->after('sell_price');
            }

            if (!Schema::hasColumn('products', 'yield_unit_id')) {
                $table->foreignId('yield_unit_id')
                    ->nullable()
                    ->after('yield_qty')
                    ->constrained('units')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'yield_unit_id')) {
                $table->dropForeign(['yield_unit_id']);
                $table->dropColumn('yield_unit_id');
            }

            if (Schema::hasColumn('products', 'yield_qty')) {
                $table->dropColumn('yield_qty');
            }
        });
    }
};
