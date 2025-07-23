<?php
// database/migrations/xxxx_create_reconciliation_results_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReconciliationResultsTable extends Migration
{
    public function up()
    {
        Schema::create('reconciliation_results', function (Blueprint $table) {
            $table->id();
            $table->date('reconciliation_date');
            $table->string('comparison_type'); // cip_vs_ams, ams_vs_bs, bs_vs_cip
            $table->integer('volume_debit_diff')->default(0);
            $table->integer('volume_credit_diff')->default(0);
            $table->decimal('amount_debit_diff', 15, 2)->default(0);
            $table->decimal('amount_credit_diff', 15, 2)->default(0);
            $table->json('discrepancies')->nullable();
            $table->unsignedBigInteger('processed_by');
            $table->timestamps();
            
            $table->foreign('processed_by')->references('id')->on('users');
            $table->index(['reconciliation_date', 'comparison_type']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('reconciliation_results');
    }
}
