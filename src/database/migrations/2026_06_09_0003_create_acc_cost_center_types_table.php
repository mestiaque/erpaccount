<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('acc_cost_center_types', function (Blueprint $table) {
            $table->id('cost_center_type_id');
            $table->string('type_name', 50)->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $now = now();

        DB::table('acc_cost_center_types')->insert([
            ['type_name' => 'Order', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['type_name' => 'Department', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['type_name' => 'Machine_Line', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        if (Schema::hasTable('acc_cost_centers')) {
            $existingTypes = DB::table('acc_cost_centers')
                ->select('cost_center_type')
                ->whereNotNull('cost_center_type')
                ->distinct()
                ->pluck('cost_center_type')
                ->filter()
                ->values();

            foreach ($existingTypes as $typeName) {
                DB::table('acc_cost_center_types')->updateOrInsert(
                    ['type_name' => (string) $typeName],
                    ['is_active' => true, 'updated_at' => $now, 'created_at' => $now]
                );
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('acc_cost_center_types');
    }
};
