// database/migrations/xxxx_add_inventory_fields_to_products_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'safety_stock')) {
                $table->integer('safety_stock')->default(10);
            }
            if (!Schema::hasColumn('products', 'lead_time_days')) {
                $table->integer('lead_time_days')->default(3);
            }
            if (!Schema::hasColumn('products', 'avg_daily_usage')) {
                $table->decimal('avg_daily_usage', 8, 2)->default(5);
            }
            if (!Schema::hasColumn('products', 'max_daily_usage')) {
                $table->decimal('max_daily_usage', 8, 2)->nullable();
            }
            if (!Schema::hasColumn('products', 'maximum_stock')) {
                $table->integer('maximum_stock')->nullable();
            }
            if (!Schema::hasColumn('products', 'stock_status')) {
                $table->enum('stock_status', ['normal', 'reorder', 'low', 'critical'])->default('normal');
            }
        });
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'safety_stock',
                'lead_time_days',
                'avg_daily_usage',
                'max_daily_usage',
                'maximum_stock',
                'stock_status'
            ]);
        });
    }
};