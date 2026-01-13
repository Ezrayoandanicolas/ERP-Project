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
        Schema::table('ingredients', function (Blueprint $table) {
            if (Schema::hasColumn('ingredients', 'base_unit_id')) {
                try {
                    $table->dropForeign(['base_unit_id']);
                } catch (\Exception $e) {
                    // FK sudah tidak ada — aman
                }

                $table->dropColumn('base_unit_id');
            }
            // 3️⃣ Hapus cost_per_base juga kalau ada
            if (Schema::hasColumn('ingredients', 'cost_per_base')) {
                $table->dropColumn('cost_per_base');
            }

            // 4️⃣ Tambah unit_id (sistem baru)
            if (!Schema::hasColumn('ingredients', 'unit_id')) {
                $table->foreignId('unit_id')->after('name')->constrained('units');
            }

            // 5️⃣ Tambah price_per_unit (sistem baru)
            if (!Schema::hasColumn('ingredients', 'price_per_unit')) {
                $table->float('price_per_unit')->nullable();
            }
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ingredients', function (Blueprint $table) {
            //
        });
    }
};
