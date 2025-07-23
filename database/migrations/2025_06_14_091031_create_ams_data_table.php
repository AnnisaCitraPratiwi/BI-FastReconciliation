<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAmsDataTable extends Migration
{
    public function up()
    {
        Schema::create('ams_data', function (Blueprint $table) {
            $table->id();
            $table->date('transaction_date');
            $table->string('reference_number');
            $table->string('bifast_reference_number')->nullable();
            $table->decimal('trx_amount', 15, 2);
            $table->string('source_account_number');
            $table->string('destination_account_number');
            $table->datetime('trx_date_time')->nullable();
            $table->string('debit_status')->nullable();
            $table->string('credit_status')->nullable();
            $table->timestamps();
            
            $table->index(['transaction_date']);
            $table->index(['reference_number']);
            $table->index(['bifast_reference_number']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('ams_data');
    }
}
