@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800 p-6">
    <div class="max-w-4xl mx-auto">
        <!-- Add Home Button -->
        <div class="mb-6">
            <a href="{{ route('home') }}" 
               class="hover:bg-emerald-700 transition btn-primary px-4 py-2 bg-emerald-600 text-white rounded-lg disabled:opacity-50 inline-block">
                Kembali ke Beranda
            </a>
        </div>

        <h1 class="text-4xl mb-4 font-bold text-center text-transparent bg-clip-text bg-gradient-to-r from-emerald-600 to-teal-600">
            Artikel Edukasi
        </h1>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @foreach($articles as $id => $article)
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 hover:shadow-2xl transition duration-300">
                    <h2 class="text-2xl font-semibold text-white dark:text-gray-200 mb-2">
                        {{ $article['title'] }}
                    </h2>
                    <p class="text-gray-600 dark:text-gray-400 line-clamp-3">
                        {{ Str::limit(strip_tags($article['content']), 150) }}
                    </p>
                    <a href="{{ route('articles.show', $id) }}"
                       class="hover:bg-emerald-700 transition btn-primary px-4 py-2 bg-emerald-600 text-white rounded-lg disabled:opacity-50 inline-block mt-4">
                        Baca Selengkapnya
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
