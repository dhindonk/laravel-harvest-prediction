@extends('layouts.app')

@section('content')
    <div class="min-h-screen flex flex-col items-center justify-center">
        <div class="mt-6 mb-6" style="padding-left: 100px; width: 100%; text-align: start !important">
            <a href="{{ route('home') }}" class="text-gray-500 hover:text-gray-700">
                ← Kembali ke halaman upload
            </a>
        </div>
        <div class="bg-white shadow-md rounded-lg p-8 w-full max-w-4xl">
            @if (session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif
            <div class="p-4 bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Preview Data CSV</h3>
                    <span class="px-3 py-1 text-sm text-blue-600 bg-blue-100 rounded-full">
                        <p class="mb-0">Nama file: {{ $fileName }}</p>
                    </span>
                    <span class="px-3 py-1 text-sm text-blue-600 bg-blue-100 rounded-full">
                        <p class="mb-0">Total baris: {{ count($data) - 1 }}</p>
                    </span>
                </div>
            </div>
            <div class="overflow-x-auto" style="scrollbar-width: thin">
                <table class="min-w-full border-collapse mb-6">
                    <thead class="bg-gray-50">
                        <tr>
                            @foreach ($data[0] as $header)
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ $header }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach (array_slice($data, 1, 5) as $row)
                            <tr class="hover:bg-gray-50">
                                @foreach ($row as $cell)
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $cell }}
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                @if (count($data) > 6)
                    <div class="mb-4 text-start text-gray-600">
                        <p>Menampilkan 5 baris pertama dari {{ count($data) - 1 }} baris data</p>
                    </div>
                @endif
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
                <div class="p-4 mt-4 bg-red-50 rounded border border-red-200">
                    <p class="flex items-center text-red-600 text-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>{{ $errors->first() }}</span>
                    </p>
                </div>
            @endif

            
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
