<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('display_name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Insert default roles for Dakoss Global
        DB::table('roles')->insert([
            ['name' => 'admin', 'display_name' => 'Administrator', 'description' => 'Full system access - manages users, products, reports, and all operations', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'subadmin', 'display_name' => 'Sub-Administrator', 'description' => 'Full system access like admin - manages products, categories, inventory, and operations', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'sales_staff', 'display_name' => 'Sales Staff', 'description' => 'POS operations only - sells items, processes payments, and prints receipts', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
