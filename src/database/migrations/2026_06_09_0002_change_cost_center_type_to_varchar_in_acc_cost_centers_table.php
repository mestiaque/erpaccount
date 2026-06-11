<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE acc_cost_centers MODIFY cost_center_type VARCHAR(50) NOT NULL");
    }

    public function down(): void
    {
        // Keep only enum-compatible values before reverting.
        DB::statement("UPDATE acc_cost_centers SET cost_center_type = 'Department' WHERE cost_center_type NOT IN ('Order', 'Department', 'Machine_Line')");
        DB::statement("ALTER TABLE acc_cost_centers MODIFY cost_center_type ENUM('Order', 'Department', 'Machine_Line') NOT NULL");
    }
};
