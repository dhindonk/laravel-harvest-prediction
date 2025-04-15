@extends('layouts.app')

@section('content')
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 p-6">
        <div class="max-w-3xl mx-auto bg-white rounded-xl shadow-lg p-8">
            <h1 class="text-4xl font-bold text-gray-800 mb-4">
                {{ $article['title'] }}
            </h1>

            @php
                $parsedown = new ParsedownExtra();
                $parsedown->setBreaksEnabled(true);
            @endphp

            <div class="text-gray-400 prose max-w-none">
                {!! $parsedown->text($article['content']) !!}
            </div>

            <div class="mt-6">
                <a href="{{ route('articles.index') }}"
                    class="hover:bg-emerald-700 transition btn-primary text-white px-4 py-2 bg-gray-200 text-gray-700 rounded">
                    Kembali ke Daftar Artikel
                </a>
            </div>
        </div>
    </div>
@endsection
