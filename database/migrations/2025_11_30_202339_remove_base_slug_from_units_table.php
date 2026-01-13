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
        Schema::table('units', function (Blueprint $table) {
            if (Schema::hasColumn('units', 'base_slug')) {
                $table->dropColumn('base_slug');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('units', function (Blueprint $table) {
            $table->string('base_slug')->nullable();
        });
    }
};
