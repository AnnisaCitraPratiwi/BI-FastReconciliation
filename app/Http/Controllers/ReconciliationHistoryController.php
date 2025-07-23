<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ReconciliationHistory;

class ReconciliationHistoryController extends Controller
{ 
    public function index(Request $request)
    {
        $adminList = \App\Models\User::where('role', 2)->orderBy('name')->get();

        $q = \App\Models\ReconciliationHistory::with('user')
            ->whereHas('user', function($query) {
                $query->where('role', 2);
            });

        if ($request->admin_id) {
            $q->where('user_id', $request->admin_id);
        }
        if ($request->date) {
            $q->whereDate('reconciliation_date', $request->date);
        }
        if ($request->status) {
            if ($request->status === 'completed') {
                $q->whereNotNull('excel_file_path')->whereNotNull('pdf_file_path');
            } elseif ($request->status === 'failed') {
                $q->where(function($query) {
                    $query->whereNull('excel_file_path')->orWhereNull('pdf_file_path');
                });
            }
        }

        $reconciliationHistories = $q->orderBy('reconciliation_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('master.recon-history', compact('reconciliationHistories', 'adminList'));
    }

    public function destroy($id)
    {
        $history = ReconciliationHistory::findOrFail($id);
 
        if ($history->excel_file_path) {
            \Storage::delete($history->excel_file_path);
        }
        if ($history->pdf_file_path) {
            \Storage::delete($history->pdf_file_path);
        }

        $history->delete();

        return redirect()->back()->with('success', 'Rekonsiliasi berhasil dihapus.');
    }

}
