<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade');
            $table->integer('quantity_on_hand')->default(0);
            $table->integer('quantity_reserved')->default(0);
            $table->integer('quantity_available')->virtualAs('quantity_on_hand - quantity_reserved');
            $table->timestamp('last_counted_at')->nullable();
            $table->timestamps();

            $table->unique('item_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock');
    }
};
