<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('acc_ious', function (Blueprint $table) {
            $table->id('iou_id');
            $table->string('iou_no', 20)->unique();

            // party_type: 'employee' pulls from ME\Hr\Models\HrEmployee via party_id
            // party_type: 'custom'   means a free-text person, party_id is null
            $table->enum('party_type', ['employee', 'custom']);
            $table->unsignedBigInteger('party_id')->nullable();
            $table->string('party_name', 150);

            $table->decimal('original_amount', 15, 2);
            $table->date('issue_date');
            $table->string('purpose', 255)->nullable();

            // COA account that acts as IOU Receivable (asset side)
            $table->unsignedBigInteger('iou_account_id');
            // COA account used to disburse funds (cash/bank)
            $table->unsignedBigInteger('cash_account_id');

            // auto-updated from settlement sum
            $table->decimal('settled_amount', 15, 2)->default(0.00);
            $table->enum('status', ['open', 'partial', 'closed'])->default('open');

            // approval_status: kept for future workflow — currently always 'approved'
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('approved');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();

            $table->unsignedBigInteger('journal_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('iou_account_id')->references('account_id')->on('acc_chart_of_accounts')->onDelete('restrict');
            $table->foreign('cash_account_id')->references('account_id')->on('acc_chart_of_accounts')->onDelete('restrict');
            $table->foreign('journal_id')->references('journal_id')->on('acc_journal_masters')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acc_ious');
    }
};
