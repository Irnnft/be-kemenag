<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Madrasah;
use App\Models\LaporanBulanan;
use App\Models\DataSiswa;
use App\Models\DataRekapPersonal;
use App\Models\DataGuru;
use App\Models\DataSarpras;
use App\Models\DataMobiler;
use App\Models\DataKeuangan;
use App\Models\Pengumuman;
use Carbon\Carbon;

class DummyDataSeeder extends Seeder
{
    public function run()
    {
        // 1. Create Kasi Penmad (Admin)
        $admin = User::firstOrCreate(
            ['username' => 'admin'],
            [
                'password' => Hash::make('password'),
                'role' => 'kasi_penmad',
                'id_madrasah' => null,
            ]
        );

        // 2. Create Pengumuman
        Pengumuman::create([
            'judul' => 'Jadwal Pelaporan Bulan Februari',
            'isi_info' => 'Mohon Bapak/Ibu Operator segera mengupload laporan bulan Februari sebelum tanggal 10. Terima kasih.',
            'created_by' => $admin->id
        ]);

        // 3. Create Madrasahs & Operators
        $madrasahs = [
            [
                'npsn' => '10101010',
                'nama' => 'MI NURUL HUDA',
                'alamat' => 'Jl. Mawar No. 10, Pekanbaru',
                'user' => 'op_mi'
            ],
            [
                'npsn' => '20202020',
                'nama' => 'MTS AL-ITTIHAD',
                'alamat' => 'Jl. Melati No. 45, Kampar',
                'user' => 'op_mts'
            ],
            [
                'npsn' => '30303030',
                'nama' => 'MA DARUSSALAM',
                'alamat' => 'Jl. Anggrek No. 88, Riau',
                'user' => 'op_ma'
            ]
        ];

        foreach ($madrasahs as $m) {
            $madrasah = Madrasah::create([
                'npsn' => $m['npsn'],
                'nama_madrasah' => $m['nama'],
                'alamat' => $m['alamat'],
                'status_aktif' => true
            ]);

            User::create([
                'username' => $m['user'],
                'password' => Hash::make('password'),
                'role' => 'operator_sekolah',
                'id_madrasah' => $madrasah->id_madrasah
            ]);

            // 4. Create Laporan for each Madrasah
            $this->createHistoricalReports($madrasah);
        }
    }

    private function createHistoricalReports($madrasah)
    {
        // Report 1: January (Verified)
        $jan = LaporanBulanan::create([
            'id_madrasah' => $madrasah->id_madrasah,
            'bulan_tahun' => Carbon::now()->subMonth(2)->startOfMonth()->format('Y-m-d'),
            'status_laporan' => 'verified',
            'submitted_at' => Carbon::now()->subMonth(2)->endOfMonth(),
        ]);
        $this->seedReportDetails($jan);

        // Report 2: Last Month (Revisi)
        $feb = LaporanBulanan::create([
            'id_madrasah' => $madrasah->id_madrasah,
            'bulan_tahun' => Carbon::now()->subMonth(1)->startOfMonth()->format('Y-m-d'),
            'status_laporan' => 'revisi',
            'catatan_revisi' => 'Mohon lengkapi data sarpras bagian tanah.',
            'submitted_at' => Carbon::now()->subMonth(1)->endOfMonth(),
        ]);
        $this->seedReportDetails($feb);

        // Report 3: This Month (Draft)
        $mar = LaporanBulanan::create([
            'id_madrasah' => $madrasah->id_madrasah,
            'bulan_tahun' => Carbon::now()->startOfMonth()->format('Y-m-d'),
            'status_laporan' => 'draft',
            'submitted_at' => null,
        ]);
        $this->seedReportDetails($mar);
    }

    private function seedReportDetails($laporan)
    {
        // A. Data Siswa
        $kelas_list = ['Kel A', 'Kel B'];
        foreach ($kelas_list as $k) {
            DataSiswa::create([
                'id_laporan' => $laporan->id_laporan,
                'kelas' => $k,
                'jumlah_rombel' => 1,
                'jumlah_lk' => rand(10, 20),
                'jumlah_pr' => rand(10, 20),
                'mutasi_masuk' => rand(0, 2),
                'mutasi_keluar' => rand(0, 1),
            ]);
        }

        // B. Rekap Personal
        $categories = ['Guru Tetap/PNS', 'Guru PNS Dinas', 'Guru Honor Madrasah', 'Satpam'];
        foreach ($categories as $cat) {
            DataRekapPersonal::create([
                'id_laporan' => $laporan->id_laporan,
                'keadaan' => $cat,
                'jumlah_lk' => rand(1, 5),
                'jumlah_pr' => rand(1, 5),
            ]);
        }

        // F. Data Guru (Individual)
        $guru_names = ['Budi Santoso', 'Siti Aminah'];
        foreach ($guru_names as $name) {
            DataGuru::create([
                'id_laporan' => $laporan->id_laporan,
                'nama_guru' => $name,
                'nip_nik' => rand(10000000, 99999999),
                'lp' => rand(0, 1) ? 'L' : 'P',
                'jabatan' => 'Guru Kelas',
                'mutasi_status' => 'aktif'
            ]);
        }
        
        // C. Sarpras
        $aset = ['Ruang Kelas', 'Ruang Guru', 'WC Guru', 'WC Siswa'];
        foreach ($aset as $a) {
            DataSarpras::create([
                'id_laporan' => $laporan->id_laporan,
                'jenis_aset' => $a,
                'luas' => rand(20, 100) . ' m2',
                'kondisi_baik' => 1,
                'kondisi_rusak_ringan' => 0,
                'kondisi_rusak_berat' => 0
            ]);
        }

        // Mobiler
        $mobiler = ['Meja Siswa', 'Kursi Siswa'];
        foreach ($mobiler as $m) {
            DataMobiler::create([
                'id_laporan' => $laporan->id_laporan,
                'nama_barang' => $m,
                'jumlah_total' => rand(20, 50),
                'kondisi_baik' => rand(15, 40),
                'kondisi_rusak_ringan' => 0,
                'kondisi_rusak_berat' => 0
            ]);
        }

        // D. Keuangan
        $kegiatan = ['Pembelian ATK', 'Honor Guru Honorer'];
        foreach ($kegiatan as $k) {
            DataKeuangan::create([
                'id_laporan' => $laporan->id_laporan,
                'uraian_kegiatan' => $k,
                'volume' => 1,
                'satuan' => 'Paket',
                'harga_satuan' => rand(500000, 2000000)
            ]);
        }
    }
}
