<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PDF; // Make sure you have installed laravel-dompdf

class SiteController extends Controller
{
    public function home()
    {
        return view('pages.home');
    }

    // Perbaikan fungsi upload di SiteController.php
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx,xls|max:2048'
        ]);

        try {
            $file = $request->file('file');
            $extension = $file->getClientOriginalExtension();
            $fileName = time() . '.' . $extension;

            // Simpan file dengan cara yang lebih aman
            $filePath = $file->getRealPath();

            // Verifikasi file original sebelum diproses
            if (!file_exists($filePath)) {
                throw new \Exception("File upload tidak valid: {$filePath}");
            }

            // Pindahkan ke direktori storage
            $storagePath = storage_path('app/uploads');
            if (!file_exists($storagePath)) {
                mkdir($storagePath, 0755, true);
            }

            $destinationPath = $storagePath . '/' . $fileName;
            copy($filePath, $destinationPath);

            // Verifikasi file berhasil disalin
            if (!file_exists($destinationPath)) {
                throw new \Exception("Gagal menyalin file ke: {$destinationPath}");
            }

            Log::info("File berhasil disimpan di: {$destinationPath}");

            // Read file content based on extension
            if ($extension === 'csv') {
                $data = array_map('str_getcsv', file($destinationPath));
            } else {
                $spreadsheet = IOFactory::load($destinationPath);
                $worksheet = $spreadsheet->getActiveSheet();
                $data = $worksheet->toArray(null, true, true, true);

                // Transform associative array to indexed array for consistency
                $indexedData = [];
                foreach ($data as $row) {
                    $indexedData[] = array_values($row);
                }
                $data = $indexedData;
            }

            // Ensure we have data
            if (empty($data)) {
                throw new \Exception('File tidak memiliki data.');
            }

            Log::info("Data berhasil dibaca dari file: " . count($data) . " baris");

            // Standardize column names (first row)
            $possibleResultColumns = ['Hasil_Panen_Kw', 'Hasil_Panen (kwintal/ha)', 'Hasil_Panen'];
            $headers = $data[0];

            // Check if we need to rename the result column
            foreach ($possibleResultColumns as $colName) {
                $index = array_search($colName, $headers);
                if ($index !== false && $colName !== 'Hasil_Panen_Kw') {
                    $headers[$index] = 'Hasil_Panen_Kw';
                    $data[0] = $headers;
                    break;
                }
            }

            // Store data in session
            Session::put('uploaded_data', $data);
            Session::put('file_name', $fileName);
            Session::put('file_path', $destinationPath);
            Session::put('file_extension', $extension);

            return redirect()->route('preview');
        } catch (\Exception $e) {
            Log::error('Error uploading file: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengunggah file: ' . $e->getMessage());
        }
    }

    public function preview()
    {
        $data = Session::get('uploaded_data');
        $fileName = Session::get('file_name');
        $destinationPath = Session::get('file_path');
        $exFile = Session::get('file_extention');

        if (!$data || !$fileName) {
            return redirect()->route('home')->with('error', 'Silakan unggah file terlebih dahulu.');
        }

        return view('pages.preview', compact('data', 'fileName', 'destinationPath', 'exFile'));
    }

    public function analyze()
    {
        $data = Session::get('uploaded_data');
        $fileName = Session::get('file_name');
        $filePath = Session::get('file_path');
        $fileExtension = Session::get('file_extension');

        if (!$data || !$fileName) {
            return redirect()->route('home')->with('error', 'Silakan unggah file terlebih dahulu.');
        }

        try {
            $apiUrl = config('app.python_api_url', 'http://localhost:5000');
            Log::info('Mencoba terhubung ke API Python di: ' . $apiUrl);

            // Check if API is running with increased timeout
            try {
                $pingResponse = Http::timeout(20)->get($apiUrl);
                Log::info('Ping API Python: ' . $pingResponse->status());

                if (!$pingResponse->successful()) {
                    Log::error('API Python tidak merespons: ' . $pingResponse->status());
                    return redirect()->back()->with('error', 'API Python tidak tersedia. Silakan coba lagi nanti.');
                }
            } catch (\Exception $e) {
                Log::error('API Python tidak dapat dijangkau: ' . $e->getMessage());
                return redirect()->back()->with('error', 'API Python tidak dapat dijangkau. Silakan periksa koneksi atau coba lagi nanti.');
            }

            // Prepare headers
            $headers = $data[0];

            // Ensure we have the required columns
            $requiredBaseColumns = ['Tahun', 'Bulan'];
            $possibleResultColumns = ['Hasil_Panen_Kw', 'Hasil_Panen (kwintal/ha)', 'Hasil_Panen'];

            // Check base columns
            $missingBaseColumns = array_diff($requiredBaseColumns, $headers);
            if (!empty($missingBaseColumns)) {
                Log::error('Kolom dasar yang diperlukan tidak ditemukan: ' . implode(', ', $missingBaseColumns));
                return redirect()->back()->with('error', 'Format data tidak sesuai. Silakan gunakan template yang disediakan.');
            }

            // Check result column
            $resultColumnExists = false;
            $resultColumnName = '';
            foreach ($possibleResultColumns as $colName) {
                if (in_array($colName, $headers)) {
                    $resultColumnExists = true;
                    $resultColumnName = $colName;
                    break;
                }
            }

            if (!$resultColumnExists) {
                Log::error('Kolom hasil panen tidak ditemukan. Dibutuhkan salah satu dari: ' . implode(', ', $possibleResultColumns));
                return redirect()->back()->with('error', 'Format data tidak sesuai. Kolom hasil panen tidak ditemukan.');
            }

            // Standardize result column name if needed
            if ($resultColumnName !== 'Hasil_Panen_Kw') {
                $index = array_search($resultColumnName, $headers);
                if ($index !== false) {
                    $headers[$index] = 'Hasil_Panen_Kw';
                    Log::info("Mengubah nama kolom dari '{$resultColumnName}' menjadi 'Hasil_Panen_Kw'");
                }
            }

            // Initialize response variable
            $response = null;

            // Process based on file type
            if (file_exists($filePath) && in_array($fileExtension, ['xlsx', 'xls'])) {
                // For Excel files, send the original file to the API
                Log::info('Mengirim file Excel original ke API Python: ' . $apiUrl . '/predict');

                $contentType = ($fileExtension === 'xlsx')
                    ? 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                    : 'application/vnd.ms-excel';

                // Use a longer timeout for processing Excel files
                $response = Http::timeout(120)
                    ->withHeaders([
                        'Accept' => 'application/json'
                    ])
                    ->attach(
                        'file',
                        fopen($filePath, 'r'),
                        'data.' . $fileExtension,
                        ['Content-Type' => $contentType]
                    )->post($apiUrl . '/predict');
            } else {
                // For CSV or if original file doesn't exist, use CSV approach
                // Prepare CSV content from data
                $csvContent = implode(',', $headers) . "\n";
                $rows = array_slice($data, 1); // Skip header row

                foreach ($rows as $row) {
                    // Ensure row has the right number of columns
                    while (count($row) < count($headers)) {
                        $row[] = ''; // Pad with empty values if needed
                    }

                    // Properly escape and format CSV values
                    $escapedRow = array_map(function ($value) {
                        // Convert non-string values to string
                        $value = (string)$value;
                        return '"' . str_replace('"', '""', $value) . '"';
                    }, $row);

                    $csvContent .= implode(',', $escapedRow) . "\n";
                }

                // Save as temporary CSV file to send to API
                $tempCsvPath = storage_path('app/uploads/temp_' . time() . '.csv');
                file_put_contents($tempCsvPath, $csvContent);

                // Log for debugging (limited to avoid huge logs)
                Log::info('CSV Content untuk API (sample): ' . substr($csvContent, 0, 500) . '...');

                // Send CSV file with multi-part form and increased timeout
                try {
                    $response = Http::timeout(120)
                        ->withHeaders([
                            'Accept' => 'application/json'
                        ])
                        ->attach(
                            'file',
                            fopen($tempCsvPath, 'r'),
                            'data.csv',
                            ['Content-Type' => 'text/csv']
                        )->post($apiUrl . '/predict');

                    // Delete temporary file after sending
                    @unlink($tempCsvPath);
                } catch (\Exception $e) {
                    Log::error('Error sending CSV to API: ' . $e->getMessage());
                    return redirect()->back()->with('error', 'Gagal mengirim data ke API: ' . $e->getMessage());
                }
            }

            // Check if response exists
            if (!$response) {
                Log::error('Tidak ada respons dari API');
                return redirect()->back()->with('error', 'Tidak ada respons dari API. Silakan coba lagi.');
            }

            Log::info('Response status dari API Python: ' . $response->status());

            // Process response
            if ($response->successful()) {
                // Get the raw response body
                $responseBody = $response->body();

                // Tambahkan di controller sebelum parsing JSON
                Log::info('INI Raw response from API: ' . $responseBody);

                // Log truncated response to avoid overwhelming logs
                $logLength = min(strlen($responseBody), 1000);
                Log::info('Raw response (truncated): ' . substr($responseBody, 0, $logLength) . (strlen($responseBody) > $logLength ? '...[truncated]' : ''));

                try {
                    // Attempt to decode the JSON with error handling
                    $analysis = json_decode($responseBody, true, 512, JSON_THROW_ON_ERROR);

                    // Log the type and structure of the decoded response
                    Log::info('Response structure keys: ' . json_encode(array_keys($analysis)));

                    // Extract data based on the response structure
                    if (isset($analysis['data'])) {
                        Log::info('Using nested data structure');
                        Session::put('analysis', $analysis['data']);
                    } elseif (isset($analysis['status']) && $analysis['status'] === 'success') {
                        Log::info('Using direct data structure');
                        Session::put('analysis', $analysis);
                    } else {
                        Log::warning('Unexpected response structure');
                        Session::put('analysis', $analysis);
                    }

                    return redirect()->route('results');
                } catch (\JsonException $e) {
                    Log::error('JSON decode error: ' . $e->getMessage());
                    Log::error('JSON content causing error: ' . substr($responseBody, 0, 500));
                    return redirect()->back()->with('error', 'Format JSON tidak valid: ' . $e->getMessage());
                }
            } else {
                // Handle unsuccessful response
                $statusCode = $response->status();
                $errorBody = $response->body();
                Log::error("API response unsuccessful (status $statusCode): " . substr($errorBody, 0, 500));

                try {
                    $errorJson = json_decode($errorBody, true, 512, JSON_THROW_ON_ERROR);
                    $errorMessage = $errorJson['message'] ?? $errorJson['error'] ?? "Gagal memproses prediksi (status $statusCode)";
                } catch (\JsonException $e) {
                    $errorMessage = "Gagal memproses prediksi. Response tidak valid (status $statusCode).";
                }

                Log::error('Error message to display: ' . $errorMessage);
                return redirect()->back()->with('error', $errorMessage);
            }
        } catch (\Exception $e) {
            Log::error('Error analyzing data: ' . $e->getMessage());
            Log::error('Error stack trace: ' . $e->getTraceAsString());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menganalisis data: ' . $e->getMessage());
        }
    }

    public function results()
    {
        $analysis = Session::get('analysis');

        if (!$analysis) {
            return redirect()->route('home')->with('error', 'Silakan lakukan analisis terlebih dahulu.');
        }

        return view('pages.results', compact('analysis'));
    }

    public function downloadTemplate()
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Add headers sesuai format yang diharapkan API Python
            $headers = [
                'Tahun',
                'Bulan',
                'Suhu',
                'Curah_Hujan',
                'Kelembapan',
                'Dosis_Pupuk',
                'Umur_Tanaman',
                'Luas_Lahan',
                'Hasil_Panen_Kw', // Ini lebih sesuai dengan yang diharapkan oleh API
                'Lokasi',
                'Area',
                'Pelepah_Terkena_Penyakit'
            ];
            $sheet->fromArray($headers, NULL, 'A1');

            // Add sample data
            $sampleData = [
                [2023, 1, 28.5, 150.0, 75.0, 100.0, 12, 2.5, 10.5, 'Kebun A', 2500, 0.5],
                [2023, 2, 29.0, 120.0, 70.0, 100.0, 12, 2.5, 11.0, 'Kebun A', 2500, 0.3],
                [2023, 3, 30.0, 100.0, 65.0, 100.0, 12, 2.5, 10.8, 'Kebun A', 2500, 0.4],
            ];
            $sheet->fromArray($sampleData, NULL, 'A2');

            // Style the sheet
            $sheet->getStyle('A1:L1')->getFont()->setBold(true);
            $sheet->getStyle('A1:L5')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            // Auto-size columns
            foreach (range('A', 'L') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            // Create Excel file
            $writer = new Xlsx($spreadsheet);
            $fileName = 'template_data_panen.xlsx';
            $filePath = storage_path('app/templates/' . $fileName);

            // Ensure directory exists
            if (!file_exists(storage_path('app/templates'))) {
                mkdir(storage_path('app/templates'), 0755, true);
            }

            $writer->save($filePath);

            return response()->download($filePath)->deleteFileAfterSend();
        } catch (\Exception $e) {
            Log::error('Error creating template: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal membuat template. Silakan coba lagi.');
        }
    }

    public function exportPDF()
    {
        $analysis = Session::get('analysis');

        if (!$analysis) {
            return redirect()->route('home')->with('error', 'No analysis data available.');
        }

        $pdf = PDF::loadView('pages.pdf', compact('analysis'));

        return $pdf->download('harvest-predictions.pdf');
    }

    public function exportExcel()
    {
        $analysis = Session::get('analysis');

        if (!$analysis) {
            return redirect()->route('home')->with('error', 'No analysis data available.');
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Headers
        $sheet->setCellValue('A1', 'Periode');
        $sheet->setCellValue('B1', 'Lokasi');
        $sheet->setCellValue('C1', 'Prediksi Hasil Panen (Kwintal/Ha)');

        // Data
        $row = 2;
        foreach ($analysis['timeLabelsYearlyLocationFuture'] as $index => $yearLocation) {
            [$year, $location] = explode(' - ', $yearLocation);

            $sheet->setCellValue('A' . $row, $year);
            $sheet->setCellValue('B' . $row, $location);
            $sheet->setCellValue('C' . $row, $analysis['predictionsYearlyLocationFuture'][$index] ?? 0);

            $row++;
        }

        // Style the sheet
        $sheet->getStyle('A1:C1')->getFont()->setBold(true);
        foreach (range('A', 'C') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'harvest-predictions-' . date('Y-m-d') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
    }

    public function shareResults(Request $request)
    {
        $analysis = Session::get('analysis');

        if (!$analysis) {
            return response()->json(['error' => 'No analysis data available.'], 404);
        }

        // Create a shareable link or process sharing logic
        $shareableUrl = route('results') . '?share=' . base64_encode(json_encode([
            'id' => uniqid(),
            'timestamp' => now()->timestamp
        ]));

        return response()->json([
            'success' => true,
            'shareableUrl' => $shareableUrl,
            'message' => 'Results ready to share'
        ]);
    }
}
