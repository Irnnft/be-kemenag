<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LaporanBulanan;
use App\Models\DataSiswa;
use App\Models\DataGuru;
use App\Models\DataSarpras;
use App\Models\DataMobiler;
use App\Models\DataKeuangan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class LaporanController extends Controller
{
    // === OPERATOR SEKOLAH ===

    // Get List of Laporan (Dashboard)
    public function index(Request $request)
    {
        $user = $request->user();
        $query = LaporanBulanan::where('id_madrasah', $user->id_madrasah);

        if ($request->has('year')) {
            $query->whereYear('bulan_tahun', $request->year);
        }

        return response()->json($query->orderBy('bulan_tahun', 'desc')->get());
    }

    // Create Draft for a Month
    public function store(Request $request)
    {
        $request->validate([
            'bulan_tahun' => 'required|date', // YYYY-MM-DD (e.g., 2025-01-01)
        ]);

        $user = $request->user();

        // Check if exists
        $exists = LaporanBulanan::where('id_madrasah', $user->id_madrasah)
            ->whereYear('bulan_tahun', date('Y', strtotime($request->bulan_tahun)))
            ->whereMonth('bulan_tahun', date('m', strtotime($request->bulan_tahun)))
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Laporan bulan ini sudah ada'], 400);
        }

        return DB::transaction(function () use ($user, $request) {
            $laporan = LaporanBulanan::create([
                'id_madrasah' => $user->id_madrasah,
                'bulan_tahun' => $request->bulan_tahun,
                'status_laporan' => 'draft'
            ]);

            // Seed Default Bagian A: Siswa
            $defaultSiswa = ['Kel A', 'Kel B'];
            foreach ($defaultSiswa as $kelas) {
                $laporan->siswa()->create(['kelas' => $kelas]);
            }

            // Seed Default Bagian B: Rekapitulasi Personal (Common Categories)
            $defaultRekap = [
                'Guru Tetap/PNS', 'Guru PNS Dinas', 'Guru Honor Tk II/Tk I', 'Guru Honor Madrasah',
                'Sertifikasi Guru PNS', 'Sertifikasi Guru Non PNS', 'Pegawai TU PNS', 'Pegawai TU Honorer',
                'Petugas Pustaka', 'Petugas UKS', 'Satpam', 'Petugas Kebersihan', 'Petugas Madrasah'
            ];
            foreach ($defaultRekap as $kat) {
                $laporan->rekapPersonal()->create([
                    'keadaan' => $kat
                ]);
            }

            // Bagian F: Guru/TU (Header list) - Optional based on whether schools want to fill from scratch or keep old ones
            // Usually, list of persons starts empty or copied from last month.

            // Seed Default Bagian C: Sarpras
            $defaultSarpras = [
                'Luas Tanah yg terbangun', 'Luas tanah Pekarangan', 'Total Luas Tanah Seluruh nya', 'Status Tanah',
                'Jumlah Lokal Belajar', 'Ruang Kantor TU', 'Ruang kepala Madrasah', 'Ruang Tamu', 
                'Ruang Majelis Guru', 'Ruang Perpustakaan', 'WC Guru', 'WC Siswa', 'Mushalla', 'Gudang'
            ];
            foreach ($defaultSarpras as $aset) {
                $laporan->sarpras()->create(['jenis_aset' => $aset]);
            }

            // Seed Default Bagian Mobiler
            $defaultMobiler = ['Almari Guru', 'Meja Guru', 'Kursi Guru', 'Meja Siswa', 'Kursi Siswa', 'Almari Siswa'];
            foreach ($defaultMobiler as $item) {
                $laporan->mobiler()->create(['nama_barang' => $item]);
            }

            // Seed Default Bagian D: Keuangan
            $defaultKeuangan = [
                'Jam Wajib PNS/Sertifikasi', 'NON PNS dan Non sertifikasi', 'Kepala', 'Waka Kur & Kesis',
                'Operator', 'Transport Rapat Anggota', 'Kegiatan PMT', 'Biaya tak di duga'
            ];
            foreach ($defaultKeuangan as $keu) {
                $laporan->keuangan()->create(['uraian_kegiatan' => $keu]);
            }

            return response()->json($laporan->load(['siswa', 'rekapPersonal', 'guru', 'sarpras', 'mobiler', 'keuangan']), 201);
        });
    }

    // Get Detail Laporan (Full Data)
    public function show($id)
    {
        $laporan = LaporanBulanan::with(['siswa', 'rekapPersonal', 'guru', 'sarpras', 'mobiler', 'keuangan', 'madrasah'])
            ->findOrFail($id);

        $this->authorizeAccess($laporan);

        return response()->json($laporan);
    }

    // Update Section A: Siswa
    public function updateSiswa(Request $request, $id)
    {
        $laporan = LaporanBulanan::findOrFail($id);
        $this->authorizeEdit($laporan);

        $data = $request->input('data'); 
        
        DB::transaction(function () use ($laporan, $data) {
            $laporan->siswa()->delete();
            foreach ($data as $row) {
                $laporan->siswa()->create($row);
            }
        });

        return response()->json(['message' => 'Data Siswa updated']);
    }

    // Update Section B: Rekap Personal
    public function updateRekapPersonal(Request $request, $id)
    {
        $laporan = LaporanBulanan::findOrFail($id);
        $this->authorizeEdit($laporan);
        
        $data = $request->input('data');
        DB::transaction(function () use ($laporan, $data) {
            $laporan->rekapPersonal()->delete();
            foreach ($data as $row) {
                $laporan->rekapPersonal()->create($row);
            }
        });

        return response()->json(['message' => 'Rekap Personal updated']);
    }

    // Update Section F: Guru/TU List
    public function updateGuru(Request $request, $id)
    {
        $laporan = LaporanBulanan::findOrFail($id);
        $this->authorizeEdit($laporan);
        
        $data = $request->input('data');
        DB::transaction(function () use ($laporan, $data) {
            $laporan->guru()->delete();
            foreach ($data as $row) {
                $laporan->guru()->create($row);
            }
        });

        return response()->json(['message' => 'Data Guru updated']);
    }

    // Update Section C: Sarpras
    public function updateSarpras(Request $request, $id)
    {
        $laporan = LaporanBulanan::findOrFail($id);
        $this->authorizeEdit($laporan);
        
        $data = $request->input('data');
        DB::transaction(function () use ($laporan, $data) {
            $laporan->sarpras()->delete();
            foreach ($data as $row) {
                $laporan->sarpras()->create($row);
            }
        });

        return response()->json(['message' => 'Data Sarpras updated']);
    }

    // Update Mobiler
    public function updateMobiler(Request $request, $id)
    {
        $laporan = LaporanBulanan::findOrFail($id);
        $this->authorizeEdit($laporan);
        
        $data = $request->input('data');
        DB::transaction(function () use ($laporan, $data) {
            $laporan->mobiler()->delete();
            foreach ($data as $row) {
                $laporan->mobiler()->create($row);
            }
        });

        return response()->json(['message' => 'Data Mobiler updated']);
    }

    // Update Keuangan
    public function updateKeuangan(Request $request, $id)
    {
        $laporan = LaporanBulanan::findOrFail($id);
        $this->authorizeEdit($laporan);
        
        $data = $request->input('data');
        DB::transaction(function () use ($laporan, $data) {
            $laporan->keuangan()->delete();
            foreach ($data as $row) {
                $laporan->keuangan()->create($row);
            }
        });

        return response()->json(['message' => 'Data Keuangan updated']);
    }

    // Submit Laporan
    public function submit(Request $request, $id)
    {
        $laporan = LaporanBulanan::findOrFail($id);
        $this->authorizeEdit($laporan);

        $laporan->update([
            'status_laporan' => 'submitted',
            'submitted_at' => now()
        ]);

        // Optional: Send Notification Logic Here

        return response()->json(['message' => 'Laporan berhasil disubmit. Menunggu verifikasi Kasi Penmad.']);
    }

    // Helper: Verify Ownership & Status
    private function authorizeAccess($laporan)
    {
        $user = Auth::user();
        if ($user->role === 'kasi_penmad') return true;
        if ($user->id_madrasah !== $laporan->id_madrasah) {
            abort(403, 'Unauthorized');
        }
    }

    private function authorizeEdit($laporan)
    {
        $this->authorizeAccess($laporan);
        if ($laporan->status_laporan !== 'draft' && $laporan->status_laporan !== 'revisi') {
            abort(403, 'Laporan sudah disubmit atau diverifikasi, tidak bisa diedit.');
        }
    }
}
