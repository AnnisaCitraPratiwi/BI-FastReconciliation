<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class AllAnomaliesExport implements FromCollection, WithHeadings, WithStyles, WithTitle, WithColumnFormatting, WithColumnWidths
{
    protected $anomalies;
    protected $date;

    public function __construct($anomalies, $date)
    {
        $this->anomalies = $anomalies;
        $this->date = $date;
    }

    public function collection()
    {
        $data = collect();
        $no = 1;

        foreach ($this->anomalies as $anomaly) {
            $cip = $anomaly['cip'] ?? null;
            $ams = $anomaly['ams'] ?? null;
            $bs = $anomaly['bs'] ?? null;

            $endToEndId = $cip->end_to_end_id ?? ($ams->bifast_reference_number ?? '-');
            $referenceNumber = $ams->reference_number ?? ($cip->reference_number ?? '-');
            $referenceNumber = optional($ams)->reference_number ?? optional($bs)->retrieval_ref_number ?? '-';

            $trxTime = $ams && $ams->trx_date_time ? $ams->trx_date_time->format('H:i:s') : ($cip && $cip->transaction_date ? $cip->transaction_date->format('H:i:s') : '-');
            $trxSource = $ams->trx_source ?? '-';
            $srcAccount = $ams ? " " . $ams->source_account_number : ($cip->rekening_pengirim ?? '-');
            $dstAccount = $ams ? " " . $ams->destination_account_number : ($cip->rekening_penerima ?? '-');

            $nominal = ($cip->debit ?? 0) + ($cip->kredit ?? 0);
            if (!$nominal) $nominal = $ams->trx_amount ?? 0;
            if (!$nominal) $nominal = $bs->nilai_transaksi ?? 0;

            $statusData =
                ($cip ? 'Ada' : 'Tidak Ada') . ' | ' .
                ($ams ? 'Ada' : 'Tidak Ada') . ' | ' .
                ($bs ? 'Ada' : 'Tidak Ada');
            $statusAMS = $ams ? ($ams->trx_status ?? '-') : '-';
            $statusDebit = $ams ? (ucfirst($ams->debit_status ?? '-')) : '-';
            $statusCredit = $ams ? (ucfirst($ams->credit_status ?? '-')) : '-';

            $cipFound = !is_null($cip);
            $amsFound = !is_null($ams);
            $bsFound = !is_null($bs);
            if ($cipFound && $amsFound && $bsFound) {
                $keterangan = 'Data Lengkap';
            } elseif (!$cipFound && !$amsFound && $bsFound) {
                $keterangan = 'Hanya Ditemukan di BS';
            } elseif (!$cipFound && $amsFound && !$bsFound) {
                $keterangan = 'Hanya Ditemukan di AMS';
            } elseif ($cipFound && !$amsFound && !$bsFound) {
                $keterangan = 'Hanya Ditemukan di CIP';
            } elseif ($cipFound && $amsFound && !$bsFound) {
                $keterangan = 'Tidak Ditemukan di BS';
            } elseif ($cipFound && !$amsFound && $bsFound) {
                $keterangan = 'Tidak Ditemukan di AMS';
            } elseif (!$cipFound && $amsFound && $bsFound) {
                $keterangan = 'Tidak Ditemukan di CIP';
            } elseif (isset($anomaly['type']) && $anomaly['type'] === 'AMOUNT_MISMATCH') {
                $keterangan = 'Beda Nominal';
            } else {
                $keterangan = 'Data Tidak Lengkap';
            }

            $data->push([
                'no' => $no++,
                'end_to_end_id' => $endToEndId,
                'reference_number' => $referenceNumber,
                'trx_time' => $trxTime,
                'trx_source' => $trxSource,
                'rekening_pengirim' => $srcAccount,
                'rekening_penerima' => $dstAccount,
                'nominal' => $nominal,
                'status_data' => $statusData,
                'status_ams' => $statusAMS,
                'status_debit' => $statusDebit,
                'status_credit' => $statusCredit,
                'keterangan' => $keterangan,
            ]);
        }
        return $data;
    }

    public function headings(): array
    {
        return [
            'No',
            'End-to-End ID',
            'Reference Number',
            'Trx Time',
            'Trx Source',
            'Source Account',
            'Destination Account',
            'Amount',
            'Status Data (CIP|AMS|BS)',
            'Status AMS',
            'Status Debit',
            'Status Credit',
            'Keterangan',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'F' => NumberFormat::FORMAT_TEXT, // Source Account
            'G' => NumberFormat::FORMAT_TEXT, // Destination Account
            'H' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // Amount
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 30,
            'C' => 30,
            'D' => 12,
            'E' => 18,
            'F' => 20,
            'G' => 20,
            'H' => 18,
            'I' => 22,
            'J' => 16,
            'K' => 16,
            'L' => 16,
            'M' => 25,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFE2E2E2'],
                ],
                'borders' => [
                    'allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                ],
            ],
            'A2:M' . ($sheet->getHighestRow()) => [
                'borders' => [
                    'allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                ],
            ],
            'H:H' => [
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT],
            ],
        ];
    }

    public function title(): string
    {
        return 'Summary' . \Carbon\Carbon::parse($this->date)->format('d-m-Y');
    }
}
