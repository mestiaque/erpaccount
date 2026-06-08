<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('acc_bank_statement_entries', function (Blueprint $table) {
            $table->id('statement_id');
            $table->unsignedBigInteger('bank_account_id');
            $table->date('statement_date');
            $table->string('reference_no', 100)->nullable();
            $table->string('description', 255)->nullable();
            $table->decimal('debit_amount', 15, 2)->default(0.00);
            $table->decimal('credit_amount', 15, 2)->default(0.00);
            $table->decimal('closing_balance', 15, 2)->nullable();
            $table->boolean('is_reconciled')->default(false);
            $table->timestamp('reconciled_at')->nullable();
            $table->unsignedBigInteger('matched_detail_id')->nullable()->index();
            $table->timestamps();

            $table->foreign('bank_account_id')
                ->references('bank_account_id')
                ->on('acc_bank_accounts')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acc_bank_statement_entries');
    }
};
