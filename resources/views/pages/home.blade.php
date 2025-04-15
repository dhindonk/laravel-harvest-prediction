@extends('layouts.app')

@section('content')
    <div class="min-h-screen flex flex-col">
        <!-- Hero Section -->
        <div class="relative py-20 px-6 sm:px-8 lg:px-12">
            <div class="max-w-4xl mx-auto text-center">
                <!-- Background Decoration -->
                <div class="absolute inset-0 -z-10">
                    <div class="absolute inset-0 bg-gradient-to-br from-emerald-50 to-teal-50">
                    </div>
                    <div
                        class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] bg-gradient-to-r from-emerald-500/20 to-teal-500/20 rounded-full blur-3xl animate-pulse">
                    </div>
                </div>
                <!-- Main Content -->
                <div class="relative z-10">
                    <h1
                        class="text-4xl sm:text-5xl lg:text-6xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-emerald-600 to-teal-600 mb-6">
                        Prediksi Hasil Panen
                    </h1>
                    <p class="text-lg text-gray-600 mb-12 max-w-2xl mx-auto">
                        Upload data Excel atau CSV Anda untuk mendapatkan prediksi hasil panen yang akurat dengan teknologi
                        Machine Learning
                    </p>

                    <!-- Upload Area -->
                    <div class="max-w-3xl mx-auto">
                        <div class="bg-white rounded-xl shadow-lg border border-gray-200">
                            <div class="p-8">
                                <form action="{{ route('upload.submit') }}" method="POST" enctype="multipart/form-data"
                                    id="upload-form">
                                    @csrf
                                    <div id="upload-container"
                                        class="relative border-2 border-dashed rounded-xl p-8 transition-all duration-300 cursor-pointer border-gray-300 hover:border-emerald-400"
                                        x-data="{ dragOver: false }" @dragover.prevent="dragOver = true"
                                        @dragleave.prevent="dragOver = false"
                                        @drop.prevent="handleFileDrop($event); dragOver = false"
                                        :class="{
                                            'border-emerald-500 bg-emerald-50/50': dragOver,
                                            'border-gray-300 hover:border-emerald-400': !dragOver
                                        }"
                                        @click="document.getElementById('file-input').click()">
                                        <div class="flex flex-col items-center space-y-6">
                                            <div class="relative">
                                                <div
                                                    class="w-20 h-20 rounded-full bg-emerald-100 flex items-center justify-center animate-float">
                                                    <svg class="w-10 h-10 text-emerald-500" fill="none"
                                                        viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                                    </svg>
                                                </div>
                                                <div
                                                    class="absolute -top-2 -right-2 w-4 h-4 rounded-full bg-teal-400 animate-ping">
                                                </div>
                                                <div
                                                    class="absolute -bottom-1 -left-1 w-3 h-3 rounded-full bg-emerald-300 animate-bounce">
                                                </div>
                                            </div>
                                            <div class="text-center space-y-2">
                                                <h3 class="text-xl font-semibold text-gray-700">Upload File Excel/CSV</h3>
                                                <p class="text-gray-500">
                                                    Drag and drop file di sini atau
                                                    <span class="text-emerald-600 hover:text-emerald-500">pilih file</span>
                                                </p>
                                                <input type="file" name="file" id="file-input" class="hidden"
                                                    accept=".csv,.xlsx,.xls" onchange="handleFileSelect(this)">
                                            </div>
                                            <div class="flex items-center space-x-4 text-sm text-gray-500">
                                                <div class="flex items-center">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    <span>Format Excel/CSV</span>
                                                </div>
                                                <div class="flex items-center">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    <span>Maks. 2MB</span>
                                                </div>
                                            </div>
                                        </div>
                                        @if ($errors->any())
                                            <div class="p-4 border-t border-gray-200 mt-4">
                                                <p class="flex items-center justify-center text-red-600 text-sm">
                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    <span>{{ $errors->first() }}</span>
                                                </p>
                                            </div>
                                        @endif
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Education Button -->
                    <div class="mt-8">
                        <a href="{{ route('articles.index') }}"
                            class="group inline-flex items-center px-8 py-4 rounded-xl border-2 border-emerald-500 text-emerald-600 font-medium hover:bg-emerald-50 transform transition-all duration-300 hover:-translate-y-1">
                            <span>Pelajari Tips & Trik</span>
                            <svg class="w-5 h-5 ml-2 transition-transform group-hover:translate-x-1" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function handleFileSelect(input) {
                const file = input.files[0];
                if (file) {
                    validateAndSubmit(file);
                }
            }

            function handleFileDrop(event) {
                event.preventDefault();
                const file = event.dataTransfer.files[0];
                if (file) {
                    // Create a new FileList containing the dropped file
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(file);

                    // Set the file input's files
                    document.getElementById('file-input').files = dataTransfer.files;

                    validateAndSubmit(file);
                }
            }

            function validateAndSubmit(file) {
                // Validasi ukuran file
                if (file.size > 2 * 1024 * 1024) { // 2MB
                    alert('Ukuran file terlalu besar. Maksimal 2MB.');
                    return;
                }

                // Validasi tipe file
                const validTypes = ['.csv', '.xlsx', '.xls'];
                const fileExtension = '.' + file.name.split('.').pop().toLowerCase();
                if (!validTypes.includes(fileExtension)) {
                    alert('Format file tidak didukung. Gunakan CSV atau Excel.');
                    return;
                }

                // Submit form
                document.getElementById('upload-form').submit();
            }

            // Prevent default drag behaviors
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                document.body.addEventListener(eventName, preventDefaults, false);
            });

            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }
        </script>
    @endpush
@endsection
