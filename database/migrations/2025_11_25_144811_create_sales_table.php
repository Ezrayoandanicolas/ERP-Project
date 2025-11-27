<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();

            $table->foreignId('outlet_id')->constrained()->cascadeOnDelete();

            $table->string('customer_name')->nullable();

            $table->enum('payment_method', ['cash', 'transfer', 'qris', 'credit'])
                  ->default('cash');

            $table->decimal('total', 12, 2)->default(0);

            $table->text('note')->nullable(); // catatan tambahan

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
