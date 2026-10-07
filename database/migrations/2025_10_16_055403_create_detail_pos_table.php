<?php

// database/migrations/YYYY_MM_DD_create_detail_pos_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detail_pos', function (Blueprint $table) {
            $table->bigIncrements('id');
            // Foreign Key ke tabel pos yang baru dibuat
            $table->foreignId('pos_id')->constrained('pos')->onDelete('cascade'); 
            
            $table->unsignedBigInteger('product_id');
            $table->integer('jumlah');
            $table->decimal('harga', 15, 2); // Harga satuan (Sesuai gambar)
            $table->decimal('subtotal', 15, 2); // Perhitungan (Harga * Jumlah)
            
            // Kolom tanggal DIBUANG dari sini karena sudah ada di pos
            
            $table->timestamps();
            
            // Definisikan foreign key ke produk
            $table->foreign('product_id')->references('id')->on('products');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_pos');
    }
};