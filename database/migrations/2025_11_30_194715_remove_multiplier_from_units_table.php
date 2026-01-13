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
            $table->dropColumn('multiplier');
        });
    }

    public function down(): void
    {
        Schema::table('units', function (Blueprint $table) {
            // Optional restore (kalau butuh rollback)
            $table->decimal('multiplier', 20, 8)->default(1);
        });
    }

};
