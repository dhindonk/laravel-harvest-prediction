<div class="mb-6">
    <div x-data="uploadHandler()"
        class="bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-700">
        <div x-show="currentStep === 0" x-transition class="p-8">
            <div id="upload-container"
                class="relative border-2 border-dashed rounded-xl p-8 transition-all duration-300 cursor-pointer"
                :class="{ 'border-emerald-500 bg-emerald-50/50 dark:bg-emerald-900/20': dragOver, 'border-gray-300 dark:border-gray-600 hover:border-emerald-400 dark:hover:border-emerald-500':
                        !dragOver }"
                @click="$refs.fileInput.click()"
                @drop.prevent="handleFileUpload($event.dataTransfer.files[0]); dragOver = false"
                @dragover.prevent="dragOver = true" @dragleave.prevent="dragOver = false">
                <div class="flex flex-col items-center space-y-6">
                    <div class="relative">
                        <div
                            class="w-20 h-20 rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center animate-float">
                            <svg class="w-10 h-10 text-emerald-500 dark:text-emerald-400" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                            </svg>
                        </div>
                        <div class="absolute -top-2 -right-2 w-4 h-4 rounded-full bg-teal-400 animate-ping"></div>
                        <div class="absolute -bottom-1 -left-1 w-3 h-3 rounded-full bg-emerald-300 animate-bounce">
                        </div>
                    </div>
                    <div class="text-center space-y-2">
                        <h3 class="text-xl font-semibold text-gray-700 dark:text-gray-300">Upload File CSV</h3>
                        <p class="text-gray-500 dark:text-gray-400">
                            Drag and drop file di sini atau
                            <span class="text-emerald-600 dark:text-emerald-400 hover:text-emerald-500">pilih
                                file</span>
                            <input type="file" class="hidden" accept=".csv" x-ref="fileInput"
                                @change="handleFileUpload($event.target.files[0])">
                        </p>
                    </div>
                    <div class="flex items-center space-x-4 text-sm text-gray-500 dark:text-gray-400">
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Format CSV</span>
                        </div>
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Maks. 2MB</span>
                        </div>
                    </div>
                </div>
                <div x-show="errorMessage" x-transition class="p-4 border-t border-gray-200 dark:border-gray-700">
                    <p class="flex items-center justify-center text-red-600 dark:text-red-400 text-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span x-text="errorMessage"></span>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
