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
        Schema::table('ingredients', function (Blueprint $table) {
            $table->unsignedBigInteger('base_unit_id')->nullable();
            $table->decimal('cost_per_base', 20, 6)->nullable();

            $table->foreign('base_unit_id')->references('id')->on('units');
        });
    }



    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $table->dropColumn('base_unit_id');
        $table->dropColumn('cost_per_base');
    }
};
