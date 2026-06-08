<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('acc_journal_details', function (Blueprint $table) {
            $table->id('detail_id');
            $table->unsignedBigInteger('journal_id');
            $table->unsignedBigInteger('account_id');
            $table->unsignedBigInteger('cost_center_id')->nullable(); // Can be null if overhead or general expense

            $table->enum('party_type', ['Buyer', 'Supplier', 'Employee', 'None'])->default('None');
            $table->unsignedBigInteger('party_id')->nullable()->index(); // ID from external party/vendor module

            // 15 digits total, 2 decimal places for handling millions/billions securely
            $table->decimal('debit_amount', 15, 2)->default(0.00);
            $table->decimal('credit_amount', 15, 2)->default(0.00);
            $table->timestamps();

            // Foreign Key Relations
            $table->foreign('journal_id')->references('journal_id')->on('acc_journal_masters')->onDelete('cascade');
            $table->foreign('account_id')->references('account_id')->on('acc_chart_of_accounts')->onDelete('restrict');
            $table->foreign('cost_center_id')->references('cost_center_id')->on('acc_cost_centers')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acc_journal_details');
    }
};
