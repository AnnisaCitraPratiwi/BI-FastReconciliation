<?php

namespace App\Imports;

use App\Models\CipData;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CipDataImport implements ToCollection
{
    private $filename;
    private $dataToInsert = [];
    private $batchSize = 100;
    private $processedReferences = [];

    public function __construct($filename)
    {
        $this->filename = $filename;
    }

    public function collection(Collection $rows)
    {
        Log::info("=== CIP IMPORT DEBUG START ===");
        Log::info("Filename: {$this->filename}");
        
        $rowsArray = $rows->toArray();
        Log::info("Total rows in Excel: " . count($rowsArray));
        
        $startRow = 13; 
        $processedCount = 0;
        
        for ($rowIndex = $startRow; $rowIndex < count($rowsArray); $rowIndex++) {
            if (!isset($rowsArray[$rowIndex])) {
                continue;
            }
            
            $row = $rowsArray[$rowIndex];
            
            // Transaction ID di kolom D (index 3)
            $transactionId = isset($row[3]) ? trim($row[3]) : '';
            // Business Message ID di kolom E (index 4)
            $businessMsgId = isset($row[4]) ? trim($row[4]) : '';
            // Debit Amount di kolom J (index 9), Credit Amount di kolom K (index 10)
            $debitAmount = isset($row[9]) ? $row[9] : 0;
            $creditAmount = isset($row[10]) ? $row[10] : 0;
            $transactionDate = $this->extractDateFromTransactionId($transactionId);
            if (!$transactionDate) { 
                continue;
            }

            if (empty($transactionDate) || empty($businessMsgId)) {
                continue; // skip row tidak jelas
            }
            $dupeKey = $transactionDate . '_' . strtoupper($businessMsgId);
            if (in_array($dupeKey, $this->processedReferences)) {
                continue;
            }
            if (CipData::where('transaction_date', $transactionDate)
                ->where('end_to_end_id', $businessMsgId)
                ->exists()) {
                continue;
            }
            $this->processedReferences[] = $dupeKey; 

            
            $recordData = [
                'transaction_date' => $transactionDate,
                'end_to_end_id' => $businessMsgId,
                'debit' => $this->cleanNumericValue($debitAmount),
                'kredit' => $this->cleanNumericValue($creditAmount),
                'created_at' => now(),
                'updated_at' => now(),
            ];
            
            Log::info("Row {$rowIndex} - Transaction ID: {$businessMsgId}, Date: {$transactionDate}, Debit: {$debitAmount}, Credit: {$creditAmount}");
            
            $this->dataToInsert[] = $recordData;
            $processedCount++;

            if (count($this->dataToInsert) >= $this->batchSize) {
                $this->insertBatch();
            }
        }
        
        // Insert sisa data
        if (!empty($this->dataToInsert)) {
            $this->insertBatch();
        }
        
        Log::info("=== CIP IMPORT DEBUG END ===");
        Log::info("Total processed records: {$processedCount}");
    }

    private function extractDateFromTransactionId($transactionId)
    {
        if (empty($transactionId) || strlen($transactionId) < 6) {
            return null;
        }

        try {
            // Ambil 6 digit pertama dari Transaction ID
            // Contoh: 2501030035540021 -> 250103 (YYMMDD format)
            $dateString = substr($transactionId, 0, 6);
            
            if (!preg_match('/^\d{6}$/', $dateString)) {
                return null;
            }
            
            // Parse sebagai YYMMDD (2 digit tahun)
            $date = Carbon::createFromFormat('ymd', $dateString);
            
            // Validasi tahun - asumsi 25 = 2025, bukan 1925
            if ($date && $date->year >= 2020 && $date->year <= (date('Y') + 10)) {
                return $date->format('Y-m-d');
            }
            
            return null;
            
        } catch (\Exception $e) {
            Log::error("Error parsing date from {$transactionId}: " . $e->getMessage());
            return null;
        }
    }

    private function cleanNumericValue($value)
    {
        if (empty($value) || $value === null) {
            return 0;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        if (is_string($value)) {
            $value = trim($value);
            
            // Handle format Indonesia: 210.000,00 -> 210000.00
            if (strpos($value, '.') !== false && strpos($value, ',') !== false) {
                $parts = explode(',', $value);
                if (count($parts) === 2) {
                    $integerPart = str_replace('.', '', $parts[0]);
                    $decimalPart = $parts[1];
                    $cleaned = $integerPart . '.' . $decimalPart;
                }
            } else {
                $cleaned = preg_replace('/[^\d.,-]/', '', $value);
                $cleaned = str_replace(',', '.', $cleaned);
            }
            
            if (is_numeric($cleaned)) {
                return (float) $cleaned;
            }
        }

        return 0;
    }

    private function insertBatch()
    {
        if (!empty($this->dataToInsert)) {
            try {
                $count = count($this->dataToInsert);
                Log::info("Inserting batch of {$count} records");
                
                CipData::insert($this->dataToInsert);
                
                Log::info("Successfully inserted {$count} records");
                $this->dataToInsert = [];
            } catch (\Exception $e) {
                Log::error("Batch insert failed: " . $e->getMessage());
                throw $e;
            }
        }
    }
}
