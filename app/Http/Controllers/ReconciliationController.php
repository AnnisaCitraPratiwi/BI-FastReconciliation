<?php

namespace App\Http\Controllers;

use App\Models\CipData;
use App\Models\AmsData;
use App\Models\BsData;
use App\Models\UploadHistory;
use App\Models\ReconciliationResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\CipDataImport;
use App\Imports\AmsDataImport;
use App\Imports\BsDataImport;
use App\Exports\AllAnomaliesExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Models\ReconciliationHistory;
use Illuminate\Support\Facades\Storage; 
use App\Jobs\ReconciliationJob; 

class ReconciliationController extends Controller
{
    public function index()
    {
        $availableDates = collect([
            CipData::getAvailableDates(),
            AmsData::getAvailableDates(),
            BsData::getAvailableDates()
        ])->flatten()->unique()->sort()->values();

        $uploadHistories = UploadHistory::with('user') 
                                ->where('uploaded_by', Auth::id())
                                ->orderBy('created_at', 'desc')
                                ->limit(10)
                                ->get();

        $filteredDates = $this->getFilteredDates();
        
        return view('reconciliation.index', compact('filteredDates', 'uploadHistories'));
    }

    public function getFilteredDates()
    {
        $cipDates = \App\Models\CipData::select('transaction_date')->distinct();
        $amsDates = \App\Models\AmsData::select('transaction_date')->distinct();
        $bsDates  = \App\Models\BsData::select('transaction_date')->distinct();

        $dates = DB::table(DB::raw("({$cipDates->toSql()}) as cip"))
            ->mergeBindings($cipDates->getQuery())
            ->joinSub($amsDates, 'ams', function ($join) {
                $join->on('cip.transaction_date', '=', 'ams.transaction_date');
            })
            ->joinSub($bsDates, 'bs', function ($join) {
                $join->on('cip.transaction_date', '=', 'bs.transaction_date');
            })
            ->select('cip.transaction_date')
            ->orderBy('cip.transaction_date')
            ->pluck('transaction_date')
            ->toArray();

        return $dates;
    }


    public function uploadCip(Request $request)
    {
        $minFiles = 1;                    
        $request->validate([
            'cip_file'     => "required|array|min:$minFiles",        
            'cip_file.*'   => 'mimes:xlsx,xls|max:51200'           
        ]);
    
        DB::beginTransaction();
        try {
            $uploaded = [];
            foreach ($request->file('cip_file') as $file) {         
                $filename = $file->getClientOriginalName();
                
                Excel::import(new CipDataImport($filename), $file);
                UploadHistory::create([
                    'filename'    => $filename,
                    'file_type'   => 'cip',
                    'uploaded_by' => Auth::id(),
                ]);
                $uploaded[] = $filename;
            }
            DB::commit();
            return back()->with('success', "Successfully uploaded ".count($uploaded)." CIP file(s): ".implode(', ', $uploaded));
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error uploading CIP: '.$e->getMessage());
            return back()->with('error', 'Error uploading CIP: '.$e->getMessage());
        }
    }
    
    public function uploadAms(Request $request)
    {
        $minFiles = 1;
        $request->validate([
            'ams_file'   => "required|array|min:$minFiles",
            'ams_file.*' => 'mimes:xlsx,xls|max:51200'
        ]);
    
        DB::beginTransaction();
        try {
            $uploaded = [];
            foreach ($request->file('ams_file') as $file) {
                $filename = $file->getClientOriginalName();
    
                // ambil tanggal dari nama file
                if (!preg_match('/(\d{8})/', $filename, $m)) {
                    throw new \Exception("{$filename}: date format not found");
                }
                $dateString = $m[1];
                $transactionDate = Carbon::createFromFormat('Ymd', $dateString)->format('Y-m-d');
     
                Excel::import(new AmsDataImport($filename), $file);
                $records = AmsData::where('transaction_date', $transactionDate)->count();
                if ($records == 0) {
                    throw new \Exception("{$filename}: tidak ada data yang ter-import");
                }
    
                UploadHistory::create([
                    'filename'    => $filename,
                    'file_type'   => 'ams',
                    'uploaded_by' => Auth::id(),
                ]);
                $uploaded[] = "{$filename} ({$records} rec)";
            }
            DB::commit();
            return back()->with('success', "Successfully uploaded ".count($uploaded)." AMS file(s): ".implode(', ', $uploaded));
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error uploading AMS: '.$e->getMessage());
            return back()->with('error', 'Error uploading AMS: '.$e->getMessage());
        }
    }
    
