<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Models\CipData;
use App\Models\AmsData;
use App\Models\BsData;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Http\Controllers\ReconciliationController;

class ReconciliationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $date;
    protected $userId;

    public function __construct($date, $userId)
    {
        $this->date = $date;
        $this->userId = $userId;
    }

    public function handle()
    {
        Log::info("ReconciliationJob started for date: {$this->date}");

        try {
            $controller = new ReconciliationController(); 

            $result = $controller->performReconciliation($this->date);  
            $controller->saveReconciliationHistory(
                $this->date,
                $result['anomalies'],
                $result['summary'],
                $this->userId  
            );

            \Log::info("Reconciliation history saved successfully for date: {$this->date}");
        } catch (\Exception $e) {
            \Log::error("ReconciliationJob failed for date: {$this->date} with error: " . $e->getMessage());
            throw $e;
        }
    }
}
