<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE acc_journal_masters MODIFY COLUMN source_module ENUM('Manual','Inventory','Production','Commercial','Payroll','IOU') DEFAULT 'Manual'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE acc_journal_masters MODIFY COLUMN source_module ENUM('Manual','Inventory','Production','Commercial','Payroll') DEFAULT 'Manual'");
    }
};
