<?php
// database/migrations/YYYY_MM_DD_create_pos_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->date('tanggal')->default(DB::raw('CURRENT_DATE'));
            $table->decimal('total_amount', 15, 2);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->decimal('change_amount', 15, 2)->default(0);
            $table->foreignId('user_id')->nullable()->constrained(); // ID Kasir
            // Tambahkan kolom lain seperti customer_id jika perlu
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos');
    }
};