    public function uploadBs(Request $request)
    {
        $minFiles = 1;
        $request->validate([
            'bs_file'   => "required|array|min:$minFiles",
            'bs_file.*' => 'mimes:xlsx,xls|max:51200'
        ]);
    
        DB::beginTransaction();
        try {
            $uploaded = [];
            foreach ($request->file('bs_file') as $file) {
                $filename = $file->getClientOriginalName();
    
                Excel::import(new BsDataImport($filename), $file);
    
                $transactionDate = BsData::select('tgl_transaksi')
                                          ->orderByDesc('tgl_transaksi')
                                          ->first()
                                          ->tgl_transaksi ?? null;
                if (!$transactionDate) {
                    throw new \Exception("{$filename}: no data was imported");
                }
                $records = BsData::where('tgl_transaksi', $transactionDate)->count();
    
                UploadHistory::create([
                    'filename'    => $filename,
                    'file_type'   => 'bs',
                    'uploaded_by' => Auth::id(),
                ]);
                $uploaded[] = "{$filename} (".$records." rec ".Carbon::parse($transactionDate)->format('d/m/Y').")";
            }
            DB::commit();
            return back()->with('success', "Successfully uploaded ".count($uploaded)." BS file(s): ".implode(', ', $uploaded));
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error uploading BS: '.$e->getMessage());
            return back()->with('error', 'Error uploading BS: '.$e->getMessage());
        }
    }

    public function getUploadHistory()
    {
        $uploadHistory = UploadHistory::with('user')
            ->where('uploaded_by', Auth::id())
            ->orderBy('created_at', 'desc');
            
        return view('upload-history', compact('uploadHistory'));
    }

    public function getRecordsByDate(Request $request)
    {
        $date = $request->input('date');
        
        $cipCount = DB::table('cip_data')->whereDate('transaction_date', $date)->count();
        $amsCount = DB::table('ams_data')->whereDate('transaction_date', $date)->count();
        $bsCount = DB::table('bs_data')->whereDate('tgl_transaksi', $date)->count();
        
        return response()->json([
            'date' => $date,
            'cip_records' => $cipCount,
            'ams_records' => $amsCount,
            'bs_records' => $bsCount
        ]);
    }

