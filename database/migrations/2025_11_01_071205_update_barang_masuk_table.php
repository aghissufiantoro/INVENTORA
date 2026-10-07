<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('barang_masuk', function (Blueprint $table) {
            if (!Schema::hasColumn('barang_masuk', 'status')) {
                $table->enum('status', ['pending', 'verified'])->default('pending');
            }

            if (!Schema::hasColumn('barang_masuk', 'harga_beli')) {
                $table->decimal('harga_beli', 15, 2)->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('barang_masuk', function (Blueprint $table) {
            if (Schema::hasColumn('barang_masuk', 'harga_beli')) {
                $table->dropColumn('harga_beli');
            }
        });
    }
};