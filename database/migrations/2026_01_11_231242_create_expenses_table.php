<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('outlet_id');
            $table->date('tanggal');

            $table->string('kategori');
            $table->text('keterangan'); // wajib ada penjelasan

            $table->decimal('jumlah', 15, 2);

            $table->string('metode_pembayaran')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Foreign key
            $table->foreign('outlet_id')
                ->references('id')
                ->on('outlets')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
