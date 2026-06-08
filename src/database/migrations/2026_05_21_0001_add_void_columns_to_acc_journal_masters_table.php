<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('acc_journal_masters', function (Blueprint $table) {
            $table->boolean('is_voided')->default(false)->after('created_by');
            $table->timestamp('voided_at')->nullable()->after('is_voided');
            $table->unsignedBigInteger('voided_by')->nullable()->after('voided_at');
            $table->string('void_reason', 255)->nullable()->after('voided_by');
        });
    }

    public function down(): void
    {
        Schema::table('acc_journal_masters', function (Blueprint $table) {
            $table->dropColumn(['is_voided', 'voided_at', 'voided_by', 'void_reason']);
        });
    }
};
