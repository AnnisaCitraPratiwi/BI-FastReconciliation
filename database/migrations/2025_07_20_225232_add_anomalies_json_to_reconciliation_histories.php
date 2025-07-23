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
        Schema::table('reconciliation_histories', function (Blueprint $table) {
            $table->json('anomalies_json')->nullable()->after('summary_data');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reconciliation_histories', function (Blueprint $table) {
            $table->dropColumn('anomalies_json');
        });
    }
};
