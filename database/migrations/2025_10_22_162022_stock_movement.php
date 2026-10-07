<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['in', 'out']); // masuk atau keluar
            $table->enum('transaction_type', [
                'purchase',
                'sale',
                'return_from_customer',
                'adjustment_plus',
                'adjustment_minus',
                'damage',
            ]);
            $table->integer('quantity'); // jumlah
            $table->integer('stock_before'); // stok sebelum
            $table->integer('stock_after'); // stok setelah
            $table->string('reference_no')->nullable(); // nomor referensi (invoice, BM-ID, dll)
            $table->decimal('price', 15, 2)->nullable(); // harga beli/jual
            $table->text('notes')->nullable(); // catatan
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // user yang input
            $table->timestamp('transaction_date'); // tanggal transaksi
            $table->timestamps();

            // Index untuk performa
            $table->index('product_id');
            $table->index('type');
            $table->index('transaction_type');
            $table->index('transaction_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
