# NS BLOCK - Solusi Material Bangunan Modern

NS BLOCK adalah platform e-commerce dan panel administrasi berbasis web yang dirancang khusus untuk produsen dan distributor material bangunan modern (seperti Roster, Paving Block, Buis Beton, dan produk beton cetak lainnya). 

Aplikasi ini memudahkan pelanggan untuk menghitung kebutuhan bahan secara presisi, melakukan checkout pesanan, dan mengunggah bukti pembayaran secara digital. Di sisi lain, pemilik toko dibekali dengan Dashboard Admin terpusat untuk mengelola inventaris produk, moderasi testimoni pelanggan, dan memantau status pesanan.

---

## 🚀 Fitur Utama

### 1. Fitur Pelanggan (Public Portal)
* **Katalog Produk Interaktif:** Tampilan katalog produk yang dikelompokkan berdasarkan kategori (**Roster**, **Paving Block**, **Buis Beton**, dan **Lainnya**) dilengkapi dengan fitur geser (carousel).
* **Estimator Kebutuhan Bahan (Area Calculator):** Kalkulator dinamis otomatis untuk produk kategori **Roster** dan **Paving Block**. Pelanggan cukup memasukkan dimensi panjang & lebar area proyek untuk mengetahui jumlah pcs bahan yang dibutuhkan beserta estimasi total biayanya.
* **Sistem Keranjang Belanja (Shopping Cart):** Keranjang interaktif berbasis penyimpanan lokal browser (*client-side LocalStorage*) untuk menambah, mengubah kuantitas, menghapus, atau membatalkan pesanan.
* **Proses Checkout & Simulasi Pembayaran:** Formulir checkout lengkap (Nama, WhatsApp, Alamat Pengiriman, Opsi Pengambilan) yang terintegrasi dengan metode pembayaran QRIS. Pelanggan dapat mengunggah foto bukti transfer langsung di tempat.
* **Riwayat Pesanan Saya (Order History):** Panel khusus bagi pelanggan untuk memantau status pengerjaan atau pengiriman pesanan mereka secara real-time.
* **Sistem Testimoni (Review):** Form pengiriman umpan balik (feedback) pelanggan yang masuk ke sistem moderasi admin sebelum ditampilkan ke publik.
* **Mode Gelap / Terang (Light & Dark Theme):** Pengubah tema tampilan global yang persistensinya disimpan dalam memori browser.

### 2. Dashboard Administrasi (Admin Panel)
* **Ringkasan Metrik Bisnis (Overview):** Kartu informasi statistik jumlah produk, total pelanggan, jumlah testimoni, pesanan aktif, hingga status konfirmasi pembayaran.
* **Manajemen Produk (CRUD):** Tambah, edit, dan hapus data produk lengkap dengan fitur unggah file foto produk. Sistem secara otomatis menghapus file gambar lama dari server saat produk diperbarui atau dihapus (mencegah penumpukan sampah file di disk).
* **Manajemen Pesanan:** Tampilan transaksi pelanggan secara real-time. Admin dapat menyetujui, membatalkan, mengubah status pengiriman (Diproses, Siap Diambil, Dalam Pengiriman, Selesai), serta melihat gambar bukti transfer QRIS dalam ukuran penuh.
* **Moderasi Testimoni:** Persetujuan satu klik untuk menyetujui (Tampilkan) atau menyembunyikan testimoni pelanggan sebelum dirilis ke halaman utama.
* **Daftar Pelanggan:** Daftar seluruh data akun pengguna yang telah terdaftar di aplikasi.

### 3. Keamanan & Desain Sistem
* **Perlindungan SQL Injection:** Seluruh interaksi database backend PHP menggunakan *Prepared Statements* (`$conn->prepare`).
* **Autentikasi Aman:** Registrasi akun menggunakan pengacakan kata sandi satu arah yang kuat (*BCRYPT hashing* via `password_hash`).
* **Sistem Peran Otomatis (Auto-Role):** Akun pertama kali yang terdaftar dalam database secara otomatis diangkat sebagai **Administrator** (memiliki hak akses penuh ke panel admin), sedangkan akun selanjutnya bertindak sebagai **Pelanggan**.
* **Antarmuka Modern (Premium UI):** Dibangun menggunakan CSS modern dengan variabel kustom, font Google Poppins, animasi transisi lembut, serta tata letak responsif (*mobile-friendly*).

---

## 📁 Struktur Direktori

Berikut adalah tata letak folder dan file utama dalam proyek ini:

