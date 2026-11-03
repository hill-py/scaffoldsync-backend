<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\DetailInspeksi;
use App\Models\Inspeksi;
use App\Models\Jadwal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InspeksiController extends Controller
{
    public function index(Request $request)
    {
        $query = Inspeksi::with(['jadwal.pesanan.pelanggan', 'jadwal.mandor', 'detailInspeksi.barang']);

        if ($request->has('id_jadwal')) {
            $query->where('id_jadwal', $request->id_jadwal);
        }

        return response()->json([
            'data' => $query->latest()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_jadwal' => 'required|exists:jadwal,id_jadwal',
            'catatan' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.id_barang' => 'required|exists:barang,id_barang',
            'items.*.jumlah_baik' => 'required|integer|min:0',
            'items.*.jumlah_rusak' => 'required|integer|min:0',
            'items.*.jumlah_hilang' => 'required|integer|min:0',
        ]);

        $jadwal = Jadwal::with('pesanan')->findOrFail($validated['id_jadwal']);

        if ($jadwal->inspeksi) {
            return response()->json([
                'message' => 'Inspeksi untuk jadwal ini sudah pernah dibuat.',
            ], 422);
        }

        DB::beginTransaction();
        try {
            $inspeksi = Inspeksi::create([
                'id_jadwal' => $validated['id_jadwal'],
                'tgl_inspeksi' => now(),
                'catatan' => $validated['catatan'] ?? null,
            ]);

            foreach ($validated['items'] as $item) {
                DetailInspeksi::create([
                    'id_inspeksi' => $inspeksi->id_inspeksi,
                    'id_barang' => $item['id_barang'],
                    'jumlah_baik' => $item['jumlah_baik'],
                    'jumlah_rusak' => $item['jumlah_rusak'],
                    'jumlah_hilang' => $item['jumlah_hilang'],
                ]);

                // Pembaruan status fisik inventaris
                $barang = Barang::findOrFail($item['id_barang']);
                $totalDikembalikan = $item['jumlah_baik'] + $item['jumlah_rusak'] + $item['jumlah_hilang'];

                // Kurangi stok yang sedang disewa
                $barang->decrement('stok_disewa', min($barang->stok_disewa, $totalDikembalikan));
                
                // Kembalikan barang kondisi baik ke stok tersedia
                $barang->increment('stok_tersedia', $item['jumlah_baik']);

                // Catat barang rusak ke akumulasi stok rusak
                if ($item['jumlah_rusak'] > 0) {
                    $barang->increment('stok_rusak', $item['jumlah_rusak']);
                }

                // Kurangi total stok perusahaan jika barang hilang sepenuhnya
                if ($item['jumlah_hilang'] > 0) {
                    $barang->decrement('stok_total', min($barang->stok_total, $item['jumlah_hilang']));
                }
            }

            // Tandai jadwal selesai
            $jadwal->update(['status' => 'selesai']);

            DB::commit();

            return response()->json([
                'message' => 'Hasil inspeksi lapangan berhasil disimpan dan stok telah diperbarui',
                'data' => $inspeksi->load('detailInspeksi.barang'),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal menyimpan hasil inspeksi.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        $inspeksi = Inspeksi::with(['jadwal.pesanan', 'detailInspeksi.barang'])->findOrFail($id);

        return response()->json([
            'data' => $inspeksi,
        ]);
    }
}