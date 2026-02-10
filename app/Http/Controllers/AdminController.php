<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LaporanBulanan;
use Illuminate\Support\Facades\DB;

use App\Models\Madrasah;

class AdminController extends Controller
{
    // === KASI PENMAD ===

    // Monitoring Dashboard
    public function dashboard(Request $request) 
    {
        // Summary Stats
        $stats = [
            'total_madrasah' => Madrasah::count(),
            'total_submitted' => LaporanBulanan::where('status_laporan', 'submitted')->count(),
            'total_verified' => LaporanBulanan::where('status_laporan', 'verified')->count(),
            'total_revisi' => LaporanBulanan::where('status_laporan', 'revisi')->count(),
        ];

        return response()->json($stats);
    }

    // List Validasi Laporan
    public function index(Request $request)
    {
        $query = LaporanBulanan::with('madrasah');

        if ($request->has('status')) {
            $query->where('status_laporan', $request->status);
        }

        if ($request->has('bulan')) {
            $query->whereMonth('bulan_tahun', date('m', strtotime($request->bulan)))
                  ->whereYear('bulan_tahun', date('Y', strtotime($request->bulan)));
        }

        return response()->json($query->orderBy('updated_at', 'desc')->get());
    }

    // Validasi Action (Terima / Revisi)
    public function verify(Request $request, $id)
    {
        $request->validate([
            'status_laporan' => 'required|in:verified,revisi',
            'catatan_revisi' => 'required_if:status_laporan,revisi'
        ]);

        $laporan = LaporanBulanan::findOrFail($id);
        
        $laporan->update([
            'status_laporan' => $request->status_laporan,
            'catatan_revisi' => $request->catatan_revisi
        ]);

        return response()->json(['message' => 'Status laporan diperbarui', 'data' => $laporan]);
    }

    // Rekapitulasi Data (For Excel Export)
    public function recap(Request $request)
    {
        // Example: Get total siswa per madrasah for a specific month
        $bulan = $request->input('bulan', date('Y-m-d')); // Month Needed

        $data = LaporanBulanan::whereYear('bulan_tahun', date('Y', strtotime($bulan)))
            ->whereMonth('bulan_tahun', date('m', strtotime($bulan)))
            ->where('status_laporan', 'verified')
            ->with(['madrasah', 'siswa', 'guru']) // Eager load
            ->get()
            ->map(function($lap) {
                return [
                    'nama_madrasah' => $lap->madrasah->nama_madrasah,
                    'total_siswa' => $lap->siswa->sum('jumlah_lk') + $lap->siswa->sum('jumlah_pr'),
                    'total_guru' => $lap->guru->count(),
                    // Add more complex aggregations here
                ];
            });

        return response()->json($data);
    }
}
