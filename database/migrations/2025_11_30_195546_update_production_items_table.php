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
        Schema::table('production_items', function (Blueprint $table) {

            // Tambahkan kolom unit_id (jika belum ada)
            if (!Schema::hasColumn('production_items', 'unit_id')) {
                $table->foreignId('unit_id')->after('ingredient_id')->constrained('units');
            }

            // Rename atau pastikan kolom qty_used adalah float
            $table->float('qty_used')->change();

            // Hapus field-field lama yang tidak relevan
            if (Schema::hasColumn('production_items', 'qty_base')) {
                $table->dropColumn('qty_base');
            }

            if (Schema::hasColumn('production_items', 'unit_base_id')) {
                $table->dropColumn('unit_base_id');
            }

            if (Schema::hasColumn('production_items', 'price_per_base')) {
                $table->dropColumn('price_per_base');
            }

            // Cost tetap ada (optional)
            $table->float('cost')->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('production_items', function (Blueprint $table) {
            // rollback optional — bisa dikosongkan atau tulis ulang struktur lama
        });
    }
};
