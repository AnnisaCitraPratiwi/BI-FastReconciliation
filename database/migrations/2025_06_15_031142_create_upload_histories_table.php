<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('upload_histories', function (Blueprint $table) {
            $table->id();
            $table->string('filename');
            $table->string('file_type'); // cip, ams, bs
            $table->unsignedBigInteger('uploaded_by');
            $table->timestamps();
            
            $table->foreign('uploaded_by')->references('id')->on('users');
            $table->index(['file_type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('upload_histories');
    }
};
