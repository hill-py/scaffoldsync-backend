<!DOCTYPE html>
<html>
<head>
    <title>Surat Jalan - {{ $suratJalan->no_dokumen }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .table th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <div class="header">
        <h2>SCAFFOLDSYNC LOGISTICS</h2>
        <p>SURAT JALAN / DOKUMEN PENGIRIMAN BARANG</p>
        <small>No Dokumen: {{ $suratJalan->no_dokumen }} | Tanggal Terbit: {{ $suratJalan->tgl_terbit }}</small>
    </div>

    <p><strong>Tujuan Pengiriman:</strong> {{ $suratJalan->jadwal->pesanan->proyek->nama_proyek }}</p>
    <p><strong>Alamat Proyek:</strong> {{ $suratJalan->jadwal->pesanan->proyek->alamat_proyek }}</p>
    <p><strong>Mandor Penanggung Jawab:</strong> {{ $suratJalan->jadwal->mandor->nama }} ({{ $suratJalan->jadwal->mandor->no_telepon }})</p>

    <table class="table">
        <thead>
            <tr>
                <th>No</th>
                <th>Kode Barang</th>
                <th>Nama Barang</th>
                <th>Jumlah Unit</th>
            </tr>
        </thead>
        <tbody>
            @foreach($suratJalan->jadwal->pesanan->detailPesanan as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->barang->kode_barang }}</td>
                <td>{{ $item->barang->nama_barang }}</td>
                <td>{{ $item->jumlah }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>