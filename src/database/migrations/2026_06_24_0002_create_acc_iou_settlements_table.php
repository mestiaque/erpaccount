<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('acc_iou_settlements', function (Blueprint $table) {
            $table->id('settlement_id');
            $table->unsignedBigInteger('iou_id');

            $table->date('settlement_date');
            $table->decimal('settled_amount', 15, 2);

            // cash / bank / salary_adjust / other
            $table->enum('settlement_type', ['cash', 'bank', 'salary_adjust', 'other'])->default('cash');

            // COA account on the debit side of settlement entry
            $table->unsignedBigInteger('offset_account_id');

            $table->string('note', 255)->nullable();

            // approval_status: for future workflow
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('approved');

            $table->unsignedBigInteger('journal_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('iou_id')->references('iou_id')->on('acc_ious')->onDelete('cascade');
            $table->foreign('offset_account_id')->references('account_id')->on('acc_chart_of_accounts')->onDelete('restrict');
            $table->foreign('journal_id')->references('journal_id')->on('acc_journal_masters')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acc_iou_settlements');
    }
};
