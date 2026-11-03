<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BarangController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\InspeksiController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\JadwalController;
use App\Http\Controllers\Api\PdfController;
use App\Http\Controllers\Api\PesananController;
use Illuminate\Support\Facades\Route;

// Public Route (Rate Limiter Login: Max 5x per menit)
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');

// Protected Routes (Sanctum + Rate Limiter API Standard: Max 60x per menit)
Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    
    // 1. Auth & Profil User
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // 2. Master Inventaris Barang
    Route::get('/barang', [BarangController::class, 'index']);
    Route::get('/barang/{id}', [BarangController::class, 'show']);
    Route::post('/barang/check-availability', [BarangController::class, 'checkAvailability']);

    // 3. Modul Pesanan & Penawaran
    Route::get('/orders', [PesananController::class, 'index']);
    Route::get('/orders/{id}', [PesananController::class, 'show']);
    Route::post('/orders', [PesananController::class, 'store']);

    // 4. Penjadwalan & Logistik
    Route::get('/schedules', [JadwalController::class, 'index']);
    Route::put('/schedules/{id}/status', [JadwalController::class, 'updateStatus']);

    // 5. Inspeksi Lapangan
    Route::get('/inspections', [InspeksiController::class, 'index']);
    Route::get('/inspections/{id}', [InspeksiController::class, 'show']);

    // 6. Invoice & Pembayaran
    Route::get('/invoices', [InvoiceController::class, 'index']);
    Route::get('/invoices/{id}', [InvoiceController::class, 'show']);
    Route::post('/invoices/{id}/pay', [InvoiceController::class, 'pay']);

    // 7. Dokumen PDF & Agregasi Dashboard
    Route::get('/pdf/penawaran/{id}', [PdfController::class, 'printPenawaran']);
    Route::get('/pdf/surat-jalan/{id}', [PdfController::class, 'printSuratJalan']);
    Route::get('/pdf/invoice/{id}', [PdfController::class, 'printInvoice']);
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Otorisasi Khusus Admin & Pemilik
    Route::middleware('role:admin,pemilik')->group(function () {
        Route::post('/barang', [BarangController::class, 'store']);
        Route::put('/barang/{id}', [BarangController::class, 'update']);
        Route::delete('/barang/{id}', [BarangController::class, 'destroy']);
        Route::put('/orders/{id}/approve', [PesananController::class, 'approve']);
        Route::post('/schedules', [JadwalController::class, 'store']);
        Route::post('/invoices/generate', [InvoiceController::class, 'generate']);
    });

    // Otorisasi Khusus Mandor, Admin, & Pemilik
    Route::middleware('role:mandor,admin,pemilik')->group(function () {
        Route::post('/inspections', [InspeksiController::class, 'store']);
    });
});