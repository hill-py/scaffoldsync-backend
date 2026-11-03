<!DOCTYPE html>
<html>
<head>
    <title>Invoice - {{ $invoice->no_dokumen }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .table th { background-color: #f2f2f2; }
        .summary { text-align: right; margin-top: 15px; font-size: 13px; }
        .status-badge { display: inline-block; padding: 5px 10px; background-color: #eee; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h2>SCAFFOLDSYNC INVOICING</h2>
        <p>INVOICE PENAGIHAN SEWA & KLAIM DOKUMEN</p>
        <small>No Dokumen: {{ $invoice->no_dokumen }} | Tanggal: {{ $invoice->tgl_terbit }}</small>
    </div>

    <p><strong>Ditagihkan Kepada:</strong> {{ $invoice->pesanan->pelanggan->nama_pelanggan }} ({{ $invoice->pesanan->pelanggan->perusahaan }})</p>
    <p><strong>Jatuh Tempo:</strong> {{ $invoice->jatuh_tempo }}</p>
    <p><strong>Status Pembayaran:</strong> <span class="status-badge">{{ strtoupper($invoice->status) }}</span></p>

    <table class="table">
        <thead>
            <tr>
                <th>Rincian Tagihan</th>
                <th>Jumlah Biaya</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Total Biaya Sewa Scaffolding</td>
                <td>Rp {{ number_format($invoice->total_sewa, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Total Klaim Kerusakan & Kehilangan Barang (Inspeksi)</td>
                <td>Rp {{ number_format($invoice->total_klaim, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="summary">
        <h3>TOTAL TAGIHAN: Rp {{ number_format($invoice->total_tagihan, 0, ',', '.') }}</h3>
    </div>
</body>
</html>