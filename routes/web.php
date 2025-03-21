<?php

use App\Http\Controllers\SiteController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ArticleController;

Route::get('/', [SiteController::class, 'home'])->name('home');
Route::post('/upload', [SiteController::class, 'upload'])->name('upload.submit');
Route::get('/preview', [SiteController::class, 'preview'])->name('upload.preview');
Route::post('/analyze', [SiteController::class, 'analyze'])->name('analyze');
Route::get('/results', [SiteController::class, 'results'])->name('results');

Route::get('/articles', [ArticleController::class, 'index'])->name('articles.index');
Route::get('/articles/{id}', [ArticleController::class, 'show'])->name('articles.show');
