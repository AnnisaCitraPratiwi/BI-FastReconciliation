<?php

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
    private $processedReferences = [];

    public function __construct($filename)
    {
        $this->filename = $filename;
        Log::info("BS Import initialized with filename: {$filename}");
    }

    public function model(array $row)
    {
        if (empty(array_filter($row))) {
            Log::info("Skipping empty row");
            return null;
        }

        $transactionDate = $this->extractTransactionDateFromRow($row);
        if (!$transactionDate) {
            Log::warning("No transaction date, skipping row");
            return null;
        }

        if (!isset(self::$isDataCleared[$transactionDate])) {
            $this->clearExistingData($transactionDate);
            self::$isDataCleared[$transactionDate] = true;
        }

        $rawRetrievalRef = $row['retrieval_ref_number'] ?? $row['retrieval_reference_number'] ?? null;
        $retrievalRefNumber = $this->extractRetrievalReference($rawRetrievalRef);
        $dupeKey = $transactionDate . '_' . $retrievalRefNumber;

        if (in_array($dupeKey, $this->processedReferences)) {
            Log::warning("Duplicate BS row detected: {$dupeKey}");
            return null;
        }
        $this->processedReferences[] = $dupeKey;

        $nilaiTransaksi = $this->cleanNumericValue($row['nilai_transaksi'] ?? 0);

        return new BsData([
            'transaction_date' => $transactionDate,
            'retrieval_ref_number' => $retrievalRefNumber,
            'tgl_transaksi' => $transactionDate,
            'nilai_transaksi' => $nilaiTransaksi,
            'filename' => $this->filename,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function extractRetrievalReference($raw)
    {
        if (empty($raw)) return '-';

        if (preg_match('/Ref\[([A-Z0-9]+)(?:D\d+)?\]/', $raw, $matches)) return $matches[1];
        if (preg_match('/\[([A-Z0-9]+)(?:D\d+)?\]/', $raw, $matches)) return $matches[1];
        if (preg_match('/\[([^\]]+)\]/', $raw, $matches)) return preg_replace('/D\d+$/', '', $matches[1]);

        return trim(str_replace(['Ref', '[', ']'], '', $raw)) ?: '-';
    }

    private function extractTransactionDateFromRow($row)
    {
        $dateColumns = ['tgl_transaksi', 'tanggal_transaksi', 'transaction_date', 'date', 'tgl', 'tanggal'];

        // foreach ($dateColumns as $col) {
        //     if (!empty($row[$col])) {
        //         return $this->convertToDate($row[$col]);
        //     }
        // }
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

    private function cleanNumericValue($value)
    {
        if (empty($value) || $value === '-') {
            return 0;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        $value = trim($value);
        $value = str_replace(['.', ','], ['', '.'], $value); // Indo format
        $value = preg_replace('/[^\d\.\-]/', '', $value);
        return is_numeric($value) ? (float) $value : 0;
    }

    private function clearExistingData($transactionDate)
    {
        $deletedCount = BsData::where('tgl_transaksi', $transactionDate)->count();
        BsData::where('tgl_transaksi', $transactionDate)->delete();
        Log::info("Cleared {$deletedCount} existing BS records for date: {$transactionDate}");
    }

    public function batchSize(): int { return 500; }
    public function chunkSize(): int { return 500; }

    public function rules(): array
    {
        return [
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

    public function __destruct()
    {
        self::$isDataCleared = [];
        Log::info("BS Import completed for filename: {$this->filename}");
    }
}
