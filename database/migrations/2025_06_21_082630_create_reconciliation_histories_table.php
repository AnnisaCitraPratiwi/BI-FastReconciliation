<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('reconciliation_histories', function (Blueprint $table) {
            $table->id();
            $table->date('reconciliation_date');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('total_anomalies')->default(0);
            $table->integer('cip_records')->default(0);
            $table->integer('ams_records')->default(0);
            $table->integer('bs_records')->default(0);
            $table->string('excel_file_path')->nullable();
            $table->string('pdf_file_path')->nullable();
            $table->json('summary_data')->nullable();
            $table->timestamps();
            
            $table->index(['reconciliation_date', 'user_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('reconciliation_histories');
    }
};
