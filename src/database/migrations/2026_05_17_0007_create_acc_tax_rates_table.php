<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('acc_tax_rates', function (Blueprint $table) {
            $table->id('tax_rate_id');
            $table->string('tax_name', 50); // e.g., VAT 5%, Source Tax 2%
            $table->decimal('percentage', 5, 2); // e.g., 5.00, 2.50
            $table->unsignedBigInteger('ledger_account_id'); // এই ট্যাক্সের টাকা কোন লেজারে জমা হবে
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('ledger_account_id')->references('account_id')->on('acc_chart_of_accounts')->onDelete('restrict');
        });
    }

    public function down()
    {
        Schema::dropIfExists('acc_tax_rates');
    }
};
