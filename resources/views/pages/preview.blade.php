@extends('layouts.app')

@section('content')
    <div class="min-h-screen flex flex-col items-center justify-center">
        <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-8 w-full max-w-4xl">
            {{-- <h2 class="text-2xl font-bold mb-4 text-center">Preview Data CSV (5 Baris Pertama)</h2> --}}
            <div
                class="p-4 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Preview Data CSV</h3>
                    <span
                        class="px-3 py-1 text-sm text-blue-600 dark:text-blue-400 bg-blue-100 dark:bg-blue-900/40 rounded-full">
                        5 Baris Pertama
                    </span>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full border-collapse mb-6">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            @foreach ($headers as $header)
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    {{ $header }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($rows as $row)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200">
                                @foreach ($row as $cell)
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">
                                        {{ $cell }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <form style="margin: 20px ; display: flex; justify-content: center; align-items: center;"
                action="{{ route('analyze') }}" method="POST" id="analyze-form">
                @csrf
                <button type="button" id="process-button"
                    class="flex items-center hover:bg-emerald-700 transition btn-primary px-4 py-2 bg-emerald-600 text-white rounded-lg disabled:opacity-50"
                    onclick="startProcessing()">
                    Proses Prediksi

                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3">
                        </path>
                    </svg>
                </button>

                <div id="processing-indicator" class="hidden flex flex-col items-center justify-center">
                    <div
                        class="flex items-center hover:bg-emerald-700 transition btn-primary px-4 py-2 bg-emerald-600 text-white rounded-lg disabled:opacity-50">
                        Memproses data...
                        <svg class="w-5 h-5 ml-2 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                    </div>
                </div>
            </form>

            @if ($errors->any())
                <div class="p-4 mt-4 bg-red-50 dark:bg-red-900/20 rounded border border-red-200 dark:border-red-800">
                    <p class="flex items-center text-red-600 dark:text-red-400 text-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>{{ $errors->first() }}</span>
                    </p>
                </div>
            @endif

            <div class="mt-6 text-center">
                <a href="{{ route('home') }}"
                    class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                    ← Kembali ke halaman upload
                </a>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function startProcessing() {
                document.getElementById('process-button').classList.add('hidden');
                document.getElementById('processing-indicator').classList.remove('hidden');

                // Submit form setelah menampilkan loading indicator
                setTimeout(function() {
                    document.getElementById('analyze-form').submit();
                }, 500);
            }
        </script>
    @endpush
@endsection
