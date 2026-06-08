<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('acc_financial_periods', function (Blueprint $table) {
            $table->id('period_id');
            $table->string('period_name', 50); // e.g., FY 2025-2026
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_closed')->default(false); // true হলে এই ডেট রেঞ্জের ব্যাক-ডেট এন্ট্রি ব্লক হয়ে যাবে
            $table->timestamps();
        });

    }

    public function down()
    {
        Schema::dropIfExists('acc_financial_periods');
    }
};
