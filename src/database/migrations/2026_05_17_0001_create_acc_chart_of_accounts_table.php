<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('acc_chart_of_accounts', function (Blueprint $table) {
            $table->id('account_id');
            $table->string('account_code', 50)->unique();
            $table->string('account_name', 150);
            // Self-referencing parent ID for sub-accounts
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->enum('account_type', ['Asset', 'Liability', 'Equity', 'Revenue', 'Expense']);
            $table->boolean('is_reconcilable')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Foreign key for hierarchical/parent-child accounts
            $table->foreign('parent_id')->references('account_id')->on('acc_chart_of_accounts')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acc_chart_of_accounts');
    }
};
