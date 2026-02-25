# 📬 Tutorial Postman - Sistem Laporan Kemenag

## Persiapan Awal

Sebelum mulai, pastikan hal berikut:
1. Server Laravel berjalan dengan perintah `php artisan serve`
2. Postman sudah terbuka
3. Database sudah terisi data (jalankan `php artisan db:seed --class=DummyDataSeeder`)

---

## ⚙️ Konfigurasi Global Postman

Buat **Environment** baru di Postman dengan variabel berikut:

| Variable     | Initial Value              |
|:-------------|:---------------------------|
| `base_url`   | `http://127.0.0.1:8000/api` |
| `token`      | *(kosongkan dulu)*          |

**Cara membuat Environment:**
1. Klik ikon **Environments** (mata) di sidebar kiri Postman.
2. Klik **+** (Create Environment).
3. Beri nama: `Kemenag Local`.
4. Tambahkan variabel di atas, lalu klik **Save**.
5. Aktifkan environment ini melalui dropdown kanan atas Postman.

**Header Wajib** untuk semua request:
```
Accept: application/json
Content-Type: application/json
```

---

## 🔑 GRUP 1: Autentikasi (Public, Tanpa Token)

### 1. POST /login
Login ke sistem dan mendapatkan token.

- **Method:** `POST`
- **URL:** `{{base_url}}/login`
- **Tab Headers:**
  - `Accept`: `application/json`
- **Tab Body** (raw → JSON):

**Login sebagai Admin (Kasi Penmad):**
```json
{
    "username": "admin",
    "password": "password"
}
```

**Login sebagai Staff Penmad:**
```json
{
    "username": "staff",
    "password": "password"
}
```

**Login sebagai Operator Sekolah:**
```json
{
    "username": "operator",
    "password": "password"
}
```

**Respon Sukses (200):**
```json
{
    "message": "Login berhasil",
    "token": "1|xxxxxxxxxxxxxxxx",
    "user": {
        "id": 1,
        "username": "admin",
        "role": "kasi_penmad"
    }
}
```

> 💡 **Salin nilai `token`** dari respon ini. Dibutuhkan untuk semua request berikutnya.

**Cara Simpan Token Otomatis:**
Di tab **Tests** pada request Login ini, tambahkan kode:
```javascript
var jsonData = pm.response.json();
pm.environment.set("token", jsonData.token);
```
Token akan tersimpan otomatis ke variabel `{{token}}`.

---

## 🔒 Semua Request di Bawah Ini Membutuhkan Token

Untuk setiap request berikutnya, tambahkan di tab **Authorization**:
- **Type:** `Bearer Token`
- **Token:** `{{token}}`

---

## 👤 GRUP 2: Profil & Sesi

### 2. GET /me
Melihat data profil user yang sedang login.

- **Method:** `GET`
- **URL:** `{{base_url}}/me`

**Respon Sukses (200):**
```json
{
    "id": 1,
    "username": "admin",
    "role": "kasi_penmad",
    "id_madrasah": null,
    "madrasah": null
}
```

---

### 3. POST /logout
Keluar dari sistem (menghapus token).

- **Method:** `POST`
- **URL:** `{{base_url}}/logout`

**Respon Sukses (200):**
```json
{
    "message": "Logout berhasil"
}
```

---

### 4. GET /pengumuman
Melihat daftar pengumuman untuk semua user.

- **Method:** `GET`
- **URL:** `{{base_url}}/pengumuman`

**Respon Sukses (200):**
```json.
[
    {
        "id": 1,
        "judul": "Jadwal Pelaporan Bulan Februari",
        "isi_info": "Mohon Bapak/Ibu Operator segera mengupload laporan...",
        "created_at": "2025-02-01"
    }
]
```

---

## 🏫 GRUP 3: Operator Sekolah
> ⚠️ **Pastikan login menggunakan akun `operator` (role: operator_sekolah)**

### 5. GET /operator/dashboard
Melihat daftar semua laporan milik sekolah sendiri.

- **Method:** `GET`
- **URL:** `{{base_url}}/operator/dashboard`

**Filter opsional (tambahkan di Tab Params):**
| Key     | Value   | Keterangan               |
|:--------|:--------|:-------------------------|
| `year`  | `2025`  | Filter berdasarkan tahun |
| `trashed` | `1` | Melihat laporan di tempat sampah |

---

### 6. POST /laporan
Membuat draft laporan baru untuk bulan tertentu.

