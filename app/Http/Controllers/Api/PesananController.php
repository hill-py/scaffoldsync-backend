<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\DetailPesanan;
use App\Models\Penawaran;
use App\Models\Pesanan;
use App\Services\ConflictValidationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PesananController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Pesanan::with(['pelanggan', 'proyek', 'detailPesanan.barang']);

        // Pelanggan hanya bisa melihat pesanan miliknya sendiri
        if ($user->role === 'pelanggan') {
            $query->whereHas('pelanggan', function ($q) use ($user) {
                $q->where('id_user', $user->id_user);
            });
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        return response()->json([
            'data' => $query->latest()->get(),
        ]);
    }

    public function store(Request $request, ConflictValidationService $validationService)
    {
        $validated = $request->validate([
            'id_pelanggan' => 'required|exists:pelanggan,id_pelanggan',
            'id_proyek' => 'required|exists:proyek,id_proyek',
            'tgl_mulai_sewa' => 'required|date|after_or_equal:today',
            'tgl_selesai_sewa' => 'required|date|after_or_equal:tgl_mulai_sewa',
            'items' => 'required|array|min:1',
            'items.*.id_barang' => 'required|exists:barang,id_barang',
            'items.*.jumlah' => 'required|integer|min:1',
        ]);

        // 1. Validasi Bentrok Stok Setiap Item
        foreach ($validated['items'] as $item) {
            $check = $validationService->checkStockAvailability(
                $item['id_barang'],
                $item['jumlah'],
                $validated['tgl_mulai_sewa'],
                $validated['tgl_selesai_sewa']
            );

            if (! $check['is_available']) {
                return response()->json([
                    'message' => 'Gagal membuat pesanan: Stok tidak mencukupi.',
                    'detail' => $check['message'],
                ], 422);
            }
        }

        // 2. Simpan Pesanan & Detail Pesanan via DB Transaction
        DB::beginTransaction();
        try {
            $noPesanan = 'ORD-' . strtoupper(Str::random(6)) . '-' . date('Ymd');

            $pesanan = Pesanan::create([
                'no_pesanan' => $noPesanan,
                'id_pelanggan' => $validated['id_pelanggan'],
                'id_proyek' => $validated['id_proyek'],
                'id_admin' => $request->user()->role === 'admin' ? $request->user()->id_user : null,
                'tgl_mulai_sewa' => $validated['tgl_mulai_sewa'],
                'tgl_selesai_sewa' => $validated['tgl_selesai_sewa'],
                'status' => 'menunggu_persetujuan',
            ]);

            $totalSewa = 0;
            $durasiHari = Carbon::parse($validated['tgl_mulai_sewa'])
                ->diffInDays(Carbon::parse($validated['tgl_selesai_sewa'])) + 1;

            foreach ($validated['items'] as $item) {
                $barang = Barang::findOrFail($item['id_barang']);
                $subtotal = $barang->harga_sewa_per_hari * $item['jumlah'] * $durasiHari;

                DetailPesanan::create([
                    'id_pesanan' => $pesanan->id_pesanan,
                    'id_barang' => $item['id_barang'],
                    'jumlah' => $item['jumlah'],
                    'harga_satuan' => $barang->harga_sewa_per_hari,
                    'subtotal' => $subtotal,
                ]);

                $totalSewa += $subtotal;
            }

            // 3. Otomatis Buat Dokumen Penawaran
            $noPenawaran = 'PNW-' . date('Ymd') . '-' . rand(100, 999);
            Penawaran::create([
                'no_dokumen' => $noPenawaran,
                'id_pesanan' => $pesanan->id_pesanan,
                'tgl_terbit' => now(),
                'total' => $totalSewa,
                'status' => 'menunggu',
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Pesanan dan dokumen penawaran berhasil dibuat',
                'data' => $pesanan->load(['detailPesanan.barang', 'penawaran']),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Terjadi kesalahan sistem saat membuat pesanan.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        $pesanan = Pesanan::with(['pelanggan', 'proyek', 'admin', 'detailPesanan.barang', 'penawaran', 'jadwal', 'invoice'])
            ->findOrFail($id);

        return response()->json([
            'data' => $pesanan,
        ]);
    }

    public function approve(Request $request, $id)
    {
        $pesanan = Pesanan::with('penawaran')->findOrFail($id);

        if ($pesanan->status !== 'menunggu_persetujuan') {
            return response()->json([
                'message' => 'Pesanan tidak dalam status menunggu persetujuan.',
            ], 422);
        }

        $pesanan->update([
            'status' => 'disetujui',
            'id_admin' => $request->user()->id_user,
        ]);

        if ($pesanan->penawaran) {
            $pesanan->penawaran->update([
                'status' => 'disetujui',
                'tgl_persetujuan' => now(),
            ]);
        }

        return response()->json([
            'message' => 'Pesanan berhasil disetujui',
            'data' => $pesanan,
        ]);
    }
}