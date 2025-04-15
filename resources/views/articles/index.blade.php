@extends('layouts.app')

@section('content')
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 p-6">
        <div class="max-w-4xl mx-auto">
            <!-- Tombol kembali ke Beranda -->
            <div class="mb-6">
                <a href="{{ route('home') }}"
                    class="hover:bg-emerald-700 transition btn-primary px-4 py-2 bg-emerald-600 text-white rounded-lg disabled:opacity-50 inline-block">
                    Kembali ke Beranda
                </a>
            </div>

            <h1
                class="text-4xl mb-4 font-bold text-center text-transparent bg-clip-text bg-gradient-to-r from-emerald-600 to-teal-600">
                Artikel Edukasi
            </h1>

            <!-- Informasi Format Dataset & Template Download -->
            <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
                <h2 class="text-2xl font-semibold text-emerald-600 mb-2">
                    Format Dataset untuk Analisis Panen
                </h2>
                <p class="text-gray-600 mb-4">
                    Pastikan dataset memiliki kolom-kolom berikut sebelum diunggah:
                </p>

                <div class="overflow-x-auto">
                    <table class="w-full border-collapse border border-gray-200">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="border border-gray-200 px-4 py-2 text-gray-700">Atribut</th>
                                <th class="border border-gray-200 px-4 py-2 text-gray-700">Deskripsi</th>
                                <th class="border border-gray-200 px-4 py-2 text-gray-700">Contoh</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="border border-gray-200 px-4 py-2 text-gray-700">Tahun</td>
                                <td class="border border-gray-200 px-4 py-2 text-gray-700">Tahun data dicatat</td>
                                <td class="border border-gray-200 px-4 py-2 text-gray-700">2021</td>
                            </tr>
                            <tr>
                                <td class="border border-gray-200 px-4 py-2 text-gray-700">Bulan</td>
                                <td class="border border-gray-200 px-4 py-2 text-gray-700">Bulan dalam angka (1-12)</td>
                                <td class="border border-gray-200 px-4 py-2 text-gray-700">5</td>
                            </tr>
                            <tr>
                                <td class="border border-gray-200 px-4 py-2 text-gray-700">Suhu (°C)</td>
                                <td class="border border-gray-200 px-4 py-2 text-gray-700">Suhu rata-rata harian</td>
                                <td class="border border-gray-200 px-4 py-2 text-gray-700">28.00</td>
                            </tr>
                            <tr>
                                <td class="border border-gray-200 px-4 py-2 text-gray-700">Curah Hujan (mm/tahun)</td>
                                <td class="border border-gray-200 px-4 py-2 text-gray-700">Jumlah curah hujan tahunan</td>
                                <td class="border border-gray-200 px-4 py-2 text-gray-700">2000.00</td>
                            </tr>
                            <tr>
                                <td class="border border-gray-200 px-4 py-2 text-gray-700">Kelembapan (%)</td>
                                <td class="border border-gray-200 px-4 py-2 text-gray-700">Tingkat kelembapan rata-rata</td>
                                <td class="border border-gray-200 px-4 py-2 text-gray-700">75.00</td>
                            </tr>
                            <tr>
                                <td class="border border-gray-200 px-4 py-2 text-gray-700">Dosis Pupuk (kg/ha)</td>
                                <td class="border border-gray-200 px-4 py-2 text-gray-700">Jumlah pupuk yang digunakan</td>
                                <td class="border border-gray-200 px-4 py-2 text-gray-700">150.00</td>
                            </tr>
                            <tr>
                                <td class="border border-gray-200 px-4 py-2 text-gray-700">Umur Tanaman (bulan)</td>
                                <td class="border border-gray-200 px-4 py-2 text-gray-700">Umur tanaman saat panen</td>
                                <td class="border border-gray-200 px-4 py-2 text-gray-700">36</td>
                            </tr>
                            <tr>
                                <td class="border border-gray-200 px-4 py-2 text-gray-700">Luas Lahan (ha)</td>
                                <td class="border border-gray-200 px-4 py-2 text-gray-700">Luas area yang ditanam</td>
                                <td class="border border-gray-200 px-4 py-2 text-gray-700">2.50</td>
                            </tr>
                            <tr>
                                <td class="border border-gray-200 px-4 py-2 text-gray-700">Hasil Panen (kwintal/ha)</td>
                                <td class="border border-gray-200 px-4 py-2 text-gray-700">Jumlah panen per hektar</td>
                                <td class="border border-gray-200 px-4 py-2 text-gray-700">12.50</td>
                            </tr>
                            <tr>
                                <td class="border border-gray-200 px-4 py-2 text-gray-700">Lokasi</td>
                                <td class="border border-gray-200 px-4 py-2 text-gray-700">Nama lokasi kebun</td>
                                <td class="border border-gray-200 px-4 py-2 text-gray-700">Kebun Samping Kiri</td>
                            </tr>
                            <tr>
                                <td class="border border-gray-200 px-4 py-2 text-gray-700">Area (m²)</td>
                                <td class="border border-gray-200 px-4 py-2 text-gray-700">Luas area dalam meter persegi
                                </td>
                                <td class="border border-gray-200 px-4 py-2 text-gray-700">800</td>
                            </tr>
                            <tr>
                                <td class="border border-gray-200 px-4 py-2 text-gray-700">Pelepah Terkena Penyakit (kw)
                                </td>
                                <td class="border border-gray-200 px-4 py-2 text-gray-700">Jumlah pelepah terkena penyakit
                                </td>
                                <td class="border border-gray-200 px-4 py-2 text-gray-700">2.50</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <p class="text-gray-600 mt-4">
                    Gunakan template Excel berikut untuk pengisian data. Simpan sebagai <b>CSV</b> sebelum diunggah.
                </p>

                <a href="{{ route('download.template') }}"
                    class="hover:bg-emerald-700 transition btn-primary px-4 py-2 bg-emerald-600 text-white rounded-lg inline-block mt-4">
                    Download Template Excel
                </a>
                
            </div>

            <!-- List Artikel -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6" style="margin-top: 25px">
                @foreach ($articles as $id => $article)
                    <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-2xl transition duration-300">
                        <h2 class="text-2xl font-semibold text-gray-800 mb-2">
                            {{ $article['title'] }}
                        </h2>
                        <p class="text-gray-600 line-clamp-3">
                            {{ Str::limit(strip_tags($article['content']), 150) }}
                        </p>
                        <a href="{{ route('articles.show', $id) }}"
                            class="hover:bg-emerald-700 transition btn-primary px-4 py-2 bg-emerald-600 text-white rounded-lg disabled:opacity-50 inline-block mt-4">
                            Baca Selengkapnya
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection
