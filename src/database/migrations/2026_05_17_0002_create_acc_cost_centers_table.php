<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('acc_cost_centers', function (Blueprint $table) {
            $table->id('cost_center_id');
            $table->enum('cost_center_type', ['Order', 'Department', 'Machine_Line']);
            // ID from external production/order module
            $table->unsignedBigInteger('reference_id')->index();
            $table->string('cost_center_name', 150);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acc_cost_centers');
    }
};
