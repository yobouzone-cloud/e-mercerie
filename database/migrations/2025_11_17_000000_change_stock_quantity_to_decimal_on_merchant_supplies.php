<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // Syntaxe PostgreSQL
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE merchant_supplies ALTER COLUMN stock_quantity TYPE DECIMAL(10,3)');
            DB::statement('ALTER TABLE merchant_supplies ALTER COLUMN stock_quantity SET DEFAULT 0');
        } else {
            // Syntaxe MySQL (pour votre environnement local)
            DB::statement('ALTER TABLE `merchant_supplies` MODIFY `stock_quantity` DECIMAL(10,3) NOT NULL DEFAULT 0');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE merchant_supplies ALTER COLUMN stock_quantity TYPE INTEGER');
            DB::statement('ALTER TABLE merchant_supplies ALTER COLUMN stock_quantity SET DEFAULT 0');
        } else {
            DB::statement('ALTER TABLE `merchant_supplies` MODIFY `stock_quantity` INT NOT NULL DEFAULT 0');
        }
    }
};