- **Method:** `POST`
- **URL:** `{{base_url}}/laporan`
- **Tab Body** (raw → JSON):
```json
{
    "bulan_tahun": "2025-04-01"
}
```
> Format tanggal adalah `YYYY-MM-01` (selalu hari pertama di bulan tersebut).

**Respon Sukses (201):**
```json
{
    "id_laporan": 4,
    "id_madrasah": 1,
    "bulan_tahun": "2025-04-01",
    "status_laporan": "draft",
    "siswa": [...],
    "sarpras": [...],
    "keuangan": [...]
}
```

---

### 7. GET /laporan/{id}
Melihat detail lengkap satu laporan (semua data dari Bagian A sampai F).

- **Method:** `GET`
- **URL:** `{{base_url}}/laporan/1`

> Ganti angka `1` dengan `id_laporan` yang ingin dilihat.

---

### 8. PUT /laporan/{id}/siswa
Mengupdate data jumlah siswa pada laporan.

- **Method:** `PUT`
- **URL:** `{{base_url}}/laporan/1/siswa`
- **Tab Body** (raw → JSON):
```json
{
    "data": [
        {
            "kelas": "Kel A",
            "jumlah_rombel": 1,
            "jumlah_lk": 15,
            "jumlah_pr": 12,
            "mutasi_masuk": 1,
            "mutasi_keluar": 0
        },
        {
            "kelas": "Kel B",
            "jumlah_rombel": 1,
            "jumlah_lk": 10,
            "jumlah_pr": 14,
            "mutasi_masuk": 0,
            "mutasi_keluar": 1
        }
    ]
}
```

---

### 9. PUT /laporan/{id}/rekap-personal
Mengupdate data rekapitulasi personal (guru & pegawai).

- **Method:** `PUT`
- **URL:** `{{base_url}}/laporan/1/rekap-personal`
- **Tab Body** (raw → JSON):
```json
{
    "data": [
        {
            "keadaan": "Guru Tetap/PNS",
            "jumlah_lk": 3,
            "jumlah_pr": 5
        },
        {
            "keadaan": "Guru Honor Madrasah",
            "jumlah_lk": 2,
            "jumlah_pr": 4
        },
        {
            "keadaan": "Pegawai TU Honorer",
            "jumlah_lk": 1,
            "jumlah_pr": 1
        }
    ]
}
```

---

### 10. PUT /laporan/{id}/guru
Mengupdate daftar nama guru dan pegawai pada laporan.

- **Method:** `PUT`
- **URL:** `{{base_url}}/laporan/1/guru`
- **Tab Body** (raw → JSON):
```json
{
    "data": [
        {
            "nama_guru": "Budi Santoso",
            "nip_nik": "19800101200501001",
            "lp": "L",
            "jabatan": "Kepala Madrasah",
            "mutasi_status": "aktif"
        },
        {
            "nama_guru": "Siti Aminah",
            "nip_nik": "19850505201001002",
            "lp": "P",
            "jabatan": "Guru Kelas",
            "mutasi_status": "aktif"
        }
    ]
}
```

---

### 11. PUT /laporan/{id}/sarpras
Mengupdate data sarana dan prasarana.

- **Method:** `PUT`
- **URL:** `{{base_url}}/laporan/1/sarpras`
- **Tab Body** (raw → JSON):
```json
{
    "data": [
        {
            "jenis_aset": "Jumlah Lokal Belajar",
            "luas": "200 m2",
            "kondisi_baik": 4,
            "kondisi_rusak_ringan": 1,
            "kondisi_rusak_berat": 0
        },
        {
            "jenis_aset": "WC Siswa",
            "luas": "15 m2",
            "kondisi_baik": 2,
            "kondisi_rusak_ringan": 0,
            "kondisi_rusak_berat": 1
        }
    ]
}
```

---

### 12. PUT /laporan/{id}/mobiler
Mengupdate data perabot (meja, kursi, almari).

- **Method:** `PUT`
- **URL:** `{{base_url}}/laporan/1/mobiler`
- **Tab Body** (raw → JSON):
```json
{
    "data": [
        {
            "nama_barang": "Meja Siswa",
            "jumlah_total": 120,
            "kondisi_baik": 100,
            "kondisi_rusak_ringan": 15,
            "kondisi_rusak_berat": 5
        },
        {
            "nama_barang": "Kursi Guru",
            "jumlah_total": 15,
            "kondisi_baik": 15,
            "kondisi_rusak_ringan": 0,
            "kondisi_rusak_berat": 0
        }
    ]
}
```

