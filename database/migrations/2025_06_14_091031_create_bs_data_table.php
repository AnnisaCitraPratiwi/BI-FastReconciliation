<?php
// database/migrations/xxxx_create_bs_data_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBsDataTable extends Migration
{
    public function up()
    {
        Schema::create('bs_data', function (Blueprint $table) {
            $table->id();
            $table->date('transaction_date'); // untuk keperluan sistem
            $table->string('retrieval_ref_number');
            $table->date('tgl_transaksi');
            $table->decimal('nilai_transaksi', 15, 2);
            $table->timestamps();
            
            $table->index(['tgl_transaksi']);
            $table->index(['retrieval_ref_number']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('bs_data');
    }
}
