<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\CipData;
use App\Models\AmsData;
use App\Models\BsData;
use App\Models\User;
use Carbon\Carbon; 
use App\Models\ReconciliationHistory;

class DashboardController extends Controller
{
    public function masterDashboard()
    {
        // Master Dashboard Statistics
        $totalUsers = User::count();
        $adminUsers = User::where('role', 2)->count();
        $pendingApprovals = User::where('is_approved', 0)->count();
        $activeUsers = User::where('is_approved', 1)->count();
         
        $recentAmsUploads = DB::table('upload_histories')
            ->join('users', 'upload_histories.uploaded_by', '=', 'users.id')
            ->select('upload_histories.*', 'users.name as uploader_name', 'users.profile_photo')
            ->where('upload_histories.file_type', 'ams')
            ->orderByDesc('upload_histories.created_at')
            ->limit(8)
            ->get();

        $recentBsUploads = DB::table('upload_histories')
            ->join('users', 'upload_histories.uploaded_by', '=', 'users.id')
            ->select('upload_histories.*', 'users.name as uploader_name', 'users.profile_photo')
            ->where('upload_histories.file_type', 'bs')
            ->orderByDesc('upload_histories.created_at')
            ->limit(8)
            ->get();

        $recentCipUploads = DB::table('upload_histories')
            ->join('users', 'upload_histories.uploaded_by', '=', 'users.id')
            ->select('upload_histories.*', 'users.name as uploader_name', 'users.profile_photo')
            ->where('upload_histories.file_type', 'cip')
            ->orderByDesc('upload_histories.created_at')
            ->limit(8)
            ->get();

        $recentLogins = DB::table('users')
            ->where('users.role', 2)
            ->whereNotNull('users.last_login_at')
            ->orderByDesc('users.last_login_at')
            ->limit(10)
            ->get();


        return view('dashboard.master', compact(
            'totalUsers',
            'adminUsers',
            'pendingApprovals',
            'recentAmsUploads',
            'recentBsUploads',
            'recentCipUploads',
            'recentLogins'
        ));
    }

    public function adminDashboard(Request $request)
    {
        $today = Carbon::today();
        
        $reconSessionsToday = User::where('last_login_at', '>=', now()->subHours(6))->where('role',2)->count();
        $anomaliesFoundToday = DB::table(DB::raw('(
            SELECT MAX(id) as latest_id
            FROM reconciliation_histories
            GROUP BY DATE(reconciliation_date)
            ) as latest'))
            ->join('reconciliation_histories', 'reconciliation_histories.id', '=', 'latest.latest_id')
            ->sum('reconciliation_histories.total_anomalies');
        $totalAmsToday = AmsData::count();
        $totalCipToday = CipData::count();
        $totalBsToday = BsData::count();

        $reconciledRecords = $this->getReconciledRecords($today);
        
        // Ambil bulan dan tahun dari request, default bulan ini
        $selectedMonth = $request->get('month', Carbon::now()->month);
        $selectedYear = $request->get('year', Carbon::now()->year);
        
        // Data untuk grafik anomali berdasarkan bulan yang dipilih
        $dailyAnomalies = $this->getDailyAnomaliesChart($selectedMonth, $selectedYear);
        $recentActivities = $this->getRecentActivities();
        
        // Data untuk dropdown
        $months = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
        ];
        
        $years = range(Carbon::now()->year - 2, Carbon::now()->year);
        
        return view('dashboard.admin', compact(
            'reconSessionsToday',
            'anomaliesFoundToday',
            'totalAmsToday',
            'totalCipToday',
            'totalBsToday',
            'reconciledRecords',
            'dailyAnomalies',
            'recentActivities',
            'months',
            'years',
            'selectedMonth',
            'selectedYear'
        ));
    }

    private function getDailyAnomaliesChart($month, $year)
    {
        $subquery = DB::table('reconciliation_histories')
            ->select(DB::raw('MAX(id) as latest_id'))
            ->whereYear('reconciliation_date', $year)
            ->whereMonth('reconciliation_date', $month)
            ->groupBy(DB::raw('DATE(reconciliation_date)'));

        $historiesData = DB::table('reconciliation_histories')
            ->joinSub($subquery, 'latest', function ($join) {
                $join->on('reconciliation_histories.id', '=', 'latest.latest_id');
            })
            ->orderBy('reconciliation_date')
            ->get()
            ->keyBy(function($item) {
                return \Carbon\Carbon::parse($item->reconciliation_date)->format('Y-m-d');
            });

        $anomaliesData = [];
        $daysInMonth = \Carbon\Carbon::createFromDate($year, $month, 1)->daysInMonth;

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = \Carbon\Carbon::createFromDate($year, $month, $day);
            $dateKey = $date->format('Y-m-d');

            $anomalies = isset($historiesData[$dateKey])
                ? $historiesData[$dateKey]->total_anomalies
                : 0;

            $anomaliesData[] = [
                'date' => $date->format('d'),
                'full_date' => $date->format('d M'),
                'anomalies' => $anomalies,
            ];
        }

        return $anomaliesData;
    }


    private function getReconciledRecords($date)
    {
        $cipCount = CipData::whereDate('transaction_date', $date)->count();
        $amsCount = AmsData::whereDate('transaction_date', $date)->count();
        $bsCount = BsData::whereDate('tgl_transaksi', $date)->count();
        
        // Estimasi data yang berhasil direkonsiliasi (yang match di ketiga sistem)
        return min($cipCount, $amsCount, $bsCount);
    }
    
    private function getReconciliationSessionsToday($date)
    {
        $reconSessionsToday = User::where('last_login_at', '>=', now()->subHours(1))
        ->where('role',2)
        ->count();

        
        // Jika ada upload dari ketiga sistem = 1 sesi
        return ($cipCount > 0 && $amsCount > 0 && $bsCount > 0) ? 1 : 0;
    }
    
    private function getLastReconciliationStatus()
    {
        // Ambil 1 record terakhir saja
        $latestCip = CipData::latest('created_at')->first();
        $latestAms = AmsData::latest('created_at')->first();
        $latestBs = BsData::latest('created_at')->first();
        
        if (!$latestCip || !$latestAms || !$latestBs) {
            return [
                'status' => 'incomplete',
                'message' => 'Incomplete data upload',
                'color' => 'warning'
            ];
        }
        
        return [
            'status' => 'success',
            'message' => 'Last upload: ' . $latestCip->created_at->format('d M Y H:i'),
            'color' => 'success'
        ];
    }
    
    
    
    private function getRecentActivities()
    {
        // Ambil 8 aktivitas terakhir saja (tanpa merge berat)
        $activities = collect();
        
        // CIP uploads (limit 3)
        $cipUploads = CipData::select('created_at', DB::raw("'CIP Data Upload' as activity"))
            ->latest('created_at')
            ->limit(3)
            ->get();
            
        // AMS uploads (limit 3)
        $amsUploads = AmsData::select('created_at', DB::raw("'AMS Data Upload' as activity"))
            ->latest('created_at')
            ->limit(3)
            ->get();
            
        // BS uploads (limit 3)
        $bsUploads = BsData::select('created_at', DB::raw("'BS Data Upload' as activity"))
            ->latest('created_at')
            ->limit(3)
            ->get();
        
        // Merge sederhana
        $activities = $activities->merge($cipUploads)
                                ->merge($amsUploads)
                                ->merge($bsUploads)
                                ->take(8);
        
        return $activities;
    }

    // Legacy method
    public function reconciliation()
    {
        return view('reconciliation.index');
    }
}
