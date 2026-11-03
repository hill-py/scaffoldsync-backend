<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Penawaran;
use App\Models\SuratJalan;
use Barryvdh\DomPDF\Facade\Pdf;

class PdfController extends Controller
{
    public function printPenawaran($id)
    {
        $penawaran = Penawaran::with(['pesanan.pelanggan', 'pesanan.proyek', 'pesanan.detailPesanan.barang'])
            ->findOrFail($id);

        $pdf = Pdf::loadView('pdf.penawaran', compact('penawaran'));

        return $pdf->download("Penawaran-{$penawaran->no_dokumen}.pdf");
    }

    public function printSuratJalan($id)
    {
        $suratJalan = SuratJalan::with(['jadwal.pesanan.proyek', 'jadwal.mandor', 'jadwal.pesanan.detailPesanan.barang'])
            ->findOrFail($id);

        $pdf = Pdf::loadView('pdf.surat_jalan', compact('suratJalan'));

        return $pdf->download("SuratJalan-{$suratJalan->no_dokumen}.pdf");
    }

    public function printInvoice($id)
    {
        $invoice = Invoice::with(['pesanan.pelanggan', 'pesanan.proyek'])
            ->findOrFail($id);

        $pdf = Pdf::loadView('pdf.invoice', compact('invoice'));

        return $pdf->download("Invoice-{$invoice->no_dokumen}.pdf");
    }
}