```text
nsblock/
├── admin/                     # Modul Panel Admin
│   ├── index.php              # Dashboard utama admin (Ringkasan, CRUD, Orders, Testimoni)
│   ├── proses.php             # Backend pemrosesan CRUD produk & upload gambar
│   ├── proses_testimoni.php   # Backend moderasi testimoni
│   └── style-admin.php        # Lembar gaya dinamis khusus panel admin
├── auth/                      # Modul Autentikasi Pengguna
│   ├── check_users.php        # Middleware verifikasi sesi, peran user, & helper akses
│   ├── login.php              # Halaman antarmuka login
│   ├── login_action.php       # Backend pemrosesan login & verifikasi password BCRYPT
│   ├── logout.php             # Script untuk menghancurkan sesi user (logout)
│   └── register_action.php    # Backend pemrosesan registrasi user & hashing password
├── css/
│   └── style.css              # File stylesheet utama frontend (Landing, Cart, Detail, dll.)
├── js/
│   ├── commerce.js            # Logika belanja client-side, LocalStorage pesanan, & render tabel admin
│   └── script.js              # Logika UI landing page, menu responsif, & tema gelap/terang
├── Picture/                   # Aset gambar statis untuk profile perusahaan
├── uploads/                   # Direktori penyimpanan gambar produk yang diunggah admin
├── checkout.php               # Halaman transaksi checkout pesanan pelanggan
├── connect_testimoni.php      # Backend penerimaan kiriman testimoni pelanggan
├── db_connect.php             # Inisialisasi koneksi database MySQL & self-healing migrations
├── index.php                  # Halaman utama landing page & katalog produk
├── orders.php                 # Halaman daftar transaksi riwayat pesanan saya
└── produk.php                 # Halaman detail produk (disertai Area Calculator)
```

---

## 🗄️ Skema Database

Gunakan skrip SQL berikut untuk membuat database dan tabel yang diperlukan di MySQL Anda:

```sql
CREATE DATABASE IF NOT EXISTS ns_block_db;
USE ns_block_db;

-- 1. Tabel Users
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Tabel Products
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    category VARCHAR(50) NOT NULL, -- Roster, Paving Block, Buis Beton, Lainnya
    ukuran VARCHAR(50) NOT NULL,   -- Contoh: "20x10x5 cm"
    harga DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    stok INT NOT NULL DEFAULT 0,
    paving_rate INT NULL DEFAULT NULL, -- Kebutuhan pcs per m2 (untuk paving)
    description TEXT NOT NULL,
    image VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Tabel Testimonials
CREATE TABLE IF NOT EXISTS testimonials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NULL,
    message TEXT NOT NULL,
    is_approved TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

> [!NOTE]
> Sistem manajemen pesanan (*Orders*) memanfaatkan `LocalStorage` browser (`nsblock_orders`) untuk menyimpan transaksi belanja. Data pesanan ini digunakan secara bersama oleh portal pelanggan dan panel admin lokal untuk mensimulasikan operasional bisnis secara instan tanpa perlu membebani basis data server.

---

## 🛠️ Panduan Instalasi & Konfigurasi

Ikuti langkah-langkah di bawah ini untuk menjalankan proyek ini di lingkungan lokal Anda:

### 1. Prasyarat Sistem
Pastikan komputer Anda sudah terinstal aplikasi tumpukan web (*web stack*) seperti:
* **PHP** versi 7.4 atau yang lebih baru.
* **MySQL** / **MariaDB**.
* Web Server seperti **Apache** (tersedia satu paket di aplikasi seperti **XAMPP**, **WampServer**, atau **Laragon**).

### 2. Pengaturan Berkas Proyek
1. Pindahkan folder proyek `nsblock` ke direktori root web server Anda:
   * **XAMPP**: `C:\xampp\htdocs\nsblock` (Windows) atau `/Applications/XAMPP/htdocs/nsblock` (macOS).
   * **Laragon**: `C:\laragon\www\nsblock`.
2. Pastikan folder `uploads/` memiliki izin akses menulis (*write permissions*). Pada macOS/Linux, jalankan perintah chmod berikut jika diperlukan:
   ```bash
   chmod -R 775 uploads
   ```

### 3. Konfigurasi Database & Migrasi Mandiri
1. Jalankan panel kontrol XAMPP / Laragon Anda dan aktifkan layanan **Apache** dan **MySQL**.
2. Masuk ke **phpMyAdmin** (`http://localhost/phpmyadmin`) lalu buat database baru bernama `ns_block_db`.
3. Impor skema tabel di atas melalui tab SQL pada database `ns_block_db`.
4. Buka berkas [db_connect.php](file:///Users/reynaldisiregar/My%20Projects/nsblock/db_connect.php) menggunakan text editor Anda, lalu sesuaikan kredensial database Anda jika berbeda:
   ```php
   $db_host = 'localhost';
   $db_user = 'root';
   $db_pass = '';
   $db_name = 'ns_block_db';
   ```
5. **Migrasi Otomatis (Self-healing Migrations):** Proyek ini dilengkapi dengan skrip pemeriksaan kolom otomatis. Setiap kali Anda memuat aplikasi, program akan secara otomatis memeriksa dan memperbarui struktur kolom tabel `products` (seperti memastikan kolom `harga`, `stok`, dan `paving_rate` telah terbuat dengan benar).

### 4. Menjalankan Aplikasi
1. Buka browser Anda dan akses alamat:
   ```text
   http://localhost/nsblock/
   ```
2. Buat akun pertama Anda melalui menu **Masuk** -> **Daftar Akun Baru**.
3. Akun pertama ini secara otomatis akan dikenali sistem sebagai **Admin** dan dialihkan ke `/admin/index.php`. Anda dapat mulai menambahkan produk baru di sana.
4. Buka browser baru atau gunakan mode samaran (*Incognito Window*) untuk mendaftarkan akun kedua. Akun-akun selanjutnya ini akan bertindak sebagai **Pelanggan** biasa yang dapat melihat katalog dan berbelanja.
