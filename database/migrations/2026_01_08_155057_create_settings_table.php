<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('text'); // text, json, boolean
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Insert default printer settings
        DB::table('settings')->insert([
            [
                'key' => 'printer_type',
                'value' => 'thermal',
                'type' => 'select',
                'description' => 'Default printer type for receipts',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'receipt_width',
                'value' => '80mm',
                'type' => 'select',
                'description' => 'Receipt paper width',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'company_name',
                'value' => 'DAKOSS GLOBAL NIGERIA LTD',
                'type' => 'text',
                'description' => 'Company name on receipts',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
