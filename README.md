

## Prediksi Hasil Panen

**Prediksi Hasil Panen** adalah aplikasi web untuk membantu petani memprediksi hasil panen perkebunan lidah buaya menggunakan teknologi Machine Learning. Aplikasi ini dibangun dengan **Laravel** untuk sisi web dan terintegrasi dengan API **Flask** untuk proses prediksi. Selain itu, project ini juga menyediakan artikel edukasi sebagai sumber pengetahuan tambahan bagi para petani.

## Fitur

- **Upload & Preview CSV:**  
  Petani dapat mengunggah file CSV dengan data perkebunan lidah buaya dan melihat preview 5 baris pertama sebelum diproses.
  
- **Prediksi Hasil Panen:**  
  Menggunakan model Linear Regression (via API Flask) untuk memprediksi hasil panen.  
  Terdapat dua mode tampilan grafik:
  - **Mode Bulanan:** Menampilkan data per bulan (diurutkan dari tahun paling kecil dan bulan 1 sampai 12, lalu tahun berikutnya).
  - **Mode Tahunan:** Menampilkan data agregat per tahun.
  
- **Grafik Interaktif:**  
  Grafik ditampilkan dengan Chart.js dalam container yang dapat discroll horizontal.  
  Terdapat opsi untuk download grafik sebagai PDF.
  
- **Artikel Edukasi:**  
  Modul artikel untuk memberikan pengetahuan tambahan tentang pengelolaan tanaman lidah buaya, harga, dan strategi pemasaran.

## Instalasi

### Prasyarat
Pastikan sistem Anda memiliki:
- PHP 8.x dan ekstensi yang diperlukan (OpenSSL, PDO, Mbstring, Tokenizer, XML)
- Composer
- Node.js & NPM (atau Yarn)
- Python 3.x (untuk API Flask)
- Git

### 1. Clone Repository
```bash
git clone https://github.com/yourusername/prediksi-hasil-panen.git
cd prediksi-hasil-panen
```

### 2. Instalasi Laravel
Instal dependensi PHP dengan Composer:
```bash
composer install
```

Salin file .env.example ke .env dan atur konfigurasi (database, APP_KEY, dsb.):
```bash
cp .env.example .env
```
```bash
php artisan key:generate
```

Instal dependensi Node.js:
```bash
npm install
```

Kemudian compile asset dengan Vite:
```bash
npm run dev
```

3. Konfigurasi Database
Atur koneksi database di file .env, misalnya:
```bash
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=prediksi_panen
DB_USERNAME=root
DB_PASSWORD=
```

Jalankan migrasi (jika ada):
```bash
php artisan migrate
```

4. Menjalankan Server Laravel
```bash
php artisan serve
```