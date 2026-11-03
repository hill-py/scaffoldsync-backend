<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\Invoice;
use App\Models\Jadwal;
use App\Models\Pembayaran;
use App\Models\Pesanan;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // 1. Metrik Keuangan
        $totalPendapatan = Pembayaran::sum('jumlah');
        $totalPiutang = Invoice::where('status', 'belum_lunas')->sum('total_tagihan');

        // 2. Metrik Pesanan
        $pesananAktif = Pesanan::whereIn('status', ['menunggu_persetujuan', 'disetujui', 'dijadwalkan', 'berjalan'])->count();
        $pesananSelesai = Pesanan::where('status', 'selesai')->count();

        // 3. Ringkasan Stok Inventaris
        $stokSummary = [
            'total' => Barang::sum('stok_total'),
            'tersedia' => Barang::sum('stok_tersedia'),
            'disewa' => Barang::sum('stok_disewa'),
            'rusak' => Barang::sum('stok_rusak'),
        ];

        // 4. Jadwal Tugas Mandor Hari Ini
        $jadwalHariIniQuery = Jadwal::with(['pesanan.proyek', 'mandor'])
            ->whereDate('tgl_jadwal', now());

        if ($user->role === 'mandor') {
            $jadwalHariIniQuery->where('id_mandor', $user->id_user);
        }

        return response()->json([
            'data' => [
                'keuangan' => [
                    'total_pendapatan' => (float) $totalPendapatan,
                    'total_piutang_menunggak' => (float) $totalPiutang,
                ],
                'pesanan' => [
                    'aktif' => $pesananAktif,
                    'selesai' => $pesananSelesai,
                ],
                'stok_inventaris' => $stokSummary,
                'jadwal_hari_ini' => $jadwalHariIniQuery->get(),
            ],
        ]);
    }
}