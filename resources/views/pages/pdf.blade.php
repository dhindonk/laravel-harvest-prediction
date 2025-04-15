{{-- pdf.blade.php --}}
<!DOCTYPE html>
<html>

    <head>
        <meta charset="utf-8">
        <title>Laporan Analisis Data</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                font-size: 12px;
                line-height: 1.4;
            }

            .header {
                text-align: center;
                margin-bottom: 30px;
                padding: 20px;
                border-bottom: 2px solid #333;
            }

            .header h1 {
                margin: 0;
                color: #333;
            }

            .header p {
                margin: 5px 0 0;
                color: #666;
            }

            .section {
                margin-bottom: 20px;
            }

            .section h2 {
                color: #333;
                border-bottom: 1px solid #ddd;
                padding-bottom: 5px;
            }

            table {
                width: 100%;
                border-collapse: collapse;
                margin: 20px 0;
            }

            th,
            td {
                border: 1px solid #ddd;
                padding: 8px;
                text-align: left;
            }

            th {
                background-color: #f5f5f5;
            }

            .footer {
                text-align: center;
                margin-top: 30px;
                padding-top: 20px;
                border-top: 1px solid #ddd;
                font-size: 10px;
                color: #666;
            }
        </style>
    </head>

    <body>
        <div class="header">
            <h1>Laporan Analisis Data</h1>
            <p>Tanggal: {{ date('d F Y') }}</p>
        </div>

        <div class="section">
            <h2>Kesimpulan Analisis</h2>
            <p>{{ $analysis['conclusion'] ?? 'Tidak ada kesimpulan' }}</p>
        </div>

        <div class="section">
            <h2>Prediksi Masa Depan</h2>
            <p>{{ $analysis['detailedExplanationFuture'] ?? 'Tidak ada prediksi' }}</p>
        </div>

        <div class="section">
            <h2>Data Prediksi</h2>
            <table>
                <thead>
                    <tr>
                        <th>Periode</th>
                        <th>Prediksi</th>
                    </tr>
                </thead>
                <tbody>
                    @if (isset($analysis['timeLabelsYearlyFuture']) && isset($analysis['predictionsYearlyFuture']))
                        @foreach ($analysis['timeLabelsYearlyFuture'] as $index => $label)
                            <tr>
                                <td>{{ $label }}</td>
                                <td>{{ number_format($analysis['predictionsYearlyFuture'][$index], 2) }}</td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>

        <div class="footer">
            <p>Dokumen ini dibuat secara otomatis oleh sistem.</p>
            <p>RivanPredict @2025</p>
        </div>
    </body>

</html>
