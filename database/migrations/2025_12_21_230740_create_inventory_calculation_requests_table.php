<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('inventory_calculation_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();

            $table->decimal('avg_daily_usage', 10, 2);
            $table->decimal('max_daily_usage', 10, 2)->nullable();
            $table->integer('lead_time_days');

            $table->decimal('safety_stock', 10, 2);
            $table->decimal('rop', 10, 2);

            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');

            $table->foreignId('requested_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();

            $table->text('notes')->nullable();
            $table->timestamps();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_calculation_requests');
    }
};
