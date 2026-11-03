<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Jadwal;
use App\Models\Pesanan;
use App\Models\SuratJalan;
use App\Services\ConflictValidationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class JadwalController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Jadwal::with(['pesanan.pelanggan', 'pesanan.proyek', 'mandor', 'suratJalan']);

        // Jika login sebagai mandor, tampilkan hanya tugasnya sendiri
        if ($user->role === 'mandor') {
            $query->where('id_mandor', $user->id_user);
        }

        if ($request->has('tgl_jadwal')) {
            $query->whereDate('tgl_jadwal', $request->tgl_jadwal);
        }

        return response()->json([
            'data' => $query->latest()->get(),
        ]);
    }

    public function store(Request $request, ConflictValidationService $validationService)
    {
        $validated = $request->validate([
            'id_pesanan' => 'required|exists:pesanan,id_pesanan',
            'id_mandor' => 'required|exists:users,id_user',
            'jenis' => 'required|in:pengiriman,pengambilan',
            'tgl_jadwal' => 'required|date',
            'catatan' => 'nullable|string',
        ]);

        $pesanan = Pesanan::findOrFail($validated['id_pesanan']);

        if (! in_array($pesanan->status, ['disetujui', 'dijadwalkan', 'berjalan'])) {
            return response()->json([
                'message' => 'Jadwal hanya bisa dibuat untuk pesanan yang sudah disetujui.',
            ], 422);
        }

        // 1. Validasi Bentrok Penugasan Mandor
        $checkMandor = $validationService->checkMandorAvailability(
            $validated['id_mandor'],
            $validated['tgl_jadwal']
        );

        if (! $checkMandor['is_available']) {
            return response()->json([
                'message' => 'Gagal membuat jadwal: Mandor bentrok.',
                'detail' => $checkMandor['message'],
            ], 422);
        }

        // 2. Simpan Jadwal & Auto-Generate Surat Jalan
        DB::beginTransaction();
        try {
            $jadwal = Jadwal::create([
                'id_pesanan' => $validated['id_pesanan'],
                'id_mandor' => $validated['id_mandor'],
                'jenis' => $validated['jenis'],
                'tgl_jadwal' => $validated['tgl_jadwal'],
                'status' => 'terjadwal',
                'catatan' => $validated['catatan'] ?? null,
            ]);

            // Update status pesanan
            $pesanan->update(['status' => 'dijadwalkan']);

            // Terbitkan Surat Jalan
            $noSuratJalan = 'SJ-' . date('Ymd') . '-' . strtoupper(Str::random(4));
            $suratJalan = SuratJalan::create([
                'no_dokumen' => $noSuratJalan,
                'id_jadwal' => $jadwal->id_jadwal,
                'tgl_terbit' => now(),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Jadwal penugasan mandor dan Surat Jalan berhasil dibuat',
                'data' => $jadwal->load(['mandor', 'suratJalan', 'pesanan']),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Terjadi kesalahan sistem saat membuat jadwal.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:terjadwal,selesai,gagal,dijadwalkan_ulang',
            'catatan' => 'nullable|string',
        ]);

        $jadwal = Jadwal::with('pesanan.detailPesanan.barang')->findOrFail($id);
        $jadwal->update($validated);

        // Jika pengiriman barang selesai, ubah status pesanan & update stok terpakai
        if ($validated['status'] === 'selesai') {
            if ($jadwal->jenis === 'pengiriman') {
                $jadwal->pesanan->update(['status' => 'berjalan']);

                // Pindahkan stok dari tersedia ke disewa
                foreach ($jadwal->pesanan->detailPesanan as $detail) {
                    $barang = $detail->barang;
                    $barang->decrement('stok_tersedia', $detail->jumlah);
                    $barang->increment('stok_disewa', $detail->jumlah);
                }
            }
        }

        return response()->json([
            'message' => 'Status jadwal berhasil diperbarui',
            'data' => $jadwal,
        ]);
    }
}