<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('acc_inventory_posting_logs', function (Blueprint $table) {
            $table->id('inventory_log_id');
            $table->enum('source_module', ['Inventory', 'Production', 'Commercial'])->default('Inventory');
            $table->enum('transaction_type', ['Fabric_Issue', 'Yarn_Purchase_GRN', 'Goods_Receipt', 'Material_Issue', 'Trim_Purchase'])->index();
            $table->string('reference_no', 100)->nullable()->index();
            $table->string('description', 255)->nullable();
            $table->decimal('system_valuation', 15, 2)->default(0.00);
            $table->decimal('override_valuation', 15, 2)->nullable();
            $table->enum('posting_status', ['Pending Review', 'Overridden', 'Posted'])->default('Pending Review');
            $table->unsignedBigInteger('journal_id')->nullable()->index();
            $table->json('payload')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acc_inventory_posting_logs');
    }
};
