<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ams_data', function (Blueprint $table) {
            $table->string('trx_source')->nullable()->after('credit_status');
            $table->string('trx_status')->nullable()->after('trx_source');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ams_data', function (Blueprint $table) {
            $table->dropColumn(['trx_source', 'trx_status']);
        });
    }
};
