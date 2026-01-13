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
        Schema::table('units', function (Blueprint $table) {

            // Tambahkan kolom type
            // type = weight | count | volume | other
            $table->string('type')->default('count')->after('slug');

            // Jadikan base_slug nullable dulu (biar tidak error)
            $table->string('base_slug')->nullable()->change();

            // Jadikan multiplier nullable & tidak dipakai lagi
            $table->decimal('multiplier', 20, 8)->nullable()->change();
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
