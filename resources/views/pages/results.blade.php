{{-- results.blade.php --}}
@extends('layouts.app')

@section('content')
    <div class="bg-gray-50 min-h-screen py-8">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Back navigation -->
            <div class="mb-6">
                <div class="mt-6 mb-6" style="padding-left: 10px; width: 100%; text-align: start !important">
                    <a href="{{ route('home') }}" class="text-gray-500 hover:text-gray-700">
                        ← Kembali ke halaman upload
                    </a>
                </div>
            </div>

            <!-- Main content card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">

                <!-- Error messages -->
                @if (session('error'))
                    <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 m-6" role="alert">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm">{{ session('error') }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                @if (isset($analysis))
                    <div class="p-6">
                        <!-- Main info cards -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                            <div
                                class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl shadow-sm p-6 border border-blue-200">
                                <div class="flex items-center mb-4">
                                    <div class="p-2 bg-blue-500 text-gray-800 rounded-lg mr-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                        </svg>
                                    </div>
                                    <h2 class="text-xl font-semibold text-gray-800">Kesimpulan Analisis</h2>
                                </div>
                                <p class="text-gray-700 leading-relaxed">
                                    {{ $analysis['conclusion'] ?? 'Tidak ada kesimpulan analisis.' }}</p>
                            </div>

                            <div
                                class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl shadow-sm p-6 border border-green-200">
                                <div class="flex items-center mb-4">
                                    <div class="p-2 bg-green-500 text-gray-800 rounded-lg mr-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M13 10V3L4 14h7v7l9-11h-7z" />
                                        </svg>
                                    </div>
                                    <h2 class="text-xl font-semibold text-gray-800">Saran dan Rekomendasi</h2>
                                </div>
                                <p class="text-gray-700 leading-relaxed">
                                    {{ $analysis['suggestion'] ?? 'Tidak ada rekomendasi.' }}</p>
                            </div>
                        </div>

                        <!-- Detailed explanation card -->
                        <div class="bg-white mt-4 mb-6 rounded-xl shadow-sm p-6 border border-gray-200 ">
                            <div class="flex items-center mb-4">
                                <div class="p-2 bg-purple-600 text-gray-800 rounded-lg mr-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <h2 class="text-xl font-semibold text-gray-800">Penjelasan Detail</h2>
                            </div>
                            <p class="text-gray-700 leading-relaxed">
                                {{ $analysis['detailedExplanationFuture'] ?? 'Tidak ada penjelasan detail.' }}</p>
                        </div>

                        @if (isset($analysis['timeLabelsYearlyFuture']) && isset($analysis['predictionsYearlyFuture']))
                            <!-- Chart section -->
                            <div class="mb-8">
                                <div class="flex items-center mb-4 gap-3">
                                    <div class="p-2 bg-indigo-600 text-white rounded-lg mr-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                                        </svg>
                                    </div>
                                    <h2 class="text-xl font-semibold text-gray-800">Grafik Prediksi</h2>
                                </div>
                                <div class="bg-white rounded-xl shadow-sm p-4 md:p-6 border border-gray-200">
                                    <div class="h-80">
                                        <canvas id="predictionChart" style="height: 100vh !important"></canvas>
                                    </div>
                                </div>
                            </div>

                            <!-- Table section -->
                            <div class="mb-8">
                                <div class="flex items-center mb-4 mt-6">
                                    <div class="p-2 bg-yellow-600 text-gray-800 rounded-lg mr-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <h2 class="text-xl font-semibold text-gray-800">Data Prediksi Detail</h2>
                                </div>
                                <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-200">
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full divide-y divide-gray-200">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th scope="col"
                                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Periode
                                                    </th>
                                                    <th scope="col"
                                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Lokasi
                                                    </th>
                                                    <th scope="col"
                                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Prediksi Hasil Panen (Kwintal/Ha)
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-gray-200">
                                                @foreach ($analysis['timeLabelsYearlyLocationFuture'] as $index => $yearLocation)
                                                    @php
                                                        [$year, $location] = explode(' - ', $yearLocation);
                                                    @endphp
                                                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                                                        <td
                                                            class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                            {{ $year }}
                                                        </td>
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                                            {{ $location }}
                                                        </td>
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                                            <span class="font-medium">
                                                                {{ number_format($analysis['predictionsYearlyLocationFuture'][$index] ?? 0, 2) }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Export buttons -->
                            <div class="flex flex-wrap gap-4 mt-8">
                                <a href="{{ route('export.pdf') }}"
                                    class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-gray-500"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                    Download PDF
                                </a>
                                <a href="{{ route('export.excel') }}"
                                    class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-gray-500"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    Export Excel
                                </a>
                                <button onclick="shareResults()" type="button"
                                    class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-gray-500"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                                    </svg>
                                    Bagikan
                                </button>
                            </div>

                            <!-- Add Share Results Script -->
                            <script>
                                function shareResults() {
                                    fetch('{{ route('share.results') }}', {
                                            method: 'POST',
                                            headers: {
                                                'Content-Type': 'application/json',
                                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                            }
                                        })
                                        .then(response => response.json())
                                        .then(data => {
                                            if (data.success) {
                                                // Create a temporary input to copy the URL
                                                const input = document.createElement('input');
                                                input.value = data.shareableUrl;
                                                document.body.appendChild(input);
                                                input.select();
                                                document.execCommand('copy');
                                                document.body.removeChild(input);

                                                alert('Link berhasil disalin ke clipboard!');
                                            } else {
                                                alert('Gagal membuat link berbagi.');
                                            }
                                        })
                                        .catch(error => {
                                            console.error('Error:', error);
                                            alert('Terjadi kesalahan saat membuat link berbagi.');
                                        });
                                }
                            </script>
                        @else
                            <!-- No data available -->
                            <div class="bg-gray-50 rounded-xl p-8 text-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-400 mb-4"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p class="text-gray-600 text-lg">Tidak ada data prediksi yang tersedia.</p>
                            </div>
                        @endif
                    </div>
                @else
                    <!-- No analysis available -->
                    <div class="flex flex-col items-center justify-center p-12">
                        <div class="text-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-400 mb-4"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak ada hasil analisis</h3>
                            <p class="text-gray-500 mb-6">Tidak ada hasil analisis yang tersedia. Silahkan upload data
                                untuk memulai analisis.</p>
                            <a href="{{ route('home') }}"
                                class="inline-flex items-center px-4 py-2 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-blue-500 hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Kembali ke Halaman Utama
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @if (isset($analysis['timeLabelsYearlyFuture']) && isset($analysis['predictionsYearlyFuture']))
        @push('scripts')
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <script>
                // 
                document.addEventListener('DOMContentLoaded', function() {
                    const ctx = document.getElementById('predictionChart').getContext('2d');

                    // Process data to group by location
                    const locationData = {};
                    const yearLocationLabels = @json($analysis['timeLabelsYearlyLocationFuture']);
                    const predictions = @json($analysis['predictionsYearlyLocationFuture']);
                    const years = [...new Set(yearLocationLabels.map(yl => yl.split(' - ')[0]))];

                    yearLocationLabels.forEach((yearLocation, index) => {
                        const [year, location] = yearLocation.split(' - ');
                        if (!locationData[location]) {
                            locationData[location] = {
                                data: Array(years.length).fill(null),
                                label: location
                            };
                        }
                        const yearIndex = years.indexOf(year);
                        locationData[location].data[yearIndex] = predictions[index];
                    });

                    // Generate colors for each location
                    const colors = [{
                            border: 'rgb(37, 99, 235)',
                            background: 'rgba(59, 130, 246, 0.5)'
                        },
                        {
                            border: 'rgb(220, 38, 38)',
                            background: 'rgba(248, 113, 113, 0.5)'
                        },
                        {
                            border: 'rgb(5, 150, 105)',
                            background: 'rgba(16, 185, 129, 0.5)'
                        },
                        {
                            border: 'rgb(124, 58, 237)',
                            background: 'rgba(167, 139, 250, 0.5)'
                        },
                        {
                            border: 'rgb(245, 158, 11)',
                            background: 'rgba(251, 191, 36, 0.5)'
                        }
                    ];

                    // Create datasets for each location
                    const datasets = Object.entries(locationData).map(([location, data], index) => {
                        const colorIndex = index % colors.length;
                        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
                        gradient.addColorStop(0, colors[colorIndex].background);
                        gradient.addColorStop(1, 'rgba(255, 255, 255, 0)');

                        return {
                            label: `Prediksi ${location}`,
                            data: data.data,
                            borderWidth: 2,
                            borderColor: colors[colorIndex].border,
                            backgroundColor: gradient,
                            pointBackgroundColor: colors[colorIndex].border,
                            pointBorderColor: 'white',
                            pointBorderWidth: 2,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            tension: 0.4,
                            fill: true
                        };
                    });

                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: years,
                            datasets: datasets
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            interaction: {
                                intersect: false,
                                mode: 'index',
                            },
                            plugins: {
                                legend: {
                                    position: 'top',
                                    labels: {
                                        font: {
                                            size: 12,
                                            weight: 'bold'
                                        },
                                        padding: 15,
                                        usePointStyle: true,
                                        pointStyle: 'circle'
                                    }
                                },
                                tooltip: {
                                    backgroundColor: 'rgba(17, 24, 39, 0.9)',
                                    padding: 12,
                                    titleFont: {
                                        size: 14,
                                        weight: 'bold'
                                    },
                                    bodyFont: {
                                        size: 13
                                    },
                                    borderColor: 'rgba(255, 255, 255, 0.2)',
                                    borderWidth: 1,
                                    callbacks: {
                                        label: function(context) {
                                            return `${context.dataset.label}: ${context.raw?.toFixed(2) ?? 'N/A'} Kwintal/Ha`;
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: false,
                                    grid: {
                                        display: true,
                                        drawBorder: true,
                                        color: 'rgba(226, 232, 240, 0.6)'
                                    },
                                    ticks: {
                                        font: {
                                            size: 12
                                        },
                                        callback: function(value) {
                                            return value.toFixed(1);
                                        }
                                    },
                                    title: {
                                        display: true,
                                        text: 'Hasil Panen (Kwintal/Ha)',
                                        font: {
                                            size: 14,
                                            weight: 'bold'
                                        },
                                        padding: {
                                            bottom: 10
                                        }
                                    }
                                },
                                x: {
                                    grid: {
                                        display: false
                                    },
                                    ticks: {
                                        font: {
                                            size: 12
                                        }
                                    },
                                    title: {
                                        display: true,
                                        text: 'Periode',
                                        font: {
                                            size: 14,
                                            weight: 'bold'
                                        },
                                        padding: {
                                            top: 10
                                        }
                                    }
                                }
                            }
                        }
                    });
                });
            </script>
        @endpush
    @endif
@endsection
