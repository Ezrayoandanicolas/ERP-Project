<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('ingredient_stocks', function (Blueprint $table) {
            $table->float('price_total')->nullable()->change();
            $table->float('price_per_base')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('ingredient_stocks', function (Blueprint $table) {
            $table->float('price_total')->nullable(false)->change();
            $table->float('price_per_base')->nullable(false)->change();
        });
    }
};
