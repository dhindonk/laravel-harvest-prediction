@extends('layouts.app')

@section('content')
    <div class="min-h-screen flex flex-col">
        <!-- Hero Section -->
        <div class="relative py-20 px-6 sm:px-8 lg:px-12">
            <div class="max-w-4xl mx-auto text-center">
                <!-- Background Decoration -->
                <div class="absolute inset-0 -z-10">
                    <div
                        class="absolute inset-0 bg-gradient-to-br from-emerald-50 to-teal-50 dark:from-emerald-900/20 dark:to-teal-900/20 backdrop-blur-3xl">
                    </div>
                    <div
                        class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] bg-gradient-to-r from-emerald-500/20 to-teal-500/20 rounded-full blur-3xl animate-pulse">
                    </div>
                </div>
                <!-- Main Content -->
                <div class="relative z-10">
                    <h1
                        class="text-4xl sm:text-5xl lg:text-6xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-emerald-600 to-teal-600 dark:from-emerald-400 dark:to-teal-400 mb-6">
                        Prediksi Hasil Panen
                    </h1>
                    <p class="text-lg text-gray-600 dark:text-gray-400 mb-12 max-w-2xl mx-auto">
                        Upload data CSV Anda untuk mendapatkan prediksi hasil panen yang akurat dengan teknologi Machine
                        Learning
                    </p>

                    <!-- Upload Area -->
                    <div class="max-w-3xl mx-auto">
                        <div
                            class="bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-700">
                            <div class="p-8">
                                <form action="{{ route('upload.submit') }}" method="POST" enctype="multipart/form-data"
                                    id="upload-form">
                                    @csrf
                                    <div id="upload-container"
                                        class="relative border-2 border-dashed rounded-xl p-8 transition-all duration-300 cursor-pointer"
                                        :class="{ 'border-emerald-500 bg-emerald-50/50 dark:bg-emerald-900/20': dragOver, 'border-gray-300 dark:border-gray-600 hover:border-emerald-400 dark:hover:border-emerald-500':
                                                !dragOver }"
                                        x-data="{ dragOver: false }" @click="document.getElementById('file-input').click()"
                                        @drop.prevent="document.getElementById('file-input').files = $event.dataTransfer.files; document.getElementById('upload-form').submit(); dragOver = false"
                                        @dragover.prevent="dragOver = true" @dragleave.prevent="dragOver = false">
                                        <div class="flex flex-col items-center space-y-6">
                                            <div class="relative">
                                                <div
                                                    class="w-20 h-20 rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center animate-float">
                                                    <svg class="w-10 h-10 text-emerald-500 dark:text-emerald-400"
                                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
                                                <h3 class="text-xl font-semibold text-gray-700 dark:text-gray-300">Upload
                                                    File CSV</h3>
                                                <p class="text-gray-500 dark:text-gray-400">
                                                    Drag and drop file di sini atau
                                                    <span
                                                        class="text-emerald-600 dark:text-emerald-400 hover:text-emerald-500">pilih
                                                        file</span>
                                                    <input type="file" name="file" id="file-input" class="hidden"
                                                        accept=".csv"
                                                        onchange="document.getElementById('upload-form').submit()">
                                                </p>
                                            </div>
                                            <div
                                                class="flex items-center space-x-4 text-sm text-gray-500 dark:text-gray-400">
                                                <div class="flex items-center">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    <span>Format CSV</span>
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
                                            <div class="p-4 border-t border-gray-200 dark:border-gray-700 mt-4">
                                                <p
                                                    class="flex items-center justify-center text-red-600 dark:text-red-400 text-sm">
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
                            class="group inline-flex items-center px-8 py-4 rounded-xl border-2 border-emerald-500 dark:border-emerald-400 text-emerald-600 dark:text-emerald-400 font-medium hover:bg-emerald-50 dark:hover:bg-emerald-900/20 transform transition-all duration-300 hover:-translate-y-1">
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
@endsection
