<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Pembayaran;
use App\Models\Pesanan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Invoice::with(['pesanan.pelanggan', 'pembayaran']);

        if ($user->role === 'pelanggan') {
            $query->whereHas('pesanan.pelanggan', function ($q) use ($user) {
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

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'id_pesanan' => 'required|exists:pesanan,id_pesanan',
            'jatuh_tempo_hari' => 'nullable|integer|min:1',
        ]);

        $pesanan = Pesanan::with(['detailPesanan.barang', 'jadwal.inspeksi.detailInspeksi.barang', 'invoice'])
            ->findOrFail($validated['id_pesanan']);

        if ($pesanan->invoice) {
            return response()->json([
                'message' => 'Invoice untuk pesanan ini sudah terbit.',
                'data' => $pesanan->invoice,
            ], 422);
        }

        // 1. Hitung Total Biaya Sewa
        $durasiHari = Carbon::parse($pesanan->tgl_mulai_sewa)
            ->diffInDays(Carbon::parse($pesanan->tgl_selesai_sewa)) + 1;

        $totalSewa = 0;
        foreach ($pesanan->detailPesanan as $detail) {
            $totalSewa += ($detail->harga_satuan * $detail->jumlah * $durasiHari);
        }

        // 2. Hitung Total Denda Klaim Kerusakan & Kehilangan Barang dari Inspeksi
        $totalKlaim = 0;
        foreach ($pesanan->jadwal as $jadwal) {
            if ($jadwal->inspeksi) {
                foreach ($jadwal->inspeksi->detailInspeksi as $detailInspeksi) {
                    $hargaGanti = $detailInspeksi->barang->harga_ganti;
                    // Denda barang rusak = 50% harga ganti, barang hilang = 100% harga ganti
                    $dendaRusak = $detailInspeksi->jumlah_rusak * ($hargaGanti * 0.5);
                    $dendaHilang = $detailInspeksi->jumlah_hilang * $hargaGanti;
                    $totalKlaim += ($dendaRusak + $dendaHilang);
                }
            }
        }

        $totalTagihan = $totalSewa + $totalKlaim;
        $tglJatuhTempo = Carbon::now()->addDays($validated['jatuh_tempo_hari'] ?? 14);

        $noInvoice = 'INV-' . date('Ymd') . '-' . strtoupper(Str::random(4));

        $invoice = Invoice::create([
            'no_dokumen' => $noInvoice,
            'id_pesanan' => $pesanan->id_pesanan,
            'tgl_terbit' => now(),
            'total_sewa' => $totalSewa,
            'total_klaim' => $totalKlaim,
            'total_tagihan' => $totalTagihan,
            'status' => 'belum_lunas',
            'jatuh_tempo' => $tglJatuhTempo,
        ]);

        return response()->json([
            'message' => 'Invoice penagihan berhasil diterbitkan',
            'data' => $invoice->load('pesanan.pelanggan'),
        ], 201);
    }

    public function show($id)
    {
        $invoice = Invoice::with(['pesanan.pelanggan', 'pesanan.proyek', 'pembayaran'])->findOrFail($id);

        return response()->json([
            'data' => $invoice,
        ]);
    }

    public function pay(Request $request, $id)
    {
        $validated = $request->validate([
            'jumlah' => 'required|numeric|min:1',
            'metode' => 'required|in:tunai,transfer',
            'catatan' => 'nullable|string',
        ]);

        $invoice = Invoice::with('pembayaran')->findOrFail($id);

        if ($invoice->status === 'lunas') {
            return response()->json([
                'message' => 'Invoice ini sudah lunas.',
            ], 422);
        }

        DB::beginTransaction();
        try {
            $pembayaran = Pembayaran::create([
                'id_invoice' => $invoice->id_invoice,
                'tgl_bayar' => now(),
                'jumlah' => $validated['jumlah'],
                'metode' => $validated['metode'],
                'catatan' => $validated['catatan'] ?? null,
            ]);

            $totalDibayar = $invoice->pembayaran()->sum('jumlah');

            if ($totalDibayar >= $invoice->total_tagihan) {
                $invoice->update(['status' => 'lunas']);

                // Tandai transaksi pesanan selesai sepenuhnya
                $invoice->pesanan->update(['status' => 'selesai']);
            }

            DB::commit();

            return response()->json([
                'message' => 'Pembayaran berhasil dicatat',
                'data' => [
                    'pembayaran' => $pembayaran,
                    'invoice_status' => $invoice->fresh()->status,
                    'sisa_tagihan' => max(0, $invoice->total_tagihan - $totalDibayar),
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal mencatat pembayaran.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}