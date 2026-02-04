<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabel Users
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username', 50)->unique();
            $table->string('password');
            $table->enum('role', ['operator_sekolah', 'kasi_penmad']);
            $table->integer('id_madrasah')->nullable(); // Foreign Key constraint added later intentionally or handled by logic
            $table->timestamps();
        });

        // 2. Tabel Madrasah
        Schema::create('madrasah', function (Blueprint $table) {
            $table->id('id_madrasah');
            $table->string('npsn', 20)->unique();
            $table->string('nama_madrasah');
            $table->text('alamat')->nullable();
            $table->boolean('status_aktif')->default(true);
            $table->timestamps();
        });

        // 3. Tabel Laporan Bulanan
        Schema::create('laporan_bulanan', function (Blueprint $table) {
            $table->id('id_laporan');
            $table->unsignedBigInteger('id_madrasah');
            $table->date('bulan_tahun');
            $table->enum('status_laporan', ['draft', 'submitted', 'verified', 'revisi'])->default('draft');
            $table->text('catatan_revisi')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->foreign('id_madrasah')->references('id_madrasah')->on('madrasah')->onDelete('cascade');
        });

        // 4. Tabel Data Siswa (Section A)
        Schema::create('data_siswa', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_laporan');
            $table->string('kelas', 50);
            $table->integer('jumlah_rombel')->default(0);
            $table->integer('jumlah_lk')->default(0);
            $table->integer('jumlah_pr')->default(0);
            $table->integer('mutasi_masuk')->default(0);
            $table->integer('mutasi_keluar')->default(0);
            $table->string('keterangan')->nullable();
            $table->timestamps();

            $table->foreign('id_laporan')->references('id_laporan')->on('laporan_bulanan')->onDelete('cascade');
        });

        // 5. Tabel Rekapitulasi Personal (Section B)
        Schema::create('data_rekap_personal', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_laporan');
            $table->string('keadaan'); // e.g., Guru Tetap/PNS
            $table->integer('jumlah_lk')->default(0);
            $table->integer('jumlah_pr')->default(0);
            $table->integer('mutasi_masuk')->default(0);
            $table->integer('mutasi_keluar')->default(0);
            $table->string('keterangan')->nullable();
            $table->timestamps();

            $table->foreign('id_laporan')->references('id_laporan')->on('laporan_bulanan')->onDelete('cascade');
        });

        // 6. Tabel Data Guru & Tenaga Administrasi (Section F)
        Schema::create('data_guru', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_laporan');
            $table->string('nama_guru');
            $table->string('nip_nik', 50)->nullable();
            $table->enum('lp', ['L', 'P'])->nullable();
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('status_pegawai')->nullable();
            $table->string('pendidikan_terakhir')->nullable();
            $table->string('jurusan')->nullable();
            $table->string('golongan', 10)->nullable();
            $table->string('tmt_mengajar')->nullable();
            $table->string('tmt_di_madrasah')->nullable();
            $table->string('mata_pelajaran')->nullable();
            $table->string('satminkal')->nullable();
            $table->integer('jumlah_jam')->default(0);
            $table->string('jabatan', 100);
            $table->string('nama_ibu_kandung')->nullable();
            $table->boolean('sertifikasi')->default(false);
            $table->enum('mutasi_status', ['aktif', 'masuk', 'keluar'])->default('aktif');
            $table->timestamps();

            $table->foreign('id_laporan')->references('id_laporan')->on('laporan_bulanan')->onDelete('cascade');
        });

        // 7. Tabel Sarpras (Tanah & Bangunan - Section C)
        Schema::create('data_sarpras', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_laporan');
            $table->string('jenis_aset', 100);
            $table->string('luas')->nullable(); // format strings like "136 m2" if needed, or decimal
            $table->integer('kondisi_baik')->default(0);
            $table->integer('kondisi_rusak_ringan')->default(0);
            $table->integer('kondisi_rusak_berat')->default(0);
            $table->integer('kekurangan')->default(0);
            $table->integer('perlu_rehab')->default(0);
            $table->string('keterangan')->nullable();
            $table->timestamps();

            $table->foreign('id_laporan')->references('id_laporan')->on('laporan_bulanan')->onDelete('cascade');
        });

        // 8. Tabel Mobiler (Section C - Bawah)
        Schema::create('data_mobiler', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_laporan');
            $table->string('nama_barang', 100);
            $table->integer('jumlah_total')->default(0);
            $table->integer('kondisi_baik')->default(0);
            $table->integer('kondisi_rusak_ringan')->default(0);
            $table->integer('kondisi_rusak_berat')->default(0);
            $table->integer('kekurangan')->default(0);
            $table->string('keterangan')->nullable();
            $table->timestamps();

            $table->foreign('id_laporan')->references('id_laporan')->on('laporan_bulanan')->onDelete('cascade');
        });

        // 8. Tabel Keuangan
        Schema::create('data_keuangan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_laporan');
            $table->string('uraian_kegiatan');
            $table->integer('volume')->default(0);
            $table->string('satuan', 50)->nullable();
            $table->decimal('harga_satuan', 15, 2)->default(0);
            // created_at timestamps
            $table->timestamps();
            
            // Note: Virtual column 'total_harga' support depends on exact driver version, usually calculated in query or app
            $table->foreign('id_laporan')->references('id_laporan')->on('laporan_bulanan')->onDelete('cascade');
        });

        // 9. Pengumuman
        Schema::create('pengumuman', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->text('isi_info');
            $table->unsignedBigInteger('created_by'); // Reference to users.id
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengumuman');
        Schema::dropIfExists('data_keuangan');
        Schema::dropIfExists('data_mobiler');
        Schema::dropIfExists('data_sarpras');
        Schema::dropIfExists('data_guru');
        Schema::dropIfExists('data_siswa');
        Schema::dropIfExists('laporan_bulanan');
        Schema::dropIfExists('madrasah');
        Schema::dropIfExists('users');
    }
};
