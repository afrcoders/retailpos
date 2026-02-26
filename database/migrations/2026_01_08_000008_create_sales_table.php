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
            $table->string('sale_number')->unique();
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
            $table->string('customer_name')->nullable();
            $table->string('customer_phone')->nullable();
            $table->string('customer_email')->nullable();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('discount_percentage', 5, 2)->default(0);
            $table->decimal('vat_amount', 12, 2)->default(0);
            $table->decimal('vat_percentage', 5, 2)->default(7.5); // Nigerian VAT
            $table->decimal('total', 12, 2)->default(0);
            $table->decimal('amount_paid', 12, 2)->default(0);
            $table->decimal('change', 12, 2)->default(0);
            $table->enum('payment_method', ['cash', 'card', 'transfer', 'mixed'])->default('cash');
            $table->enum('status', ['completed', 'pending', 'cancelled', 'returned'])->default('completed');
            $table->text('notes')->nullable();
            $table->timestamp('sale_date');
            $table->timestamps();

            $table->index(['sale_date', 'status']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
