<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('barcode')->unique()->nullable();
            $table->foreignId('category_id')->constrained('categories')->onDelete('restrict');
            $table->enum('item_type', ['standard', 'kit', 'temporary'])->default('standard');
            $table->enum('stock_type', ['stock', 'non-stock'])->default('stock');
            $table->decimal('cost_price', 12, 2);
            $table->decimal('retail_price', 12, 2);
            $table->decimal('wholesale_price', 12, 2)->nullable();
            $table->integer('reorder_level')->default(10);
            $table->integer('reorder_quantity')->default(50);
            $table->string('image_path')->nullable();
            $table->string('size')->nullable();
            $table->string('colour')->nullable();
            $table->string('brand')->nullable();
            $table->date('expiry_date')->nullable();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->onDelete('set null');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
