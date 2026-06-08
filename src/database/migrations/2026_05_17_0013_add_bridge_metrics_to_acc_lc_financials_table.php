<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('acc_lc_financials', function (Blueprint $table) {
            $table->decimal('bank_margin_limit', 15, 2)->default(0.00)->after('bank_margin_percentage');
            $table->decimal('bank_margin_used', 15, 2)->default(0.00)->after('bank_margin_limit');
            $table->decimal('bank_commission_paid', 15, 2)->default(0.00)->after('bank_margin_used');
            $table->decimal('acceptance_cost_paid', 15, 2)->default(0.00)->after('bank_commission_paid');
            $table->decimal('outstanding_liability', 15, 2)->default(0.00)->after('acceptance_cost_paid');
            $table->decimal('customs_clearing_cost', 15, 2)->default(0.00)->after('outstanding_liability');
            $table->decimal('freight_cost', 15, 2)->default(0.00)->after('customs_clearing_cost');
            $table->enum('posting_status', ['Active', 'Closed'])->default('Active')->after('freight_cost');
        });
    }

    public function down(): void
    {
        Schema::table('acc_lc_financials', function (Blueprint $table) {
            $table->dropColumn([
                'bank_margin_limit',
                'bank_margin_used',
                'bank_commission_paid',
                'acceptance_cost_paid',
                'outstanding_liability',
                'customs_clearing_cost',
                'freight_cost',
                'posting_status',
            ]);
        });
    }
};
