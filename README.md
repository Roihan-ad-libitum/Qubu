# 🚀 Qubu - Platform Info Lomba & Kolaborasi

<p align="center">
  <strong>Platform Terpadu Pencarian Partner Lomba, Kolaborasi Tim, dan Bimbingan Dosen</strong>
</p>

---

## 📖 Sekilas Tentang Qubu

**Qubu** adalah platform kolaborasi kompetisi yang menghubungkan mahasiswa dan dosen. Melalui platform ini, mahasiswa dapat menemukan informasi lomba terkini, mencari rekan satu tim dari berbagai keahlian, serta mengajukan dosen pembimbing untuk mendampingi kompetisi mereka.

Platform ini telah dilengkapi dengan sistem autentikasi multi-peran (*Multi-Role Access*), pendaftaran mandiri untuk Mahasiswa dan Dosen, serta tampilan beranda modern dengan animasi interaktif.

---

## ⚡ Cara Menjalankan Website

Ikuti 3 langkah mudah berikut untuk menjalankan Qubu di perangkat lokal Anda:

1. **Persiapkan Database**
   - Pastikan layanan MySQL / MariaDB telah aktif (misalnya via XAMPP atau terminal).
   - Buat database baru bernama `qubu-project`.
   - Impor file `qubu-project.sql` ke dalam database `qubu-project`.

2. **Jalankan Server Lokal**
   - Buka terminal pada folder proyek ini.
   - Ketik dan jalankan perintah: `php -S localhost:8000`

3. **Buka di Browser**
   - Akses alamat: **http://localhost:8000**
   - Website siap digunakan dan diuji dengan berbagai akun.

---

## 👥 Panduan Masuk dengan Akun yang Berbeda

Untuk menguji alur kerja setiap pengguna, sistem telah menyediakan akun bawaan untuk masing-masing peran:

| Peran (Role) | Username / Email | Password | Akses Halaman | Keterangan Akun |
| :--- | :--- | :--- | :--- | :--- |
| **Mahasiswa (Peserta)** | `roihanz` | `roihanz123` | Dashboard Mahasiswa | Akun mahasiswa aktif (Informatika 2025) |
| **Mahasiswa (Peserta)** | `ranggaz` | `ranggaz` | Dashboard Mahasiswa | Rekan tim mahasiswa (Frontend Enthusiast) |
| **Dosen Pembimbing** | `fitriaz` | `fitriaz123` | Dashboard Dosen | Dosen pembimbing bidang Web Development |
| **Administrator** | `marcelloz` | `marceloz123` | Dashboard Admin | Pengelola master data dan sistem |

> 💡 **Fitur Uji Cepat (Auto-Fill)**: Di halaman Login (**http://localhost:8000/?page=login**), Anda cukup mengklik salah satu tombol pill peran di bawah form untuk mengisi username dan password secara otomatis tanpa perlu mengetik manual.

---

## 🎯 Panduan & Eksplorasi Setiap Peran

### 1. 🎓 Mahasiswa (Peserta)
- **Kredensial**: Gunakan `roihanz` / `roihanz123`
- **Tujuan Penggunaan**:
  - Mengakses katalog informasi perlombaan terkini dan jadwal batas pendaftaran (*deadline*).
  - Mencari partner lomba berdasarkan keahlian (UI/UX, Frontend, Backend, Data/AI).
  - Mengirim dan menerima undangan tim kompetisi.
  - Memilih dan mengajukan dosen pembimbing untuk mendampingi tim lomba.
- **Pendaftaran Akun Baru**: Mahasiswa baru dapat mendaftar mandiri melalui menu registrasi dengan memilih opsi **Mahasiswa** dan melengkapi NPM, Program Studi, Angkatan, serta Bidang Minat.

### 2. 👨‍🏫 Dosen Pembimbing
- **Kredensial**: Gunakan `fitriaz` / `fitriaz123`
- **Tujuan Penggunaan**:
  - Mengakses area kerja dosen pembimbing.
  - Memantau tim mahasiswa yang mengajukan permohonan bimbingan lomba.
  - Mengelola status kesediaan membimbing tim (*Bersedia / Tidak Bersedia*).
  - Melihat portofolio dan capaian kompetisi mahasiswa binaan.
- **Pendaftaran Akun Baru**: Dosen dapat mendaftar mandiri melalui menu registrasi dengan memilih opsi **Dosen** dan melengkapi data Nama Lengkap beserta Gelar, NIDN, Bidang Keahlian, dan Email Resmi Kampus.

### 3. 🛡️ Administrator Sistem
- **Kredensial**: Gunakan `marcelloz` / `marceloz123`
- **Tujuan Penggunaan**:
  - Mengakses dashboard pusat kendali sistem.
  - Menambah, memperbarui, dan menghapus data kompetisi lomba.
  - Memantau keaktifan pengguna (mahasiswa dan dosen).
  - Memastikan seluruh kolaborasi berjalan tertib dan terdata.

---

## 📝 Panduan Mendaftar Akun Baru

Jika ingin menguji pendaftaran akun baru dari awal:

1. Akses menu **Daftar Akun** atau buka URL: **http://localhost:8000/?page=register**
2. **Pilih Peran Akun**:
   - Pilih kartu **Mahasiswa** jika ingin mendaftar sebagai peserta lomba.
   - Pilih kartu **Dosen** jika ingin mendaftar sebagai dosen pembimbing.
3. Lengkapi formulir pendaftaran yang tampil.
4. Sistem akan memvalidasi data dan menyimpan akun secara otomatis.
5. Setelah berhasil, Anda akan dialihkan ke halaman login untuk langsung masuk menggunakan username dan password yang baru saja dibuat.

---

## 🧭 Peta Navigasi Halaman

- **Beranda (Landing Page)**: `http://localhost:8000/?page=home`
- **Halaman Masuk (Login)**: `http://localhost:8000/?page=login`
- **Pilihan Peran Pendaftaran**: `http://localhost:8000/?page=register`
- **Formulir Daftar Mahasiswa**: `http://localhost:8000/?page=register&role=mahasiswa`
- **Formulir Daftar Dosen**: `http://localhost:8000/?page=register&role=dosen`
- **Keluar Akun (Logout)**: `http://localhost:8000/?page=logout`

---

<p align="center">
  Dibuat untuk mempermudah kolaborasi dan peningkatan prestasi lomba mahasiswa.
</p>
