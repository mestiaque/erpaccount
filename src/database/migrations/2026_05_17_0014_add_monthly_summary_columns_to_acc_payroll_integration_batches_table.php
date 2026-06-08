<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('acc_payroll_integration_batches', function (Blueprint $table) {
            $table->string('summary_label', 100)->nullable()->after('payroll_batch_id');
            $table->decimal('total_basic', 15, 2)->default(0.00)->after('summary_label');
            $table->decimal('total_allowances', 15, 2)->default(0.00)->after('total_basic');
            $table->decimal('total_overtime', 15, 2)->default(0.00)->after('total_allowances');
            $table->decimal('total_pf_deduction', 15, 2)->default(0.00)->after('total_overtime');
            $table->decimal('total_advance_adjusted', 15, 2)->default(0.00)->after('total_pf_deduction');
            $table->decimal('net_payable', 15, 2)->default(0.00)->after('total_advance_adjusted');
            $table->unique('payroll_month');
        });
    }

    public function down(): void
    {
        Schema::table('acc_payroll_integration_batches', function (Blueprint $table) {
            $table->dropUnique(['payroll_month']);
            $table->dropColumn([
                'summary_label',
                'total_basic',
                'total_allowances',
                'total_overtime',
                'total_pf_deduction',
                'total_advance_adjusted',
                'net_payable',
            ]);
        });
    }
};
