<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class SiteController extends Controller
{
    public function home()
    {
        return view('pages.home');
    }

    // Proses upload file dan simpan isi file di session untuk preview
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:2048'
        ]);

        $file = $request->file('file');
        if (!$file->isValid()) {
            return redirect()->back()->withErrors(['file' => 'File upload tidak valid']);
        }

        $contents = file_get_contents($file->getRealPath());
        Session::put('uploaded_csv', $contents);
        Session::put('uploaded_filename', $file->getClientOriginalName());

        return redirect()->route('upload.preview');
    }

    // Tampilkan halaman preview (5 baris pertama)
    public function preview()
    {
        $contents = Session::get('uploaded_csv');
        if (!$contents) {
            return redirect()->route('home')->withErrors(['file' => 'Tidak ada file yang diupload']);
        }
        $lines = explode("\n", $contents);
        $headers = str_getcsv($lines[0]);
        $rows = [];
        for ($i = 1; $i < min(6, count($lines)); $i++) {
            if (trim($lines[$i]) !== '') {
                $rows[] = str_getcsv($lines[$i]);
            }
        }
        return view('pages.preview', compact('headers', 'rows'));
    }

    // Kirim file ke API Flask untuk analisis dan simpan hasilnya di session
    public function analyze(Request $request)
    {
        $contents = Session::get('uploaded_csv');
        if (!$contents) {
            return redirect()->route('home')->withErrors(['file' => 'Tidak ada file yang diupload']);
        }

        // Buat file temporary
        $tempFile = tmpfile();
        $metaData = stream_get_meta_data($tempFile);
        $tmpFilename = $metaData['uri'];
        fwrite($tempFile, $contents);
        rewind($tempFile);

        try {
            $response = Http::attach(
                'file',
                file_get_contents($tmpFilename),
                Session::get('uploaded_filename', 'data.csv')
            )->post('http://localhost:5000/predict');

            if ($response->successful()) {
                // Data response dari API Flask sudah mengandung kunci baru:
                // timeLabelsMonthlyHistorical, predictionsMonthlyHistorical,
                // timeLabelsMonthlyFuture, predictionsMonthlyFuture,
                // timeLabelsYearlyHistorical, predictionsYearlyHistorical, actualYearlyHistorical,
                // conclusion, suggestion.
                $data = $response->json()['data'];

                // --- Hitung prediksi tahunan masa depan dari data bulanan ---
                if (isset($data['timeLabelsMonthlyFuture']) && isset($data['predictionsMonthlyFuture'])) {
                    $yearlyFuture = [];
                    foreach ($data['timeLabelsMonthlyFuture'] as $index => $label) {
                        list($year, $month) = explode('-', $label);
                        $yearlyFuture[$year][] = $data['predictionsMonthlyFuture'][$index];
                    }
                    $aggregatedYearlyFuture = [];
                    foreach ($yearlyFuture as $year => $values) {
                        $aggregatedYearlyFuture[$year] = array_sum($values) / count($values);
                    }
                    ksort($aggregatedYearlyFuture);
                    $data['timeLabelsYearlyFuture'] = array_keys($aggregatedYearlyFuture);
                    $data['predictionsYearlyFuture'] = array_values($aggregatedYearlyFuture);
                } else {
                    $data['timeLabelsYearlyFuture'] = [];
                    $data['predictionsYearlyFuture'] = [];
                }

                // --- Buat penjelasan detail prediksi masa depan secara statis ---
                if (isset($data['timeLabelsMonthlyFuture']) && isset($data['predictionsMonthlyFuture'])) {
                    $futureLabels = $data['timeLabelsMonthlyFuture'];
                    $futurePredictions = $data['predictionsMonthlyFuture'];
                    $yearlyFutureGroup = [];
                    foreach ($futureLabels as $index => $label) {
                        list($year, $month) = explode('-', $label);
                        $yearlyFutureGroup[$year][] = $futurePredictions[$index];
                    }
                    $aggregatedFuture = [];
                    foreach ($yearlyFutureGroup as $year => $values) {
                        $aggregatedFuture[$year] = array_sum($values) / count($values);
                    }
                    ksort($aggregatedFuture);
                    $detailedExplanationFuture = "";
                    $prevYear = null;
                    $prevValue = null;
                    foreach ($aggregatedFuture as $year => $value) {
                        if ($prevYear !== null) {
                            if ($value < $prevValue) {
                                $detailedExplanationFuture .= "Pada tahun $year, hasil panen diprediksi menurun dibandingkan tahun $prevYear. Hal ini kemungkinan disebabkan oleh penurunan curah hujan dan kelembapan yang tidak optimal, serta faktor luas area yang kurang mendukung. ";
                            } else {
                                $detailedExplanationFuture .= "Pada tahun $year, hasil panen diprediksi meningkat dibandingkan tahun $prevYear. Hal ini didukung oleh kondisi cuaca yang lebih baik dan peningkatan faktor pendukung seperti dosis pupuk. ";
                            }
                        }
                        $prevYear = $year;
                        $prevValue = $value;
                    }
                    $data['detailedExplanationFuture'] = $detailedExplanationFuture;
                } else {
                    $data['detailedExplanationFuture'] = "";
                }
                Session::put('analysis_data', $data);
                fclose($tempFile);
                return redirect()->route('results');
            }
            fclose($tempFile);
            return redirect()->back()->withErrors(['file' => 'Gagal memproses prediksi']);
        } catch (\Exception $e) {
            fclose($tempFile);
            return redirect()->back()->withErrors(['file' => 'Gagal memproses file: Kesalahan Internal ']);
        }
    }

    // Tampilkan halaman results dengan data analisis
    public function results()
    {
        $analysis = Session::get('analysis_data');
        if (!$analysis) {
            return redirect()->route('home')->withErrors(['file' => 'Data analisis tidak ditemukan.']);
        }
        return view('pages.results', compact('analysis'));
    }
}
