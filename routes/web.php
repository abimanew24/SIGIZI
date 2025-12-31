<?php

use App\Models\Distribusi;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Route;

// =================================================================
// 1. ROUTE HALAMAN UTAMA (Redirect Otomatis)
// =================================================================
// Saat buka http://127.0.0.1:8000 -> Lempar ke Login Panel
Route::get('/', function () {
    return redirect()->route('filament.admin.auth.login');
});

// =================================================================
// 2. ROUTE LOGIN KHUSUS (Penanganan URL /login)
// =================================================================
// Ini penting! Kalau user ketik /login manual atau dilempar oleh Middleware auth,
// dia akan ditangkap di sini dan diarahkan ke login admin yang benar.
Route::get('/login', function () {
    return redirect()->route('filament.admin.auth.login');
})->name('login');

// =================================================================
// 3. ROUTE CETAK PDF (Surat Jalan)
// =================================================================
Route::get('/cetak-surat-jalan/{id}', function ($id) {
    // Cari data distribusi berdasarkan ID
    $distribusi = Distribusi::with(['sekolah', 'menu'])->findOrFail($id);
    
    // Load View PDF
    $pdf = Pdf::loadView('pdf.surat-jalan', compact('distribusi'));
    
    // Stream (Tampilkan di browser)
    return $pdf->stream('Surat-Jalan-' . $distribusi->nomor_surat_jalan . '.pdf');
})->name('cetak.surat-jalan');