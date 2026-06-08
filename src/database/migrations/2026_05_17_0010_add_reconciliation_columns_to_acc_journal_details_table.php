<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('acc_journal_details', function (Blueprint $table) {
            $table->boolean('is_reconciled')->default(false)->after('credit_amount');
            $table->timestamp('reconciled_at')->nullable()->after('is_reconciled');
            $table->unsignedBigInteger('matched_statement_id')->nullable()->index()->after('reconciled_at');
            $table->string('reconciliation_note', 255)->nullable()->after('matched_statement_id');
        });
    }

    public function down(): void
    {
        Schema::table('acc_journal_details', function (Blueprint $table) {
            $table->dropIndex(['matched_statement_id']);
            $table->dropColumn(['is_reconciled', 'reconciled_at', 'matched_statement_id', 'reconciliation_note']);
        });
    }
};
