<?php 

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCipDataTable extends Migration
{
    public function up()
    {
        Schema::create('cip_data', function (Blueprint $table) {
            $table->id();
            $table->date('transaction_date');
            $table->string('end_to_end_id');
            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('kredit', 15, 2)->default(0);
            $table->timestamps();
            
            $table->index(['transaction_date']);
            $table->index(['end_to_end_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('cip_data');
    }
}