    public function processDetailedReconciliation(Request $request)
    {
        $date = $request->input('date');
        if (!$date) {
            return back()->with('error', 'Please select a date first.');
        }
        
        try {
            $amsCount = AmsData::whereDate('transaction_date', $date)->count();
            if ($amsCount >= 17000) {  
                dispatch(new ReconciliationJob($date, auth()->id()));
                return redirect()->route('reconciliation')->with('success', "Reconciliation is being processed. Please check the history shortly.");
            }
            DB::beginTransaction();

            $results = $this->performReconciliation($date);
            $historyRecord = $this->saveReconciliationHistory($date, $results['anomalies'], $results['summary']);

            DB::commit();
            
            return view('reconciliation.detailed-results', [
                'anomalies' => $results['anomalies'],
                'summary' => $results['summary'],
                'date' => $date,
                'historyRecord' => $historyRecord
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error processing reconciliation: " . $e->getMessage() . ' on line ' . $e->getLine() . ' in file ' . $e->getFile());
            return back()->with('error', 'Error processing reconciliation: ' . $e->getMessage());
        }
    }

    public function saveReconciliationHistory($date, $anomalies, $summary,$userId = null)
    {
        try {
            $processedByUserId = $userId ?? auth()->id();
            // Hapus record rekonsiliasi lama jika ada
            $existingRecord = ReconciliationHistory::where('reconciliation_date', $date)
                ->where('user_id', $processedByUserId)
                ->first();

            if ($existingRecord) {
                // Hapus file lama juga kalau perlu
                if ($existingRecord->excel_file_path) {
                    Storage::disk('public')->delete($existingRecord->excel_file_path);
                }
                if ($existingRecord->pdf_file_path) {
                    Storage::disk('public')->delete($existingRecord->pdf_file_path);
                }
                
                $existingRecord->delete();
            }

            // Buat record baru
            $historyRecord = new ReconciliationHistory();
            $historyRecord->reconciliation_date = $date;
            $historyRecord->user_id = $userId ?? auth()->id();

            // Update data
            $historyRecord->total_anomalies = $anomalies->count();
            $historyRecord->cip_records = $summary['data_counts']['cip_total'] ?? 0;
            $historyRecord->ams_records = $summary['data_counts']['ams_total'] ?? 0;
            $historyRecord->bs_records = $summary['data_counts']['bs_total'] ?? 0;
            $historyRecord->summary_data = $summary;
            $historyRecord->anomalies_json = $anomalies;

            // Pastikan folder exists
            $reportPath = storage_path('app/public/reconciliation_reports');
            if (!file_exists($reportPath)) {
                mkdir($reportPath, 0755, true);
            }

            // Generate file names
            $timestamp = time();
            $dateStr = Carbon::parse($date)->format('Y-m-d');

            // Generate dan simpan file Excel
            $excelFileName = "Hasil_Reconciliation_{$dateStr}.xlsx";
            $excelPath = "reconciliation_reports/{$excelFileName}";

            try {
                Excel::store(new AllAnomaliesExport($anomalies, $date), $excelPath, 'public');
                $historyRecord->excel_file_path = $excelPath;
            } catch (\Exception $e) {
                Log::error("Failed to generate Excel: " . $e->getMessage());
            }

            // Generate dan simpan file PDF
            $pdfFileName = "Hasil_Reconciliation_{$dateStr}.pdf";
            $pdfPath = "reconciliation_reports/{$pdfFileName}";

            try {
                $pdf = PDF::loadView('exports.anomalies-pdf', compact('anomalies', 'date'));
                $pdf->setPaper('A4', 'landscape');

                Storage::disk('public')->put($pdfPath, $pdf->output());
                $historyRecord->pdf_file_path = $pdfPath;
            } catch (\Exception $e) {
                Log::error("Failed to generate PDF: " . $e->getMessage());
            }

            $historyRecord->save();

            Log::info("Reconciliation history saved successfully for date: {$date}");
            return $historyRecord;

        } catch (\Exception $e) {
            Log::error("Failed to save reconciliation history: " . $e->getMessage());
            throw $e;
        }
    } 
 
    public function performReconciliation($date)
    {
        \Log::info("=== [RECON START] Reconciliation for {$date} ===");

        // Membuat "Indexed CIP" dgn chunk
        $cipMap = [];
        CipData::whereDate('transaction_date', $date)
            ->select('end_to_end_id', 'id', 'transaction_date') // tambahkan kolom penting saja
            ->chunk(1000, function ($cips) use (&$cipMap) {
                foreach ($cips as $cip) {
                    $cipMap[$cip->end_to_end_id] = $cip;
                }
            });

        // Membuat "Indexed AMS"
        $amsMap = [];
        AmsData::whereDate('transaction_date', $date)
            ->select('bifast_reference_number', 'id', 'reference_number', 'trx_amount', 'debit_status', 'credit_status')
            ->chunk(1000, function ($amsList) use (&$amsMap) {
                foreach ($amsList as $ams) {
                    $amsMap[$ams->bifast_reference_number] = $ams;
                }
            });

        // Proses BS reversal map dan structured maps
        $bsReversalDetails = [];
        $bsMapByFullRef = [];
        $bsMapByTruncatedRef = [];
        $bsMapByPattern = [];

        BsData::whereDate('tgl_transaksi', $date)->chunk(1000, function ($bsChunk) use (&$bsReversalDetails, &$bsMapByFullRef, &$bsMapByTruncatedRef, &$bsMapByPattern) {
            $grouped = [];

            // Index grouped ref untuk reversal checking
            foreach ($bsChunk as $bs) {
                $ref = strtoupper(preg_replace("/[^A-Z0-9]/", "", $bs->retrieval_ref_number));
                $grouped[$ref][] = $bs;
            }

            foreach ($grouped as $ref => $records) {
                if (count($records) > 1) {
                    $total = collect($records)->sum('transaction_value');
                    if (abs($total) < 0.01) {
                        $bsReversalDetails[$ref] = [
                            'is_reversal' => true,
                            'records' => $records,
                            'count' => count($records),
                            'total_amount' => $total
                        ];
                    }
                }
            }

            foreach ($bsChunk as $bs) {
                if (empty($bs->retrieval_ref_number)) continue;
                $cleaned = strtoupper(preg_replace("/[^A-Z0-9]/", "", $bs->retrieval_ref_number));

                if (isset($bsReversalDetails[$cleaned])) continue;

                $bsMapByFullRef[$cleaned] = $bs;

                if (strlen($cleaned) > 2) {
                    $trunc = substr($cleaned, 0, -2);
                    $bsMapByTruncatedRef[$trunc][] = $bs;
                }

                if (strlen($cleaned) > 10) {
                    $pattern = substr($cleaned, 3, -3);
                    $bsMapByPattern[$pattern][] = $bs;
                }
            }
        });

        // Matching & Collecting anomaly langsung saat looping
        $results = [];
        $processedCip = [];
        $processedAms = [];
        $processedBs = [];

        AmsData::whereDate('transaction_date', $date)
            ->select('bifast_reference_number', 'reference_number', 'trx_amount', 'debit_status', 'credit_status', 'id','trx_source','source_account_number','destination_account_number','trx_date_time','trx_status')
            ->chunk(500, function ($amsList) use (
                &$cipMap, &$bsMapByFullRef, &$bsMapByTruncatedRef, &$bsMapByPattern, &$bsReversalDetails,
                &$results, &$processedCip, &$processedAms, &$processedBs
            ) {
                foreach ($amsList as $ams) {
                    $processedAms[$ams->id] = true;
                    $cip = $cipMap[$ams->bifast_reference_number] ?? null;
                    if ($cip) $processedCip[$cip->end_to_end_id] = true;

                    $refClean = strtoupper(preg_replace('/[^A-Z0-9]/', '', $ams->reference_number));
                    $bs = null;
                    $strategy = null;

                    if (!empty($refClean)) {
                        if (isset($bsMapByFullRef[$refClean])) {
                            $bs = $bsMapByFullRef[$refClean];
                            $strategy = 'exact';
                        } else {
                            foreach ($bsMapByTruncatedRef as $key => $array) {
                                if (str_starts_with($refClean, $key)) {
                                    $bs = count($array) === 1 ? $array[0] : $array[0];
                                    $strategy = 'starts_with';
                                    break;
                                }
                            }

                            if (!$bs && strlen($refClean) > 10) {
                                $patternKey = substr($refClean, 3, -3);
                                if (isset($bsMapByPattern[$patternKey])) {
                                    $bs = $bsMapByPattern[$patternKey][0];
                                    $strategy = 'pattern';
                                }
                            }
                        }
                    }

                    if ($bs) {
                        $processedBs[$bs->id] = true;
                    }

                    $results[] = [
                        'cip' => $cip,
                        'ams' => $ams,
                        'bs' => $bs,
                        'match_strategy' => $strategy
                    ];
                }
            });

        // Tambah data yatim CIP
        CipData::whereDate('transaction_date', $date)->chunk(1000, function ($chunk) use (&$results, &$processedCip) {
            foreach ($chunk as $cip) {
                if (!isset($processedCip[$cip->end_to_end_id])) {
                    $results[] = [
                        'cip' => $cip,
                        'ams' => null,
                        'bs' => null,
                        'match_strategy' => null
                    ];
                }
            }
        });

        // Tambah data yatim BS
        BsData::whereDate('tgl_transaksi', $date)->chunk(1000, function ($chunk) use (&$results, &$processedBs, &$bsReversalDetails) {
            foreach ($chunk as $bs) {
                if (!isset($processedBs[$bs->id])) {
                    $ref = strtoupper(preg_replace('/[^A-Z0-9]/', '', $bs->retrieval_ref_number));
                    if (!isset($bsReversalDetails[$ref])) {
                        $results[] = [
                            'cip' => null,
                            'ams' => null,
                            'bs' => $bs,
                            'match_strategy' => null
                        ];
                    }
                }
            }
        });

        // Langsung filter anomali ketika collect, jauh lebih aman
        $anomalies = [];
        foreach ($results as $item) {
            $cipFound = !is_null($item['cip']);
            $amsFound = !is_null($item['ams']);
            $bsFound = !is_null($item['bs']);

            if ($cipFound && $amsFound && $bsFound) continue;

            if ($amsFound) {
                $debit = strtoupper($item['ams']->debit_status ?? '');
                $credit = strtoupper($item['ams']->credit_status ?? '');

                if (in_array($debit, ['REVERSAL_SUCCESS', 'DRAFT', 'PENDING']) ||
                    in_array($credit, ['REVERSAL_SUCCESS', 'PENDING'])) {
                    continue;
                }

                if (
                    (in_array($debit, ['REVERSAL_FAILED']) || in_array($credit, ['FAILED'])) &&
                    $bsFound
                ) {
                    $bsRef = strtoupper(preg_replace('/[^A-Z0-9]/', '', $item['bs']->retrieval_ref_number));
                    if (isset($bsReversalDetails[$bsRef])) continue;
                }

                if ($item['match_strategy'] === 'reversal_group') continue;
            }

            $anomalies[] = $item;
        }

        // Summary hasil
        $summary = [
            'total_anomalies' => count($anomalies),
            'bs_reversals_detected' => count($bsReversalDetails),
            'reversal_records_count' => array_sum(array_map(fn($d) => $d['count'], $bsReversalDetails)),
            'data_counts' => [
                'cip_total' => count($cipMap),
                'ams_total' => count($amsMap),
                'bs_total' => count($bsMapByFullRef),
            ],
            'matching_strategies' => array_count_values(
                array_filter(
                    array_column($results, 'match_strategy'),
                    fn ($val) => is_string($val) || is_int($val)
                )
            ),

        ];

        \Log::info("=== [RECON DONE] for {$date}. Anomalies: " . count($anomalies));

        return [
            'anomalies' => collect($anomalies),
            'summary' => $summary
        ];
    }
 

    public function viewData(Request $request)
    {
        $date = $request->input('date') ?? $request->query('date');
        $search = $request->input('search');
        $activeTab = $request->input('tab') ?? $request->query('tab') ?? 'cip';

        if (!$date) {
            return redirect()->route('reconciliation')->with('error', 'Please select a date first.');
        }

        $cipData = $amsData = $bsData = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 50);

        $totalCip = CipData::whereDate('transaction_date', $date)->count();
        $totalAms = AmsData::whereDate('transaction_date', $date)->count();
        $totalBs  = BsData::whereDate('transaction_date', $date)->count();

        if ($activeTab === 'cip') {
            $cipQuery = CipData::whereDate('transaction_date', $date);

            if ($search) {
                $cipQuery->where(function ($query) use ($search) {
                    $query->where('end_to_end_id', 'like', "$search%")
                        ->orWhere('debit', 'like', "$search%")
                        ->orWhere('kredit', 'like', "$search%");
                });
            }

            $cipData = $cipQuery->paginate(10)->appends(request()->query());

        } elseif ($activeTab === 'ams') {
            $amsQuery = AmsData::whereDate('transaction_date', $date);

            if ($search) {
                $amsQuery->where(function ($query) use ($search) {
                    $query->where('reference_number', 'like', "$search%")
                        ->orWhere('bifast_reference_number', 'like', "$search%")
                        ->orWhere('trx_amount', 'like', "$search%")
                        ->orWhere('trx_source', 'like', "$search%")
                        ->orWhere('source_account_number', 'like', "$search%")
                        ->orWhere('destination_account_number', 'like', "$search%")
                        ->orWhere('trx_status', 'like', "%$search%")
                        ->orWhere('debit_status', 'like', "%$search%")
                        ->orWhere('credit_status', 'like', "%$search%");
                });
            }

            $amsData = $amsQuery->paginate(10)->appends(request()->query());

        } elseif ($activeTab === 'bs') {
            $bsQuery = BsData::whereDate('transaction_date', $date);

            if ($search) {
                $bsQuery->where(function ($query) use ($search) {
                    $query->where('retrieval_ref_number', 'like', "%$search%")
                        ->orWhere('nilai_transaksi', 'like', "$search%");
                });
            }

            $bsData = $bsQuery->paginate(10)->appends(request()->query());
        }

        return view('reconciliation.view-data', compact(
            'cipData', 'amsData', 'bsData', 'date', 'activeTab',
            'totalCip', 'totalAms', 'totalBs'
        ));
    }

    public function history()
    {
        $user = Auth::user();

        $uploadHistories = UploadHistory::with('user')
            ->where('uploaded_by', $user->id)
            ->orderBy('created_at', 'desc');

        $reconciliationHistories = ReconciliationHistory::with('user')
            ->where('user_id', $user->id)
            ->orderBy('reconciliation_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('reconciliation.history', compact('uploadHistories', 'reconciliationHistories'));
    }


    public function clearAllData()
    {
        try {
            DB::beginTransaction();
             
            CipData::query()->delete();
            AmsData::query()->delete();
            BsData::query()->delete();
            ReconciliationResult::query()->delete();
            UploadHistory::query()->delete();
            
            DB::commit();
            
            return back()->with('success', 'All reconciliation data has been cleared successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error clearing data: ' . $e->getMessage());
        }
    }

    public function clearDataByDate(Request $request)
    {
        $request->validate([
            'date' => 'required|date'
        ]);
        
        $date = $request->input('date');

        try {
            DB::beginTransaction(); 
            CipData::whereDate('transaction_date', $date)->delete();
            AmsData::whereDate('transaction_date', $date)->delete();
            BsData::whereDate('tgl_transaksi', $date)->delete();
            ReconciliationResult::whereDate('reconciliation_date', $date)->delete(); 

            DB::commit();

            return back()->with('success', "Successfully deleted data for {$date}!");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error','An error occurred while deleting data: ' . $e->getMessage());
        }
    }
 
    public function exportAllAnomalies(Request $request)
    {
        $date = $request->get('date');
        
        if (!$date) {
            return redirect()->back()->with('error', 'Date is required');
        } 
        $reconciliationResult = $this->performReconciliation($date);
        $anomalies = $reconciliationResult['anomalies'];

        if ($anomalies->isEmpty()) {
            return redirect()->back()->with('info', 'Tidak ada anomali untuk diekspor pada tanggal ' . $date);
        }

        $filename = 'Hasil_Reconciliation_' . \Carbon\Carbon::parse($date)->format('d-m-Y') . '.xlsx';
        
        return Excel::download(new AllAnomaliesExport($anomalies, $date), $filename);
    }

    public function exportAllAnomaliesPdf(Request $request)
    {
        $date = $request->get('date');
        
        if (!$date) {
            return redirect()->back()->with('error', 'Tanggal harus diisi');
        } 
        $reconciliationResult = $this->performReconciliation($date);
        $anomalies = $reconciliationResult['anomalies'];

        if ($anomalies->isEmpty()) {
            return redirect()->back()->with('info', 'Tidak ada anomali untuk diekspor pada tanggal ' . $date);
        }

        $pdf = PDF::loadView('exports.anomalies-pdf', compact('anomalies', 'date'));
        $pdf->setPaper('A4', 'landscape');  
        
        $filename = 'Hasil_Reconciliation_' . \Carbon\Carbon::parse($date)->format('d-m-Y') . '.pdf';
        
        return $pdf->download($filename);
    }

    public function viewHistoryDetail($id)
    {
        $history = ReconciliationHistory::findOrFail($id); 
        $anomalies = is_array($history->anomalies_json)
            ? collect($history->anomalies_json)
            : collect(json_decode($history->anomalies_json ?? '[]', true));

        $summary = $history->summary_data ?? [];
        $date = $history->reconciliation_date;

        return view('reconciliation.history-view', compact('anomalies', 'summary', 'date', 'history'));
    }

    public function exportHistoryData(Request $request)
    {
        $historyId = $request->input('history_id');
        $history = ReconciliationHistory::findOrFail($historyId);
        
        $date = $history->reconciliation_date->format('Y-m-d');
        
        try {
            $cipVsAms = $this->reconcileCipVsAms($date);
            $amsVsBs = $this->reconcileAmsVsBs($date);
            $cipVsBs = $this->reconcileBsVsCip($date);
            
            $filename = 'History_Export_' . Carbon::parse($date)->format('d-m-Y') . '.xlsx';
            
            return Excel::download(new SummaryAnomaliesExport($cipVsAms, $amsVsBs, $cipVsBs, $date), $filename);
            
        } catch (\Exception $e) {
            return back()->with('error', 'Error exporting history data: ' . $e->getMessage());
        }
    }

    public function downloadHistoryFile($id, $type)
    {
        $history = ReconciliationHistory::findOrFail($id); 
        
        $filePath = $type === 'excel' ? $history->excel_file_path : $history->pdf_file_path;
        
        if (!$filePath || !Storage::disk('public')->exists($filePath)) {
            abort(404, 'File not found');
        }
        
        $fileName = basename($filePath);
        return Storage::disk('public')->download($filePath, $fileName);
    }

}