---

### 13. PUT /laporan/{id}/keuangan
Mengupdate data keuangan operasional.

- **Method:** `PUT`
- **URL:** `{{base_url}}/laporan/1/keuangan`
- **Tab Body** (raw → JSON):
```json
{
    "data": [
        {
            "uraian_kegiatan": "Jam Wajib PNS/Sertifikasi",
            "volume": 1,
            "satuan": "Bulan",
            "harga_satuan": 2500000
        },
        {
            "uraian_kegiatan": "Pembelian ATK",
            "volume": 5,
            "satuan": "Rim",
            "harga_satuan": 45000
        }
    ]
}
```

---

### 14. POST /laporan/{id}/submit
Mengirim laporan ke Admin untuk diverifikasi. Status akan berubah menjadi `submitted`.

- **Method:** `POST`
- **URL:** `{{base_url}}/laporan/1/submit`
- **Body:** Kosong (tidak perlu)

> ⚠️ Setelah disubmit, laporan **tidak bisa diedit** lagi.

---

### 15. DELETE /laporan/{id}
Memindahkan laporan ke tempat sampah (Soft Delete). Hanya untuk laporan berstatus `draft` atau `revisi`.

- **Method:** `DELETE`
- **URL:** `{{base_url}}/laporan/1`

---

### 16. POST /laporan/{id}/restore
Mengembalikan laporan dari tempat sampah.

- **Method:** `POST`
- **URL:** `{{base_url}}/laporan/1/restore`

---

### 17. DELETE /laporan/{id}/permanent
Menghapus laporan secara permanen dari sisi Operator.

- **Method:** `DELETE`
- **URL:** `{{base_url}}/laporan/1/permanent`

> ⚠️ Laporan harus berada di **tempat sampah** terlebih dahulu.

---

### 18. GET /operator/madrasah
Melihat data profil madrasah sendiri.

- **Method:** `GET`
- **URL:** `{{base_url}}/operator/madrasah`

---

### 19. PUT /operator/madrasah
Mengupdate data profil madrasah sendiri.

- **Method:** `PUT`
- **URL:** `{{base_url}}/operator/madrasah`
- **Tab Body** (raw → JSON):
```json
{
    "nama_madrasah": "MI NURUL HUDA UPDATE",
    "alamat": "Jl. Melati No. 5, Pekanbaru",
    "kecamatan": "TAMPAN"
}
```

---

## 🛡️ GRUP 4: Admin (Kasi & Staff Penmad)
> ⚠️ **Pastikan login menggunakan akun `admin` atau `staff`**

### 20. GET /admin/dashboard
Melihat statistik dan monitoring laporan seluruh madrasah.

- **Method:** `GET`
- **URL:** `{{base_url}}/admin/dashboard`

**Respon Sukses (200):**
```json
{
    "total_madrasah": 5,
    "laporan_masuk": 12,
    "terverifikasi": 8,
    "perlu_revisi": 2,
    "recent_submissions": [...],
    "kecamatan_progress": [...]
}
```

---

### 21. GET /admin/laporan
Melihat daftar laporan yang sudah disubmit dari semua madrasah.

- **Method:** `GET`
- **URL:** `{{base_url}}/admin/laporan`

**Filter opsional (Tab Params):**
| Key       | Value        | Keterangan                        |
|:----------|:-------------|:----------------------------------|
| `status`  | `submitted`  | Filter hanya laporan yang masuk   |
| `status`  | `verified`   | Filter hanya yang sudah disetujui |
| `bulan`   | `2025-02-01` | Filter berdasarkan bulan          |
| `trashed` | `1`          | Melihat laporan di tempat sampah  |

---

### 22. POST /admin/laporan/{id}/verify *(Khusus Staff Penmad)*
Memverifikasi laporan — menyetujui atau meminta revisi.

- **Method:** `POST`
- **URL:** `{{base_url}}/admin/laporan/1/verify`
- **Tab Body** (raw → JSON):

**Untuk Menyetujui:**
```json
{
    "status_laporan": "verified"
}
```

**Untuk Meminta Revisi:**
```json
{
    "status_laporan": "revisi",
    "catatan_revisi": "Mohon lengkapi data sarpras bagian WC Siswa."
}
```

---

