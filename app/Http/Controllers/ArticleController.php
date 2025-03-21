<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ArticleController extends Controller
{
    // Data statis artikel (bisa digantikan dengan query database)
    protected $articles = [
        1 => [
            'title' => 'Cara Menghindari Tanaman Lidah Buaya Terkena Serangan Hama atau Penyakit',
            'content' => <<<EOT
Tanaman lidah buaya (Aloe vera) dikenal sebagai tanaman yang tahan banting dan mudah tumbuh. Namun, seperti tanaman lainnya, lidah buaya juga rentan terhadap serangan hama dan penyakit, terutama pada bagian pelepahnya. Artikel ini akan membahas cara menghindari serangan hama dan penyakit pada tanaman lidah buaya, dilengkapi dengan referensi dari jurnal penelitian terdahulu.

**Penyakit Umum pada Pelepah Lidah Buaya**

Berdasarkan penelitian yang dilakukan oleh Sutari dan Widyastuti (2018) dalam jurnal *Agroplantae*, beberapa penyakit yang sering menyerang pelepah lidah buaya antara lain:

- **Busuk Pangkal Batang (Stem Rot):**  
  *Penyebab:* Jamur Fusarium spp. atau bakteri.  
  *Gejala:* Pelepah menjadi lunak, berwarna cokelat kehitaman, dan berbau busuk.  
  *Pencegahan:* Hindari penyiraman berlebihan dan pastikan drainase tanah baik.

- **Bercak Daun (Leaf Spot):**  
  *Penyebab:* Jamur Alternaria spp. atau Colletotrichum spp.  
  *Gejala:* Muncul bercak cokelat atau hitam pada pelepah.  
  *Pencegahan:* Jaga kebersihan kebun dan hindari kelembapan berlebihan.

- **Infeksi Bakteri:**  
  *Penyebab:* Bakteri Erwinia spp.  
  *Gejala:* Pelepah mengeluarkan lendir dan berbau tidak sedap.  
  *Pencegahan:* Gunakan alat kebun yang steril dan hindari luka pada tanaman.

**Hama yang Sering Menyerang Lidah Buaya**

Selain penyakit, lidah buaya juga rentan terhadap serangan hama, seperti:
- **Kutu Putih (Mealybugs)**
- **Tungau (Spider Mites)**
- **Ulat dan Serangga Pemakan Daun**

**Cara Menghindari Serangan Hama dan Penyakit**

Beberapa langkah pencegahan:
- Pemilihan bibit yang sehat.
- Pengaturan jarak tanam yang baik.
- Pengelolaan air yang tepat.
- Pemupukan yang seimbang.
- Sanitasi kebun yang rutin.
- Penggunaan pestisida alami.

Referensi: Sutari & Widyastuti (2018); Kurniawan et al. (2020).

Tanaman lidah buaya dapat terhindar dari serangan hama dan penyakit dengan melakukan pencegahan yang tepat, sehingga menghasilkan kualitas optimal.
EOT
        ],
        2 => [
            'title' => 'Harga Terbaik untuk Penjualan Pelepah dan Olahan Lidah Buaya di Indonesia',
            'content' => <<<EOT
Lidah buaya (Aloe vera) adalah tanaman serbaguna yang banyak dijual dalam bentuk pelepah segar maupun olahan seperti minuman, makanan, dan kosmetik. Artikel ini membahas informasi harga terbaik dan strategi penjualan di Indonesia.

**Harga Pelepah Lidah Buaya Segar**  
- **Pasar Tradisional:** Rp 5.000 – Rp 15.000 per kilogram.  
- **Pasar Modern:** Rp 20.000 – Rp 30.000 per kilogram.  
- **Dari Petani Langsung:** Rp 3.000 – Rp 10.000 per kilogram.

**Faktor yang Mempengaruhi Harga:**  
- Lokasi  
- Kualitas  
- Musim

**Strategi Menentukan Harga:**  
- Analisis pasar  
- Tingkatkan kualitas produk  
- Manfaatkan platform online

Dengan menerapkan strategi ini, pelaku usaha dapat menentukan harga yang kompetitif dan menguntungkan.
EOT
        ],
        3 => [
            'title' => 'Tutorial: Cara Input dan Format Excel untuk Analisis Panen',
            'content' => <<<EOT
Artikel ini memberikan tutorial langkah demi langkah mengenai cara input data dan format Excel yang tepat untuk mendukung analisis panen. Panduan ini mencakup:

1. **Persiapan Data:**  
   Pastikan data panen diorganisir dengan rapi, misalnya dengan kolom: Tanggal, Luas Lahan, Jumlah Panen, dan variabel relevan lainnya.

2. **Format Excel:**  
   Gunakan format tabel dan pastikan setiap kolom memiliki header yang jelas.

3. **Tips Pengolahan Data:**  
   - Gunakan filter dan sorting untuk memudahkan analisis.
   - Gunakan fungsi-fungsi bawaan Excel (seperti SUM, AVERAGE) untuk analisis awal.
   - Pastikan data tidak mengandung nilai yang kosong.

Ikuti tutorial ini untuk memaksimalkan analisis data panen Anda.
EOT
        ],
    ];

    public function index()
    {
        $articles = $this->articles;
        return view('articles.index', compact('articles'));
    }

    public function show($id)
    {
        if (!isset($this->articles[$id])) {
            abort(404);
        }
        $article = $this->articles[$id];
        return view('articles.show', compact('article'));
    }
}
