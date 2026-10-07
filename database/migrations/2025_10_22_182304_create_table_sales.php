<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('nama_sales');
            $table->string('perusahaan')->nullable();
            $table->date('tanggal_kunjungan');
            $table->string('tujuan')->nullable();
            $table->text('hasil_kunjungan')->nullable();
            $table->string('kontak_sales')->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