### 23. GET /admin/logs
Melihat log aktivitas seluruh user (50 aktivitas terbaru).

- **Method:** `GET`
- **URL:** `{{base_url}}/admin/logs`

---

### 24. GET /admin/recap
Melihat rekapitulasi data seluruh laporan (untuk kebutuhan ekspor).

- **Method:** `GET`
- **URL:** `{{base_url}}/admin/recap`

---

### 25. DELETE /admin/laporan/{id} *(Khusus Staff Penmad)*
Memindahkan laporan *yang sudah diverifikasi* ke tempat sampah Admin.

- **Method:** `DELETE`
- **URL:** `{{base_url}}/admin/laporan/1`

---

### 26. POST /admin/laporan/{id}/restore *(Khusus Staff Penmad)*
Mengembalikan laporan dari tempat sampah Admin.

- **Method:** `POST`
- **URL:** `{{base_url}}/admin/laporan/1/restore`

---

### 27. DELETE /admin/laporan/{id}/permanent *(Khusus Staff Penmad)*
Menghapus laporan secara permanen dari sisi Admin.

- **Method:** `DELETE`
- **URL:** `{{base_url}}/admin/laporan/1/permanent`

---

## 📋 GRUP 5: Master Data Madrasah
> ⚠️ **Akses untuk Kasi & Staff Penmad**

### 28. GET /master/madrasah
Melihat daftar semua madrasah beserta datanya.

- **Method:** `GET`
- **URL:** `{{base_url}}/master/madrasah`

---

### 29. GET /master/madrasah/{id}
Melihat detail satu madrasah berdasarkan ID.

- **Method:** `GET`
- **URL:** `{{base_url}}/master/madrasah/1`

---

### 30. POST /master/madrasah *(Khusus Staff Penmad)*
Menambahkan madrasah baru.

- **Method:** `POST`
- **URL:** `{{base_url}}/master/madrasah`
- **Tab Body** (raw → JSON):
```json
{
    "npsn": "20202020",
    "nama_madrasah": "MTs DARUL ULUM",
    "alamat": "Jl. Pelajar No. 99",
    "kecamatan": "BANGKINANG",
    "status_aktif": true
}
```

---

### 31. PUT /master/madrasah/{id} *(Khusus Staff Penmad)*
Mengupdate data madrasah.

- **Method:** `PUT`
- **URL:** `{{base_url}}/master/madrasah/1`
- **Tab Body** (raw → JSON):
```json
{
    "nama_madrasah": "MTs DARUL ULUM (Updated)",
    "status_aktif": false
}
```

---

### 32. DELETE /master/madrasah/{id} *(Khusus Staff Penmad)*
Menghapus data madrasah.

- **Method:** `DELETE`
- **URL:** `{{base_url}}/master/madrasah/1`

---

## 👥 GRUP 6: Master Data User

### 33. GET /master/users
Melihat semua akun pengguna dalam sistem.

- **Method:** `GET`
- **URL:** `{{base_url}}/master/users`

---

### 34. POST /master/users
Membuat akun pengguna baru.

- **Method:** `POST`
- **URL:** `{{base_url}}/master/users`
- **Tab Body** (raw → JSON):

**Buat Akun Operator:**
```json
{
    "username": "operator_baru",
    "password": "password123",
    "role": "operator_sekolah",
    "id_madrasah": 1
}
```

**Buat Akun Staff:** *(Hanya Kasi Penmad)*
```json
{
    "username": "staff_baru",
    "password": "password123",
    "role": "staff_penmad"
}
```

---

### 35. PUT /master/users/{id}
Mengupdate data pengguna.

- **Method:** `PUT`
- **URL:** `{{base_url}}/master/users/3`
- **Tab Body** (raw → JSON):
```json
{
    "password": "passwordbaru123",
    "id_madrasah": 2
}
```
> Jika `password` dikosongkan, password lama tidak akan berubah.

---

### 36. DELETE /master/users/{id}
Menghapus akun pengguna.

- **Method:** `DELETE`
- **URL:** `{{base_url}}/master/users/3`

> ⚠️ Akun `kasi_penmad` tidak dapat dihapus. Akun sendiri tidak dapat dihapus.

---

## 📢 GRUP 7: Pengumuman
> ⚠️ **Khusus Kasi Penmad**

### 37. POST /master/pengumuman
Membuat pengumuman baru untuk semua operator.

