<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add merchant-level measure
        Schema::table('merchant_supplies', function (Blueprint $table) {
            if (!Schema::hasColumn('merchant_supplies', 'measure')) {
                $table->string('measure')->nullable()->after('stock_quantity');
            }
        });

        // Add sale_mode to supplies so admin can choose 'quantity' or 'measure'
        Schema::table('supplies', function (Blueprint $table) {
            if (!Schema::hasColumn('supplies', 'sale_mode')) {
                $table->string('sale_mode')->default('quantity')->after('measure');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('merchant_supplies', function (Blueprint $table) {
            if (Schema::hasColumn('merchant_supplies', 'measure')) {
                $table->dropColumn('measure');
            }
        });

        Schema::table('supplies', function (Blueprint $table) {
            if (Schema::hasColumn('supplies', 'sale_mode')) {
                $table->dropColumn('sale_mode');
            }
        });
    }
};
