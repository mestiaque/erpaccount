<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('acc_lc_financials', function (Blueprint $table) {
            $table->id('lc_finance_id');
            $table->enum('lc_type', ['Master_LC', 'Back_To_Back_LC']);
            $table->unsignedBigInteger('lc_id_reference')->index(); // Reference ID from Commercial Module
            $table->decimal('total_lc_value', 15, 2);
            $table->string('currency', 10)->default('USD');
            $table->decimal('exchange_rate', 10, 4)->default(1.0000);
            $table->decimal('bank_margin_percentage', 5, 2)->default(0.00);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acc_lc_financials');
    }
};
