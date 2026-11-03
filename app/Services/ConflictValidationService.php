<?php

namespace App\Services;

use App\Models\Jadwal;
use App\Models\Pesanan;
use Carbon\Carbon;

class ConflictValidationService
{
    /**
     * Validasi bentrok ketersediaan stok barang pada rentang tanggal sewa
     */
    public function checkStockAvailability(int $idBarang, int $jumlahDiminta, string $tglMulai, string $tglSelesai, ?int $ignorePesananId = null): array
    {
        $mulai = Carbon::parse($tglMulai)->startOfDay();
        $selesai = Carbon::parse($tglSelesai)->endOfDay();

        // Ambil total item yang sedang dipesan pada rentang tanggal yang beririsan (overlapping)
        $query = Pesanan::whereIn('status', ['disetujui', 'dijadwalkan', 'berjalan'])
            ->where(function ($q) use ($mulai, $selesai) {
                $q->whereBetween('tgl_mulai_sewa', [$mulai, $selesai])
                  ->orWhereBetween('tgl_selesai_sewa', [$mulai, $selesai])
                  ->orWhere(function ($sub) use ($mulai, $selesai) {
                      $sub->where('tgl_mulai_sewa', '<=', $mulai)
                          ->where('tgl_selesai_sewa', '>=', $selesai);
                  });
            });

        if ($ignorePesananId) {
            $query->where('id_pesanan', '!=', $ignorePesananId);
        }

        $pesananIds = $query->pluck('id_pesanan');

        // Total stok yang terikat di transaksi aktif lain
        $stokTerpakai = \App\Models\DetailPesanan::whereIn('id_pesanan', $pesananIds)
            ->where('id_barang', $idBarang)
            ->sum('jumlah');

        $barang = \App\Models\Barang::find($idBarang);

        if (! $barang) {
            return [
                'is_available' => false,
                'message' => 'Barang tidak ditemukan.',
            ];
        }

        // Stok riil yang bisa disewa pada periode tersebut
        $stokTersediaPeriode = $barang->stok_total - $barang->stok_rusak - $stokTerpakai;

        if ($jumlahDiminta > $stokTersediaPeriode) {
            return [
                'is_available' => false,
                'message' => "Stok {$barang->nama_barang} tidak mencukupi untuk periode tersebut. Tersedia: {$stokTersediaPeriode}, Diminta: {$jumlahDiminta}.",
                'stok_tersedia' => max(0, $stokTersediaPeriode),
            ];
        }

        return [
            'is_available' => true,
            'message' => 'Stok tersedia.',
            'stok_tersedia' => $stokTersediaPeriode,
        ];
    }

    /**
     * Validasi bentrok penugasan mandor pada tanggal jadwal yang sama
     */
    public function checkMandorAvailability(int $idMandor, string $tglJadwal, ?int $ignoreJadwalId = null): array
    {
        $query = Jadwal::where('id_mandor', $idMandor)
            ->whereDate('tgl_jadwal', $tglJadwal)
            ->whereIn('status', ['terjadwal']);

        if ($ignoreJadwalId) {
            $query->where('id_jadwal', '!=', $ignoreJadwalId);
        }

        $jadwalBentrok = $query->first();

        if ($jadwalBentrok) {
            $mandor = \App\Models\User::find($idMandor);
            return [
                'is_available' => false,
                'message' => "Mandor {$mandor->nama} sudah memiliki tugas pada tanggal tersebut.",
                'jadwal_bentrok' => $jadwalBentrok,
            ];
        }

        return [
            'is_available' => true,
            'message' => 'Mandor tersedia.',
        ];
    }
}