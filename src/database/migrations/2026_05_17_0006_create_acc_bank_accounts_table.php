<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('acc_bank_accounts', function (Blueprint $table) {
            $table->id('bank_account_id');
            $table->unsignedBigInteger('account_id'); // COA এর সাথে লিঙ্ক
            $table->string('bank_name', 100);
            $table->string('branch_name', 100);
            $table->string('account_number', 50)->unique();
            $table->string('account_type', 50); // Current, Foreign Currency, CD, etc.
            $table->string('swift_code', 20)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('account_id')->references('account_id')->on('acc_chart_of_accounts')->onDelete('restrict');
        });

    }

    public function down()
    {
        Schema::dropIfExists('acc_bank_accounts');
    }
};
