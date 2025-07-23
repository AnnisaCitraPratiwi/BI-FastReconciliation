<?php
// app/Imports/BsDataImport.php

namespace App\Imports;

use App\Models\BsData;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class BsDataImport implements ToModel, WithHeadingRow, WithValidation, WithBatchInserts, WithChunkReading
{
    private $filename;
    private static $isDataCleared = [];

    public function __construct($filename)
    {
        $this->filename = $filename;
        Log::info("BS Import initialized with filename: {$filename}");
    }

    public function model(array $row)
    {
        // Debug: Log kolom yang tersedia
        Log::info('BS Import - Available columns:', array_keys($row));
        Log::info('BS Import - Row data:', $row);
        
        // Skip baris kosong atau yang tidak memiliki data penting
        if (empty(array_filter($row))) {
            Log::info("Skipping empty row");
            return null;
        }
        
        // Extract transaction date dari kolom tgl_transaksi
        $transactionDate = $this->extractTransactionDateFromRow($row);
        
        if (!$transactionDate) {
            Log::warning("Cannot extract transaction date from row, skipping");
            return null;
        }
        
        // Clear existing data untuk tanggal ini (hanya sekali per tanggal)
        if (!isset(self::$isDataCleared[$transactionDate])) {
            $this->clearExistingData($transactionDate);
            self::$isDataCleared[$transactionDate] = true;
        }
        
        // Ambil retrieval_ref_number dan extract dari format Ref[...]
        $rawRetrievalRef = $row['retrieval_ref_number'] ?? $row['retrieval_reference_number'] ?? null;
        $retrievalRefNumber = $this->extractRetrievalReference($rawRetrievalRef);
        
        // Validasi nilai transaksi
        $nilaiTransaksi = $this->cleanNumericValue($row['nilai_transaksi'] ?? $row['transaction_value'] ?? 0);
        
        Log::info("Processing BS record: {$retrievalRefNumber} for date: {$transactionDate}, amount: {$nilaiTransaksi}");

        try {
            return new BsData([
                'transaction_date' => $transactionDate,
                'retrieval_ref_number' => $retrievalRefNumber,
                'tgl_transaksi' => $transactionDate,
                'nilai_transaksi' => $nilaiTransaksi,
                'filename' => $this->filename, // Tambahkan filename untuk tracking
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error("Error creating BsData model: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Extract retrieval reference dari format Ref[FUC2412311757280007D1] menjadi FUC2412311757280007
     */
    private function extractRetrievalReference($rawValue)
    {
        if (empty($rawValue)) {
            Log::info("Empty retrieval_ref_number, using default: '-'");
            return '-';
        }
        
        Log::info("Raw retrieval reference: {$rawValue}");
        
        // Pattern untuk extract dari format Ref[FUC2412311757280007D1]
        if (preg_match('/Ref\[([A-Z0-9]+)(?:D\d+)?\]/', $rawValue, $matches)) {
            $extractedRef = $matches[1];
            Log::info("Extracted retrieval reference: {$extractedRef}");
            return $extractedRef;
        }
        
        // Pattern alternatif untuk format lain yang mungkin ada
        if (preg_match('/\[([A-Z0-9]+)(?:D\d+)?\]/', $rawValue, $matches)) {
            $extractedRef = $matches[1];
            Log::info("Extracted retrieval reference (alternative pattern): {$extractedRef}");
            return $extractedRef;
        }
        
        // Jika tidak match pattern, coba ambil yang di dalam kurung siku
        if (preg_match('/\[([^\]]+)\]/', $rawValue, $matches)) {
            $content = $matches[1];
            
            // Hapus suffix D1, D2, dll jika ada
            $cleanContent = preg_replace('/D\d+$/', '', $content);
            Log::info("Extracted and cleaned retrieval reference: {$cleanContent}");
            return $cleanContent;
        }
        
        // Jika tidak ada pattern yang cocok, gunakan nilai asli tapi bersihkan
        $cleaned = trim(str_replace(['Ref', '[', ']'], '', $rawValue));
        Log::warning("No pattern matched for retrieval reference, using cleaned value: {$cleaned}");
        return $cleaned ?: '-';
    }

    /**
     * Extract transaction date dari kolom tgl_transaksi
     */
    private function extractTransactionDateFromRow($row)
    {
        Log::info("=== Extracting Transaction Date from tgl_transaksi column ===");
        
        // Cari kolom yang mungkin berisi tanggal transaksi
        $dateColumns = [
            'tgl_transaksi',
            'tanggal_transaksi', 
            'transaction_date',
            'date',
            'tgl',
            'tanggal'
        ];
        
        foreach ($dateColumns as $column) {
            if (isset($row[$column]) && !empty($row[$column])) {
                $value = $row[$column];
                Log::info("Checking column {$column} for date: " . $value);
                
                $date = $this->convertToDate($value);
                
                if ($date) {
                    Log::info("Successfully extracted transaction date from {$column}: {$date}");
                    return $date;
                }
            }
        }
        
        Log::warning("No transaction date found in row");
        return null;
    }

    private function convertToDate($value)
    {
        if (empty($value)) {
            return null;
        }
        
        try {
            // Handle format 8 digit: 10012025 = 10 Januari 2025 (DDMMYYYY)
            if (is_numeric($value) && strlen((string)$value) == 8) {
                $valueStr = (string)$value;
                Log::info("Processing 8-digit date format: {$valueStr}");
                
                // Format: DD-MM-YYYY
                $day = (int)substr($valueStr, 0, 2);     // Digit 1-2: Day (10)
                $month = (int)substr($valueStr, 2, 2);   // Digit 3-4: Month (01)
                $year = (int)substr($valueStr, 4, 4);    // Digit 5-8: Year (2025)
                
                Log::info("Parsed as DDMMYYYY: Day={$day}, Month={$month}, Year={$year}");
                
                // Validasi tanggal
                if ($year >= 2020 && $year <= 2030 && $month >= 1 && $month <= 12 && $day >= 1 && $day <= 31) {
                    try {
                        $date = Carbon::createFromDate($year, $month, $day);
                        $result = $date->format('Y-m-d');
                        Log::info("Successfully converted 8-digit DDMMYYYY format '{$valueStr}' to date: {$result}");
                        return $result;
                    } catch (\Exception $e) {
                        Log::warning("Failed to create date from DDMMYYYY format: " . $e->getMessage());
                    }
                } else {
                    Log::warning("Invalid date components for DDMMYYYY: Day={$day}, Month={$month}, Year={$year}");
                }
            }
            
            // Handle format 7 digit: 2012025 = 2 Januari 2025 (DMMYYYY)
            if (is_numeric($value) && strlen((string)$value) == 7) {
                $valueStr = (string)$value;
                Log::info("Processing 7-digit date format: {$valueStr}");
                
                // Format: D-MM-YYYY
                $day = (int)substr($valueStr, 0, 1);     // Digit 1: Day (2)
                $month = (int)substr($valueStr, 1, 2);   // Digit 2-3: Month (01)
                $year = (int)substr($valueStr, 3, 4);    // Digit 4-7: Year (2025)
                
                Log::info("Parsed as DMMYYYY: Day={$day}, Month={$month}, Year={$year}");
                
                // Validasi tanggal
                if ($year >= 2020 && $year <= 2030 && $month >= 1 && $month <= 12 && $day >= 1 && $day <= 31) {
                    try {
                        $date = Carbon::createFromDate($year, $month, $day);
                        $result = $date->format('Y-m-d');
                        Log::info("Successfully converted 7-digit DMMYYYY format '{$valueStr}' to date: {$result}");
                        return $result;
                    } catch (\Exception $e) {
                        Log::warning("Failed to create date from DMMYYYY format: " . $e->getMessage());
                    }
                } else {
                    Log::warning("Invalid date components for DMMYYYY: Day={$day}, Month={$month}, Year={$year}");
                }
            }
            
            // Excel date number
            if (is_numeric($value) && $value > 25000 && $value < 50000) {
                $dateTime = ExcelDate::excelToDateTimeObject($value);
                $date = $dateTime->format('Y-m-d');
                Log::info("Converted Excel serial number {$value} to date: {$date}");
                return $date;
            }
            
            // String formats
            if (is_string($value)) {
                $formats = ['d/m/Y', 'd-m-Y', 'Y-m-d', 'm/d/Y', 'm-d-Y', 'd/m/y', 'd-m-y'];
                foreach ($formats as $format) {
                    try {
                        $date = Carbon::createFromFormat($format, trim($value));
                        if ($date && $date->year >= 2020 && $date->year <= 2030) {
                            $result = $date->format('Y-m-d');
                            Log::info("Converted string '{$value}' using format '{$format}' to date: {$result}");
                            return $result;
                        }
                    } catch (\Exception $e) {
                        continue;
                    }
                }
            }
            
        } catch (\Exception $e) {
            Log::error("Failed to convert '{$value}' to date: " . $e->getMessage());
        }
        
        Log::warning("Could not parse date value: {$value}");
        return null;
    }


    /**
     * Clear data existing untuk transaction date yang sama
     */
    private function clearExistingData($transactionDate)
    {
        try {
            $deletedCount = BsData::where('tgl_transaksi', $transactionDate)->count();
            
            if ($deletedCount > 0) {
                BsData::where('tgl_transaksi', $transactionDate)->delete();
                Log::info("Cleared {$deletedCount} existing BS records for date: {$transactionDate}");
            } else {
                Log::info("No existing BS records found for date: {$transactionDate}");
            }
        } catch (\Exception $e) {
            Log::error("Error clearing existing data: " . $e->getMessage());
        }
    }

    /**
     * Improved numeric value cleaning
     */
    private function cleanNumericValue($value)
    {
        if (is_null($value) || $value === '' || $value === '-') {
            return 0;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        if (is_string($value)) {
            $value = trim($value);
            
            // Handle Indonesian format: 40.000.000,00 -> 40000000.00
            if (preg_match('/^\d{1,3}(\.\d{3})*,\d{2}$/', $value)) {
                $value = str_replace('.', '', $value);
                $value = str_replace(',', '.', $value);
                return (float) $value;
            }
            
            // Handle format: 40,000,000 -> 40000000
            if (preg_match('/^\d{1,3}(,\d{3})+$/', $value)) {
                $value = str_replace(',', '', $value);
                return (float) $value;
            }
            
            // Remove all non-numeric except dot, comma, minus
            $value = preg_replace('/[^\d\.,\-]/', '', $value);
            
            if (is_numeric($value)) {
                return (float) $value;
            }
        }

        return 0;
    }

    public function batchSize(): int
    {
        return 500; // Kurangi batch size untuk stabilitas
    }

    public function chunkSize(): int
    {
        return 500; // Kurangi chunk size untuk stabilitas
    }

    public function rules(): array
    {
        return [
            // Validasi minimal untuk memastikan data tidak kosong total
            'tgl_transaksi' => 'nullable',
            'retrieval_ref_number' => 'nullable|string',
            'nilai_transaksi' => 'nullable',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'retrieval_ref_number.string' => 'Retrieval Reference Number must be a string',
        ];
    }

    /**
     * Reset flag ketika import selesai
     */
    public function __destruct()
    {
        self::$isDataCleared = [];
        Log::info("BS Import completed for filename: {$this->filename}");
    }
}
