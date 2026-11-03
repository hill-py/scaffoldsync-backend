<!DOCTYPE html>
<html>
<head>
    <title>Surat Penawaran - {{ $penawaran->no_dokumen }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .table th { background-color: #f2f2f2; }
        .total { text-align: right; font-weight: bold; margin-top: 15px; font-size: 14px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>SCAFFOLDSYNC RENTAL</h2>
        <p>SURAT PENAWARAN HARGA SEWA</p>
        <small>No Dokumen: {{ $penawaran->no_dokumen }} | Tanggal: {{ $penawaran->tgl_terbit }}</small>
    </div>

    <p><strong>Kepada Yth:</strong> {{ $penawaran->pesanan->pelanggan->nama_pelanggan }} ({{ $penawaran->pesanan->pelanggan->perusahaan }})</p>
    <p><strong>Proyek:</strong> {{ $penawaran->pesanan->proyek->nama_proyek }}</p>
    <p><strong>Periode Sewa:</strong> {{ $penawaran->pesanan->tgl_mulai_sewa }} s/d {{ $penawaran->pesanan->tgl_selesai_sewa }}</p>

    <table class="table">
        <thead>
            <tr>
                <th>No</th>
                <th>Item Barang</th>
                <th>Jumlah</th>
                <th>Harga Sewa / Hari</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($penawaran->pesanan->detailPesanan as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->barang->nama_barang }} ({{ $item->barang->kode_barang }})</td>
                <td>{{ $item->jumlah }}</td>
                <td>Rp {{ number_format($item->harga_satuan, 0, ',', '.') }}</td>
                <td>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total">
        TOTAL ESTIMASI BIAYA SEWA: Rp {{ number_format($penawaran->total, 0, ',', '.') }}
    </div>
</body>
</html>