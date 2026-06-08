<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('acc_payroll_integration_batches', function (Blueprint $table) {
            $table->id('payroll_batch_id');
            $table->string('payroll_month', 20)->index();
            $table->string('payroll_year', 4)->index();
            $table->decimal('staff_basic', 15, 2)->default(0.00);
            $table->decimal('staff_allowances', 15, 2)->default(0.00);
            $table->decimal('staff_pf_deductions', 15, 2)->default(0.00);
            $table->decimal('staff_net_payable', 15, 2)->default(0.00);
            $table->decimal('factory_piece_rate_earnings', 15, 2)->default(0.00);
            $table->decimal('factory_overtime_amount', 15, 2)->default(0.00);
            $table->decimal('factory_net_payable', 15, 2)->default(0.00);
            $table->enum('posting_status', ['Pending Review', 'Posted'])->default('Pending Review');
            $table->unsignedBigInteger('journal_id')->nullable()->index();
            $table->json('payload')->nullable();
            $table->timestamp('posted_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acc_payroll_integration_batches');
    }
};
