@extends('layouts.app')

@section('content')
    <div class="min-h-screen flex flex-col">
        <div class="container mx-auto p-4">
            <h2 class="text-3xl font-bold text-center mb-6 text-white">Hasil Prediksi Panen</h2>

            <!-- Tombol Mode & Download PDF -->
            <div class="flex justify-between items-center mb-4">
                <div class="flex gap-3">
                    <button id="btnMonthly" class="mode-btn inactive">Mode Bulanan</button>
                    <button id="btnYearly" class="mode-btn inactive">Mode Tahunan</button>
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

            <!-- Tombol Kembali -->
            <div class="flex justify-center mt-6">
                <a href="{{ route('home') }}" class="px-4 py-2 border bg-indigo-600 text-white shadow hover:bg-indigo-700 border-gray-300 rounded  transition">
                    Upload File Lain
                </a>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            /* Tombol mode */
            .mode-btn {
                padding: 0.5rem 1rem;
                border-radius: 0.375rem;
                transition: background-color 0.2s, color 0.2s;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            }

            .mode-btn.active {
                background-color: #10B981;
                /* hijau emerald */
                color: #ffffff;
                cursor: default;
            }

            .mode-btn.inactive {
                background-color: #E5E7EB;
                /* abu muda */
                color: #4B5563;
                /* abu tua */
                opacity: 0.75;
                cursor: pointer;
            }

            .mode-btn.inactive:hover {
                opacity: 1;
            }

            /* Custom Scrollbar untuk container grafik */
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
                // Data dari API Flask (dari session)
                let monthlyLabels = {!! json_encode($analysis['timeLabelsMonthly']) !!};
                const monthlyPredictions = {!! json_encode($analysis['predictionsMonthly']) !!};
                const monthlyActual = {!! json_encode($analysis['actualMonthly']) !!};

                const yearlyLabels = {!! json_encode($analysis['timeLabelsYearly']) !!};
                const yearlyPredictions = {!! json_encode($analysis['predictionsYearly']) !!};
                const yearlyActual = {!! json_encode($analysis['actualYearly']) !!};

                // Urutkan data monthly secara ascending (misal "2021-01", "2021-02", dst.)
                monthlyLabels.sort((a, b) => a.localeCompare(b));

                let currentMode = 'monthly';
                const ctx = document.getElementById('predictionChart').getContext('2d');

                function renderChart(mode) {
                    currentMode = mode;
                    let labels = mode === 'yearly' ? yearlyLabels : monthlyLabels;
                    let predictions = mode === 'yearly' ? yearlyPredictions : monthlyPredictions;
                    let actual = mode === 'yearly' ? yearlyActual : monthlyActual;

                    // Atur ukuran canvas: untuk mode yearly, gunakan lebar penuh window; untuk mode monthly, lebar manual (2000px)
                    document.getElementById('predictionChart').width = mode === 'yearly' ? window.innerWidth - 40 :
                    2000;
                    document.getElementById('predictionChart').height = 500;

                    if (window.myLineChart) {
                        window.myLineChart.destroy();
                    }

                    window.myLineChart = new Chart(ctx, {
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
                                ...(actual ? [{
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
                                        labelString: mode === 'yearly' ? 'Tahun' : 'Bulan'
                                    },
                                    ticks: {
                                        callback: function(value) {
                                            if (mode === 'yearly') {
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

                // Inisialisasi chart dengan mode default (monthly)
                renderChart('monthly');

                // Tombol switch mode
                const btnMonthly = document.getElementById('btnMonthly');
                const btnYearly = document.getElementById('btnYearly');

                function setActive(btnActive, btnInactive) {
                    btnActive.classList.add('active');
                    btnInactive.classList.remove('active');
                    btnActive.classList.remove('inactive');
                    btnInactive.classList.add('inactive');
                    btnActive.disabled = true;
                    btnInactive.disabled = false;
                }

                btnMonthly.addEventListener('click', function() {
                    renderChart('monthly');
                    setActive(btnMonthly, btnYearly);
                });
                btnYearly.addEventListener('click', function() {
                    renderChart('yearly');
                    setActive(btnYearly, btnMonthly);
                });

                // Set initial state
                setActive(btnMonthly, btnYearly);

                // Fungsi Download PDF: tangkap seluruh area grafik (meskipun tidak terlihat karena scroll)
                document.getElementById('btnDownload').addEventListener('click', function() {
                    // Tampilkan loading icon
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
                        // Sembunyikan loading icon dan reset tombol
                        document.getElementById('loadingIcon').classList.add('hidden');
                        document.querySelector('.btn-text').textContent = 'Download PDF';
                    });
                });
            });
        </script>
    @endpush
@endsection
