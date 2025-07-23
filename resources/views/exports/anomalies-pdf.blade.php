<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Anomali Bank Lampung</title>
    <style>
        @page {
            margin: 8mm 8mm 10mm 8mm;
            size: A4 landscape;
        }
        body {
            font-family: Arial, 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 9px;
            color: #212529;
            margin: 0;
            padding: 0;
        }
        .letterhead {
            border-bottom: 2px solid #1e40af;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            padding: 8px 10px 12px 10px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logo { max-height: 45px; }
        .bank-info { text-align: right; }
        .bank-info div {
            margin: 1px 0;
            font-size: 9px;
        }
        .bank-info .bank-name {
            font-size: 11px;
            font-weight: bold;
            color: #1e40af;
        }
        .report-title {
            background: #1e40af;
            color: #fff;
            text-align: center;
            padding: 8px;
            border-radius: 4px;
            margin-bottom: 8px;
        }
        .report-title h1 {
            font-size: 12px;
            font-weight: 600;
            margin: 0;
        }
        .report-info {
            background: #f1f5f9;
            border-left: 3px solid #1e40af;
            display: flex;
            justify-content: flex-start;
            padding: 7px 10px;
            margin-bottom: 10px;
            font-size: 8.5px;
        }
        .info-item {
            margin-right: 18px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2px;
            background: #fff;
        }
        th, td {
            border: 1px solid #bbb;
            padding: 3px 2px;
            vertical-align: middle;
        }
        th {
            background-color: #f6f8fa;
            text-align: center;
            font-weight: bold;
            font-size: 8.5px;
        }
        td.text-center { text-align: center; }
        td.text-right  { text-align: right; }
        td.text-left   { text-align: left; }
        .badge {
            display: inline-block;
            padding: 1.3px 5px 1.3px 5px;
            border-radius: 3px;
            font-size: 7.6px;
            font-weight: bold;
            color: white;
            letter-spacing: 0.4px;
        }
        .bg-success   { background-color: #198754; }
        .bg-danger    { background-color: #dc3545; }
        .bg-warning   { background-color: #ffc107; color: #212529; }
        .bg-primary   { background-color: #0d6efd; }
        .bg-info      { background-color: #0dcaf0; color: #000; }
        .bg-secondary { background-color: #6c757d; }
        .bg-purple    { background-color: #6f42c1; }
        .status-inline {
            white-space: nowrap;
            font-family: 'Consolas','Menlo',monospace,Arial,sans-serif;
            font-size: 8px;
            border-radius: 2px;
            background: #efefef;
            padding: 1.8px 3.5px;
            font-weight: 500;
            display: inline-block;
        }
        tfoot td {
            font-weight: bold;
            background-color: #fff3cd;
            font-size: 9px;
        }
        .footer {
            margin-top: 16px;
            text-align: center;
            font-size: 8.9px;
            color: #666;
        }
    </style>
</head>
<body>
    {{-- Letterhead --}}
    <div class="letterhead">
        <div class="header-content">
            <div class="logo-section">
                <img src="{{ public_path('assets/images/logo-bl2.png') }}" class="logo" alt="Bank Lampung">
            </div>
            <div class="bank-info">
                <div class="bank-name">PT Bank Pembangunan Daerah Lampung</div>
                <div>Jl. Wolter Monginsidi No. 182, Bandar Lampung</div>
                <div>Telp: (0721) 261044 | www.banklampung.co.id</div>
            </div>
        </div>
    </div>
    {{-- Report Title --}}
    <div class="report-title">
        <h1>LAPORAN REKONSILIASI TRANSAKSI HARIAN BI-FAST</h1>
    </div>
    {{-- Report Info --}}
    <div class="report-info">
        <div class="info-item">
            <strong>Tanggal Transaksi:</strong> {{ \Carbon\Carbon::parse($date)->format('d F Y') }}
        </div>
        <div class="info-item">
            <strong>Total Anomali:</strong> {{ count($anomalies) }} transaksi
        </div>
        <div class="info-item">
            <strong>Dibuat pada:</strong> {{ now()->format('d F Y') }}
        </div>
    </div>
    {{-- Table --}}
    <table>
        <thead>
            <tr>
                <th rowspan="2">No</th>
                <th rowspan="2">End-to-End ID</th>
                <th rowspan="2">Reference Number</th>
                <th rowspan="2">Trx Time</th>
                <th rowspan="2">Trx Source</th>
                <th rowspan="2">Source Account</th>
                <th rowspan="2">Destination Account</th>
                <th rowspan="2">Amount</th>
                <th rowspan="2">Status Data</th>
                <th colspan="3">Status</th>
                <th rowspan="2">Keterangan</th>
            </tr>
            <tr>
                <th>AMS</th>
                <th>Debit</th>
                <th>Credit</th>
            </tr>
        </thead>
        <tbody>
            @php $totalNominal = 0; @endphp
            @foreach($anomalies as $index => $item)
                @php
                    $cip = $item['cip'] ?? null;
                    $ams = $item['ams'] ?? null;
                    $bs  = $item['bs'] ?? null;

                    $endToEndId = $cip->end_to_end_id ?? $ams->bifast_reference_number ?? '-';
                    $referenceNumber = $ams->reference_number ?? $bs->retrieval_ref_number ?? '-';

                    if ($ams?->trx_date_time) {
                        $trxTime = \Carbon\Carbon::parse($ams->trx_date_time)->format('H:i:s');
                    } elseif ($cip?->transaction_date) {
                        $trxTime = \Carbon\Carbon::parse($cip->transaction_date)->format('H:i:s');
                    } else {
                        $trxTime = '-';
                    }

                    $source = $ams->source_account_number ?? $cip->rekening_pengirim ?? '-';
                    $dest   = $ams->destination_account_number ?? $cip->rekening_penerima ?? '-';
                    $src = $ams->trx_source ?? '-';

                    $amount = ($cip->debit ?? 0) + ($cip->kredit ?? 0);
                    $amount = $amount ?: $ams->trx_amount ?? $bs->nilai_transaksi ?? 0;
                    $totalNominal += $amount;

                    // STATUS DATA INLINE
                    $statusInline = '';
                    $inlineMap = [
                        'CIP' => $cip ? '<span class="badge bg-success">CIP</span>' : '<span class="badge bg-danger">CIP</span>',
                        'AMS' => $ams ? '<span class="badge bg-success">AMS</span>' : '<span class="badge bg-danger">AMS</span>',
                        'BS'  => $bs  ? '<span class="badge bg-success">BS</span>'  : '<span class="badge bg-danger">BS</span>',
                    ];
                    $statusInline = implode(' ', $inlineMap);

                    $keterangan = 'Data Tidak Lengkap';
                    $ketClass = 'bg-danger';
                    if ($cip && $ams && $bs) {
                        $keterangan = 'Data Lengkap'; $ketClass = 'bg-success';
                    } elseif (!$cip && !$ams && $bs) {
                        $keterangan = 'Hanya Ditemukan di BS'; $ketClass = 'bg-secondary';
                    } elseif ($cip && !$ams && !$bs) {
                        $keterangan = 'Hanya Ditemukan di CIP'; $ketClass = 'bg-primary';
                    } elseif (!$cip && $ams && !$bs) {
                        $keterangan = 'Hanya Ditemukan di AMS'; $ketClass = 'bg-purple';
                    } elseif ($cip && $ams && !$bs) {
                        $keterangan = 'Tidak Ditemukan di BS'; $ketClass = 'bg-warning';
                    } elseif ($cip && !$ams && $bs) {
                        $keterangan = 'Tidak Ditemukan di AMS'; $ketClass = 'bg-info';
                    } elseif (!$cip && $ams && $bs) {
                        $keterangan = 'Tidak Ditemukan di CIP'; $ketClass = 'bg-info';
                    } elseif (isset($item['type']) && $item['type'] === 'AMOUNT_MISMATCH') {
                        $keterangan = 'Beda Nominal'; $ketClass = 'bg-danger';
                    }

                    $statusAms = $ams?->trx_status ? ucfirst($ams->trx_status) : '-';
                    $statusDebit = $ams?->debit_status ? ucfirst($ams->debit_status) : '-';
                    $statusCredit = $ams?->credit_status ? ucfirst($ams->credit_status) : '-';

                    $badgeAms = $ams ? "<span class='badge " . (strtolower($statusAms) === 'success' ? 'bg-success' : 'bg-danger') . "'>{$statusAms}</span>" : '-';
                    $badgeDebit = $ams ? "<span class='badge " . (strtolower($statusDebit) === 'success' ? 'bg-success' : 'bg-danger') . "'>{$statusDebit}</span>" : '-';
                    $badgeCredit = $ams ? "<span class='badge " . (strtolower($statusCredit) === 'success' ? 'bg-success' : 'bg-danger') . "'>{$statusCredit}</span>" : '-';
                @endphp
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>{{ $endToEndId }}</td>
                    <td>{{ $referenceNumber }}</td>
                    <td class="text-center">{{ $trxTime }}</td>
                    <td>{{ $src }}</td>
                    <td>{{ $source }}</td>
                    <td>{{ $dest }}</td>
                    <td class="text-right">{{ number_format($amount, 0, ',', '.') }}</td>
                    <td class="text-center">{!! $statusInline !!}</td>
                    <td class="text-center">{!! $badgeAms !!}</td>
                    <td class="text-center">{!! $badgeDebit !!}</td>
                    <td class="text-center">{!! $badgeCredit !!}</td>
                    <td class="text-center"><span class="badge {{ $ketClass }}">{{ $keterangan }}</span></td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="7" class="text-right">TOTAL</td>
                <td class="text-right">{{ number_format($totalNominal, 0, ',', '.') }}</td>
                <td colspan="5"></td>
            </tr>
        </tfoot>
    </table>
    {{-- Footer --}}
    <div class="footer">
        <p>Laporan ini dibuat secara otomatis pada {{ now()->format('d F Y H:i:s') }}</p>
        <p>PT Bank Lampung</p>
    </div>
</body>
</html>
