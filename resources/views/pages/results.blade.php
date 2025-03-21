@extends('layouts.app')

@section('content')
    <div class="min-h-screen flex flex-col">
        <div class="container mx-auto p-4">
            <h2 class="text-3xl font-bold text-center mb-6 text-white">Hasil Prediksi Panen</h2>

            <!-- Tombol Mode -->
            <div class="flex justify-between items-center mb-4">
                <div class="flex gap-3">
                    <button id="btnMonthlyHistorical" class="mode-btn inactive">Data Aktual (Bulan)</button>
                    <button id="btnMonthlyFuture" class="mode-btn inactive">Hasil Prediksi (Bulan)</button>
                    <button id="btnYearlyHistorical" class="mode-btn inactive">Data Aktual (Tahun)</button>
                    <button id="btnYearlyFuture" class="mode-btn inactive">Hasil Prediksi (Tahun)</button>
                </div>
                <button id="btnDownload"
                    class="px-4 py-2 rounded bg-indigo-600 text-white shadow hover:bg-indigo-700 transition flex items-center gap-2">
                    <span class="btn-text">Download PDF</span>
                    <svg id="loadingIcon" class="hidden animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg"
                        fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                </button>
            </div>

            <!-- Grafik: Container full width dengan horizontal scroll dan custom scrollbar -->
            <div id="chartContainer"
                class="overflow-x-auto border border-gray-200 dark:border-gray-700 rounded-lg p-2 bg-white shadow relative custom-scroll">
                <div class="min-w-[2200px]">
                    <canvas id="predictionChart"></canvas>
                </div>
            </div>

            <!-- Kesimpulan & Saran -->
            <div class="bg-emerald-50 dark:bg-gray-800 rounded-lg p-6 mt-6 shadow">
                <h3 class="text-xl font-bold mb-2 text-gray-700 dark:text-gray-300">Kesimpulan</h3>
                <p class="text-2xl text-emerald-600 dark:text-emerald-400">{{ $analysis['conclusion'] }}</p>
                <h3 class="text-xl font-bold mt-4 mb-2 text-gray-700 dark:text-gray-300">Saran</h3>
                <p class="text-lg text-gray-700 dark:text-gray-300">{{ $analysis['suggestion'] }}</p>
            </div>

            <!-- Penjelasan Detail Prediksi Masa Depan -->
            @if (isset($analysis['detailedExplanationFuture']) && $analysis['detailedExplanationFuture'] != '')
                <div class="bg-yellow-50 dark:bg-gray-700 rounded-lg p-6 mt-6 shadow">
                    <h3 class="text-xl font-bold mb-2 text-gray-700 dark:text-gray-300">Penjelasan Detail Prediksi Masa
                        Depan</h3>
                    <p class="text-lg text-gray-700 dark:text-gray-300">{{ $analysis['detailedExplanationFuture'] }}</p>
                </div>
            @endif

            <!-- Tombol Kembali -->

            <div class="flex justify-center mt-6">
                <a href="{{ route('home') }}"
                    class="group inline-flex items-center px-8 py-4 rounded-xl border-2 border-emerald-500 dark:border-emerald-400 text-emerald-600 dark:text-emerald-400 font-medium hover:bg-emerald-50 dark:hover:bg-emerald-900/20 transform transition-all duration-300 hover:-translate-y-1">
                    <span>Upload Lagi Yu</span>
                    <svg class="w-5 h-5 ml-2 transition-transform group-hover:translate-x-1" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            .mode-btn {
                padding: 0.5rem 1rem;
                border-radius: 0.375rem;
                transition: background-color 0.2s, color 0.2s;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            }

            .mode-btn.active {
                background-color: #10B981;
                color: #ffffff;
                cursor: default;
            }

            .mode-btn.inactive {
                background-color: #E5E7EB;
                color: #4B5563;
                opacity: 0.75;
                cursor: pointer;
            }

            .mode-btn.inactive:hover {
                opacity: 1;
            }

            .custom-scroll::-webkit-scrollbar {
                height: 8px;
            }

            .custom-scroll::-webkit-scrollbar-track {
                background: #f1f1f1;
            }

            .custom-scroll::-webkit-scrollbar-thumb {
                background: #a3a3a3;
                border-radius: 4px;
            }

            .custom-scroll::-webkit-scrollbar-thumb:hover {
                background: #888;
            }
        </style>
    @endpush

    @push('scripts')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Data dari API Flask (disimpan di session)
                const monthlyLabelsHistorical = {!! json_encode($analysis['timeLabelsMonthlyHistorical']) !!};
                const monthlyPredictionsHistorical = {!! json_encode($analysis['predictionsMonthlyHistorical']) !!};

                const monthlyLabelsFuture = {!! json_encode($analysis['timeLabelsMonthlyFuture']) !!};
                const monthlyPredictionsFuture = {!! json_encode($analysis['predictionsMonthlyFuture']) !!};

                const yearlyLabelsHistorical = {!! json_encode($analysis['timeLabelsYearlyHistorical']) !!};
                const yearlyPredictionsHistorical = {!! json_encode($analysis['predictionsYearlyHistorical']) !!};
                const yearlyActualHistorical = {!! json_encode($analysis['actualYearlyHistorical']) !!};

                const yearlyLabelsFuture = {!! json_encode($analysis['timeLabelsYearlyFuture'] ?? '[]') !!};
                const yearlyPredictionsFuture = {!! json_encode($analysis['predictionsYearlyFuture'] ?? '[]') !!};

                // Default mode: Bulanan Historis
                let currentMode = 'monthlyHistorical';
                const ctx = document.getElementById('predictionChart').getContext('2d');
                let myLineChart;

                function renderChart(mode) {
                    currentMode = mode;
                    let labels, predictions, actual;
                    if (mode === 'monthlyHistorical') {
                        labels = monthlyLabelsHistorical;
                        predictions = monthlyPredictionsHistorical;
                        actual = null;
                    } else if (mode === 'monthlyFuture') {
                        labels = monthlyLabelsFuture;
                        predictions = monthlyPredictionsFuture;
                        actual = null;
                    } else if (mode === 'yearlyHistorical') {
                        labels = yearlyLabelsHistorical;
                        predictions = yearlyPredictionsHistorical;
                        actual = yearlyActualHistorical;
                    } else if (mode === 'yearlyFuture') {
                        labels = yearlyLabelsFuture;
                        predictions = yearlyPredictionsFuture;
                        actual = null;
                    }

                    // Sortir label untuk mode bulanan
                    if (mode === 'monthlyHistorical' || mode === 'monthlyFuture') {
                        labels.sort((a, b) => a.localeCompare(b));
                    }

                    // Atur ukuran canvas
                    document.getElementById('predictionChart').width = (mode === 'yearlyHistorical' || mode ===
                        'yearlyFuture') ? window.innerWidth - 40 : 2000;
                    document.getElementById('predictionChart').height = 500;

                    if (myLineChart) {
                        myLineChart.destroy();
                    }

                    myLineChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: labels,
                            datasets: [{
                                    label: 'Prediksi',
                                    data: predictions,
                                    backgroundColor: 'rgba(75,192,192,0.2)',
                                    borderColor: 'rgba(75,192,192,1)',
                                    borderWidth: 2,
                                    pointRadius: 3,
                                    fill: true,
                                    lineTension: 0.3
                                },
                                (actual ? [{
                                    label: 'Aktual',
                                    data: actual,
                                    backgroundColor: 'rgba(255,99,132,0.2)',
                                    borderColor: 'rgba(255,99,132,1)',
                                    borderWidth: 2,
                                    pointRadius: 3,
                                    fill: true,
                                    lineTension: 0.3
                                }] : [])
                            ]
                        },
                        options: {
                            responsive: false,
                            maintainAspectRatio: false,
                            scales: {
                                xAxes: [{
                                    scaleLabel: {
                                        display: true,
                                        labelString: (mode === 'yearlyHistorical' || mode ===
                                            'yearlyFuture') ? 'Tahun' : 'Bulan'
                                    },
                                    ticks: {
                                        callback: function(value) {
                                            if (mode === 'yearlyHistorical' || mode ===
                                                'yearlyFuture') {
                                                return value;
                                            } else {
                                                const parts = value.split('-');
                                                if (parts.length === 2) {
                                                    const year = parts[0];
                                                    const month = parts[1];
                                                    const monthNames = ['Januari', 'Februari',
                                                        'Maret', 'April', 'Mei', 'Juni', 'Juli',
                                                        'Agustus', 'September', 'Oktober',
                                                        'November', 'Desember'
                                                    ];
                                                    const mIndex = parseInt(month, 10) - 1;
                                                    return monthNames[mIndex] + ' ' + year;
                                                }
                                                return value;
                                            }
                                        }
                                    }
                                }],
                                yAxes: [{
                                    scaleLabel: {
                                        display: true,
                                        labelString: 'Hasil Panen (Kwintal/ha)'
                                    }
                                }]
                            },
                            tooltips: {
                                callbacks: {
                                    label: function(tooltipItem, data) {
                                        const datasetLabel = data.datasets[tooltipItem.datasetIndex]
                                            .label || '';
                                        return datasetLabel + ': ' + tooltipItem.yLabel;
                                    }
                                }
                            }
                        }
                    });
                }

                // Inisialisasi chart dengan mode default (Bulanan Historis)
                renderChart('monthlyHistorical');

                // Tombol switch mode
                const btnMonthlyHistorical = document.getElementById('btnMonthlyHistorical');
                const btnMonthlyFuture = document.getElementById('btnMonthlyFuture');
                const btnYearlyHistorical = document.getElementById('btnYearlyHistorical');
                const btnYearlyFuture = document.getElementById('btnYearlyFuture');

                function setActive(activeBtn, ...inactiveBtns) {
                    activeBtn.classList.add('active');
                    activeBtn.classList.remove('inactive');
                    activeBtn.disabled = true;
                    inactiveBtns.forEach(btn => {
                        btn.classList.remove('active');
                        btn.classList.add('inactive');
                        btn.disabled = false;
                    });
                }

                btnMonthlyHistorical.addEventListener('click', function() {
                    renderChart('monthlyHistorical');
                    setActive(btnMonthlyHistorical, btnMonthlyFuture, btnYearlyHistorical, btnYearlyFuture);
                });
                btnMonthlyFuture.addEventListener('click', function() {
                    renderChart('monthlyFuture');
                    setActive(btnMonthlyFuture, btnMonthlyHistorical, btnYearlyHistorical, btnYearlyFuture);
                });
                btnYearlyHistorical.addEventListener('click', function() {
                    renderChart('yearlyHistorical');
                    setActive(btnYearlyHistorical, btnMonthlyHistorical, btnMonthlyFuture, btnYearlyFuture);
                });
                btnYearlyFuture.addEventListener('click', function() {
                    renderChart('yearlyFuture');
                    setActive(btnYearlyFuture, btnMonthlyHistorical, btnMonthlyFuture, btnYearlyHistorical);
                });

                // Set initial state
                setActive(btnMonthlyHistorical, btnMonthlyFuture, btnYearlyHistorical, btnYearlyFuture);

                // Fungsi Download PDF
                document.getElementById('btnDownload').addEventListener('click', function() {
                    document.getElementById('loadingIcon').classList.remove('hidden');
                    document.querySelector('.btn-text').textContent = 'Memproses...';

                    const chartContainer = document.getElementById('chartContainer');
                    const width = chartContainer.scrollWidth;
                    const height = chartContainer.scrollHeight;

                    html2canvas(chartContainer, {
                        scale: 2,
                        width: width,
                        height: height,
                        scrollY: -window.scrollY
                    }).then(canvas => {
                        const imgData = canvas.toDataURL('image/png');
                        const {
                            jsPDF
                        } = window.jspdf;
                        const pdf = new jsPDF('landscape', 'mm', 'a4');
                        const imgProps = pdf.getImageProperties(imgData);
                        const pdfWidth = pdf.internal.pageSize.getWidth();
                        const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;
                        if (pdfHeight > pdf.internal.pageSize.getHeight()) {
                            let position = 0;
                            while (position < pdfHeight) {
                                pdf.addImage(imgData, 'PNG', 0, position, pdfWidth, pdfHeight);
                                position += pdf.internal.pageSize.getHeight();
                                if (position < pdfHeight) pdf.addPage();
                            }
                        } else {
                            pdf.addImage(imgData, 'PNG', 0, 0, pdfWidth, pdfHeight);
                        }
                        pdf.save('hasil_prediksi_panen.pdf');
                        document.getElementById('loadingIcon').classList.add('hidden');
                        document.querySelector('.btn-text').textContent = 'Download PDF';
                    });
                });
            });
        </script>
    @endpush
@endsection
