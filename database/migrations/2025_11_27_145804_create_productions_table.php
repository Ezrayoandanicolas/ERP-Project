<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('productions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('store_id')->nullable()->constrained('stores');
            $table->foreignId('outlet_id')->nullable()->constrained('outlets');

            $table->float('batch_qty');      // jumlah batch yang diproduksi
            $table->float('total_output');   // total output (dalam yield product)
            $table->float('total_cost')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('productions');
    }
};
