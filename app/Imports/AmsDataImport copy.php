<?php

namespace App\Imports;

use App\Models\AmsData;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AmsDataImport implements ToModel, WithHeadingRow, WithValidation, WithBatchInserts, WithChunkReading
{
    private $filename;
    private $transactionDate;
    private $processedReferences = []; // Untuk tracking duplikasi

    public function __construct($filename)
    {
        $this->filename = $filename;
        $this->transactionDate = $this->extractTransactionDateFromFilename($filename);
        
        Log::info("AMS Import initialized with filename: {$filename}");
        Log::info("Extracted transaction date: {$this->transactionDate}");
    }

    public function model(array $row)
    {
        // TIDAK SKIP row kosong - tetap proses dengan nilai default
        $referenceNumber = $row['referencenumber'] ?? $row['reference_number'] ?? '';
        
        // Jika reference number kosong, buat ID unik untuk mencegah duplikasi
        if (empty($referenceNumber)) {
            $referenceNumber = '';
        }

        // HANDLING DUPLIKASI: Cek apakah reference number sudah diproses
        $duplicateKey = $this->transactionDate . '_' . $referenceNumber;
        
        if (in_array($duplicateKey, $this->processedReferences)) {
            Log::warning("Duplicate reference detected, skipping: {$referenceNumber}");
            return null; // Skip data duplikat
        }

        // Cek duplikasi di database
        $existingRecord = AmsData::where('transaction_date', $this->transactionDate)
                                ->where('reference_number', $referenceNumber)
                                ->first();

        if ($existingRecord) {
            Log::warning("Reference number already exists in database, skipping: {$referenceNumber}");
            return null; // Skip jika sudah ada di database
        }

        // Tambahkan ke tracking duplikasi
        $this->processedReferences[] = $duplicateKey;

        Log::info("Processing AMS record: {$referenceNumber}");

        return new AmsData([
            'transaction_date' => $this->transactionDate,
            'reference_number' => $referenceNumber,
            'bifast_reference_number' => $row['bifastreferencenumber'] ?? $row['bifast_reference_number'] ?? null,
            'trx_amount' => $this->cleanNumericValue($row['trxamount'] ?? $row['trx_amount'] ?? 0),
            'source_account_number' => $row['sourceaccountnumber'] ?? $row['source_account_number'] ?? null,
            'destination_account_number' => $row['destinationaccountnumber'] ?? $row['destination_account_number'] ?? null,
            'trx_date_time' => $this->parseDateTime($row['trxdatetime'] ?? $row['trx_date_time'] ?? $row['trx_datetime'] ?? null),
            'debit_status' => $row['debitstatus'] ?? $row['debit_status'] ?? null,
            'credit_status' => $row['creditstatus'] ?? $row['credit_status'] ?? null,
            'created_date' => $this->parseDateTime($row['trxdatetime'] ?? $row['trx_date_time'] ?? $row['trx_datetime'] ?? null),
            'created_date_time' => $this->parseDateTime($row['trxdatetime'] ?? $row['trx_date_time'] ?? $row['trx_datetime'] ?? null),
        ]);
    }

    /**
     * Extract transaction date dari nama file (format: 20250103.xlsx)
     */
    private function extractTransactionDateFromFilename($filename)
    {
        Log::info("Extracting transaction date from filename: {$filename}");
        
        // Pattern untuk mencari 8 digit berturut-turut (YYYYMMDD)
        if (preg_match('/(\d{8})/', $filename, $matches)) {
            $dateString = $matches[1];
            
            try {
                // Coba parse sebagai YYYYMMDD
                $date = Carbon::createFromFormat('Ymd', $dateString);
                
                // Validasi tahun (harus masuk akal)
                if ($date->year >= 2020 && $date->year <= 2030) {
                    $result = $date->format('Y-m-d');
                    Log::info("Successfully extracted date from filename: {$result}");
                    return $result;
                }
                
                // Jika tahun tidak masuk akal, coba DDMMYYYY
                $date = Carbon::createFromFormat('dmY', $dateString);
                if ($date->year >= 2020 && $date->year <= 2030) {
                    $result = $date->format('Y-m-d');
                    Log::info("Successfully extracted date from filename (DDMMYYYY): {$result}");
                    return $result;
                }
                
            } catch (\Exception $e) {
                Log::warning("Failed to parse date from filename: " . $e->getMessage());
            }
        }
         
    }

    /**
     * Bersihkan nilai numerik
     */
    private function cleanNumericValue($value)
    {
        if (is_null($value) || $value === '') {
            return 0;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        if (is_string($value)) {
            $value = trim($value);
            // Hapus semua karakter kecuali digit, titik, koma, dan minus
            $value = preg_replace('/[^\d\.,\-]/', '', $value);

            // Handle format Indonesia: 1.234.567,89 -> 1234567.89
            if (strpos($value, '.') !== false && strpos($value, ',') !== false) {
                $value = str_replace('.', '', $value);
                $value = str_replace(',', '.', $value);
            } 
            // Handle multiple dots: 1.234.567 -> 1234567
            elseif (strpos($value, '.') !== false && substr_count($value, '.') > 1) {
                $value = str_replace('.', '', $value);
            } 
            // Handle comma as decimal: 1234,56 -> 1234.56
            elseif (strpos($value, ',') !== false && strlen(substr($value, strpos($value, ',') + 1)) == 2) {
                $value = str_replace(',', '.', $value);
            }

            if (is_numeric($value)) {
                return (float) $value;
            }
        }

        return 0;
    }

    /**
     * Parse datetime lengkap
     */
    private function parseDateTime($dateTimeValue)
    {
        if (empty($dateTimeValue)) {
            return null;
        }

        try {
            // Handle timezone ICT
            if (is_string($dateTimeValue) && strpos($dateTimeValue, 'ICT') !== false) {
                $cleanDateTime = str_replace('ICT', '', $dateTimeValue);
                $cleanDateTime = trim($cleanDateTime);
                return Carbon::parse($cleanDateTime);
            }

            return Carbon::parse($dateTimeValue);
            
        } catch (\Exception $e) {
            Log::warning("Failed to parse datetime: {$dateTimeValue}. Error: " . $e->getMessage());
            return null;
        }
    }

    public function batchSize(): int
    {
        return 1000;
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function rules(): array
    {
        return [
            // TIDAK ada validasi required - izinkan row kosong
            'referencenumber' => 'nullable|string',
            'sourceaccountnumber' => 'nullable|string', 
            'destinationaccountnumber' => 'nullable|string',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'referencenumber.string' => 'Reference Number must be a string',
            'sourceaccountnumber.string' => 'Source Account Number must be a string',
            'destinationaccountnumber.string' => 'Destination Account Number must be a string',
        ];
    }
}