- **Method:** `POST`
- **URL:** `{{base_url}}/master/pengumuman`
- **Tab Body** (raw → JSON):
```json
{
    "judul": "Batas Akhir Laporan Maret 2025",
    "isi_info": "Mohon semua operator mengisi dan mengirim laporan bulan Maret sebelum tanggal 5 April 2025."
}
```

---

### 38. DELETE /master/pengumuman/{id} *(Khusus Kasi Penmad)*
Menghapus pengumuman.

- **Method:** `DELETE`
- **URL:** `{{base_url}}/master/pengumuman/1`

---

## 📊 Ringkasan Semua API Routes

| No | Method | Endpoint | Role | Keterangan |
|:---|:-------|:---------|:-----|:-----------|
| 1  | POST   | `/login` | Semua | Login |
| 2  | GET    | `/me` | Semua | Profil sendiri |
| 3  | POST   | `/logout` | Semua | Logout |
| 4  | GET    | `/pengumuman` | Semua | Lihat pengumuman |
| 5  | GET    | `/laporan/{id}` | Semua | Detail laporan |
| 6  | GET    | `/operator/dashboard` | Operator | Dashboard |
| 7  | POST   | `/laporan` | Operator | Buat laporan |
| 8  | PUT    | `/laporan/{id}/siswa` | Operator | Update data siswa |
| 9  | PUT    | `/laporan/{id}/rekap-personal` | Operator | Update rekap personal |
| 10 | PUT    | `/laporan/{id}/guru` | Operator | Update daftar guru |
| 11 | PUT    | `/laporan/{id}/sarpras` | Operator | Update sarpras |
| 12 | PUT    | `/laporan/{id}/mobiler` | Operator | Update mobiler |
| 13 | PUT    | `/laporan/{id}/keuangan` | Operator | Update keuangan |
| 14 | POST   | `/laporan/{id}/submit` | Operator | Submit laporan |
| 15 | DELETE | `/laporan/{id}` | Operator | Hapus ke sampah |
| 16 | POST   | `/laporan/{id}/restore` | Operator | Pulihkan laporan |
| 17 | DELETE | `/laporan/{id}/permanent` | Operator | Hapus permanen |
| 18 | GET    | `/operator/madrasah` | Operator | Profil madrasah |
| 19 | PUT    | `/operator/madrasah` | Operator | Update profil madrasah |
| 20 | GET    | `/admin/dashboard` | Admin | Dashboard admin |
| 21 | GET    | `/admin/laporan` | Admin | List validasi |
| 22 | POST   | `/admin/laporan/{id}/verify` | Staff | Verifikasi laporan |
| 23 | GET    | `/admin/logs` | Admin | Log aktivitas |
| 24 | GET    | `/admin/recap` | Admin | Rekapitulasi |
| 25 | DELETE | `/admin/laporan/{id}` | Staff | Hapus laporan |
| 26 | POST   | `/admin/laporan/{id}/restore` | Staff | Pulihkan laporan |
| 27 | DELETE | `/admin/laporan/{id}/permanent` | Staff | Hapus permanen |
| 28 | GET    | `/master/madrasah` | Admin | List madrasah |
| 29 | GET    | `/master/madrasah/{id}` | Admin | Detail madrasah |
| 30 | POST   | `/master/madrasah` | Staff | Tambah madrasah |
| 31 | PUT    | `/master/madrasah/{id}` | Staff | Update madrasah |
| 32 | DELETE | `/master/madrasah/{id}` | Staff | Hapus madrasah |
| 33 | GET    | `/master/users` | Admin | List user |
| 34 | POST   | `/master/users` | Admin | Tambah user |
| 35 | PUT    | `/master/users/{id}` | Admin | Update user |
| 36 | DELETE | `/master/users/{id}` | Admin | Hapus user |
| 37 | POST   | `/master/pengumuman` | Kasi | Buat pengumuman |
| 38 | DELETE | `/master/pengumuman/{id}` | Kasi | Hapus pengumuman |

---

## 📝 Keterangan Role

| Role | Username (Seeder) | Akses |
|:-----|:-----------------|:------|
| `operator_sekolah` | `operator` | Grup 3 + GET Pengumuman |
| `staff_penmad` | `staff` | Grup 4 + Grup 5 + Grup 6 |
| `kasi_penmad` | `admin` | Semua akses |

---

*Dibuat otomatis berdasarkan `routes/api.php` - Sistem Laporan Kemenag*
