<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Services\ConflictValidationService;
use Illuminate\Http\Request;

class BarangController extends Controller
{
    public function index(Request $request)
    {
        $query = Barang::query();

        if ($request->has('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_barang', 'like', "%{$search}%")
                  ->orWhere('kode_barang', 'like', "%{$search}%");
            });
        }

        return response()->json([
            'data' => $query->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_barang' => 'required|string|max:30|unique:barang,kode_barang',
            'nama_barang' => 'required|string|max:100',
            'kategori' => 'required|in:scaffolding,jackbase,clamp',
            'harga_sewa_per_hari' => 'required|numeric|min:0',
            'harga_ganti' => 'required|numeric|min:0',
            'stok_total' => 'required|integer|min:0',
        ]);

        $validated['stok_tersedia'] = $validated['stok_total'];
        $validated['stok_disewa'] = 0;
        $validated['stok_rusak'] = 0;

        $barang = Barang::create($validated);

        return response()->json([
            'message' => 'Barang berhasil ditambahkan',
            'data' => $barang,
        ], 201);
    }

    public function show($id)
    {
        $barang = Barang::findOrFail($id);

        return response()->json([
            'data' => $barang,
        ]);
    }

    public function update(Request $request, $id)
    {
        $barang = Barang::findOrFail($id);

        $validated = $request->validate([
            'kode_barang' => 'required|string|max:30|unique:barang,kode_barang,' . $id . ',id_barang',
            'nama_barang' => 'required|string|max:100',
            'kategori' => 'required|in:scaffolding,jackbase,clamp',
            'harga_sewa_per_hari' => 'required|numeric|min:0',
            'harga_ganti' => 'required|numeric|min:0',
            'stok_total' => 'required|integer|min:0',
        ]);

        // Rekalkulasi stok tersedia berdasar stok total baru
        $selisihStok = $validated['stok_total'] - $barang->stok_total;
        $validated['stok_tersedia'] = max(0, $barang->stok_tersedia + $selisihStok);

        $barang->update($validated);

        return response()->json([
            'message' => 'Data barang berhasil diperbarui',
            'data' => $barang,
        ]);
    }

    public function destroy($id)
    {
        $barang = Barang::findOrFail($id);

        if ($barang->stok_disewa > 0) {
            return response()->json([
                'message' => 'Gagal menghapus: Barang sedang dalam status sewa aktif.',
            ], 422);
        }

        $barang->delete();

        return response()->json([
            'message' => 'Barang berhasil dihapus',
        ]);
    }

    public function checkAvailability(Request $request, ConflictValidationService $validationService)
    {
        $request->validate([
            'id_barang' => 'required|exists:barang,id_barang',
            'jumlah' => 'required|integer|min:1',
            'tgl_mulai_sewa' => 'required|date',
            'tgl_selesai_sewa' => 'required|date|after_or_equal:tgl_mulai_sewa',
        ]);

        $result = $validationService->checkStockAvailability(
            $request->id_barang,
            $request->jumlah,
            $request->tgl_mulai_sewa,
            $request->tgl_selesai_sewa
        );

        return response()->json($result);
    }
}