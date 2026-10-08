<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE game_accounts MODIFY COLUMN status ENUM('available', 'sold', 'installment') NOT NULL DEFAULT 'available'");
        }
    }

    public function down()
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE game_accounts MODIFY COLUMN status ENUM('available', 'sold') NOT NULL DEFAULT 'available'");
        }
    }
};
