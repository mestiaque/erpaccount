<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('acc_journal_masters', function (Blueprint $table) {
            $table->id('journal_id');
            $table->string('voucher_no', 100)->unique();
            $table->date('journal_date');
            $table->enum('source_module', ['Manual', 'Inventory', 'Production', 'Commercial', 'Payroll'])->default('Manual');
            // ID from the originating system transaction
            $table->unsignedBigInteger('source_reference_id')->nullable()->index();
            $table->text('narration')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acc_journal_masters');
    }
};
