<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AnalysisController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\ArticleController;

// Routes utama
Route::get('/', [SiteController::class, 'home'])->name('home');
Route::post('/upload', [SiteController::class, 'upload'])->name('upload.submit');
Route::get('/preview', [SiteController::class, 'preview'])->name('preview');
Route::post('/analyze', [SiteController::class, 'analyze'])->name('analyze');
Route::get('/results', [SiteController::class, 'results'])->name('results');
Route::get('/download-template', [SiteController::class, 'downloadTemplate'])->name('download.template');

// Add new export routes
Route::get('/export-pdf', [SiteController::class, 'exportPDF'])->name('export.pdf');
Route::get('/export-excel', [SiteController::class, 'exportExcel'])->name('export.excel');
Route::post('/share-results', [SiteController::class, 'shareResults'])->name('share.results');

// Routes untuk artikel
Route::get('/articles', [ArticleController::class, 'index'])->name('articles.index');
Route::get('/articles/{id}', [ArticleController::class, 'show'])->name('articles.show');
// Route::get('/template/download', [ArticleController::class, 'downloadTemplate'])->name('template.download');
