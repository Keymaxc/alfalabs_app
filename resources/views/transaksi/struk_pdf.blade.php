<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $pageTitle ?? 'Struk Transaksi' }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 8px; }
        .header h3 { margin: 0 0 4px 0; }
        .info, .totals { width: 100%; margin-bottom: 8px; }
        .info td { padding: 2px 0; }
        .totals td { padding: 3px 0; }
        .totals td.label { width: 60%; }
        .divider { border-top: 1px dashed #555; margin: 6px 0; }
    </style>
</head>
<body>
    <div class="header">
        <h3>Struk Transaksi - Alfalabs.co</h3>
        <div>No: {{ $transaksi->nomor_transaksi }}</div>
        <div>Tanggal: {{ $transaksi->created_at->format('d/m/Y H:i') }}</div>
    </div>

    <table class="info">
        <tr>
            <td>Kategori</td><td>: {{ $transaksi->kategoriProduk->nama_kategori ?? '-' }}</td>
        </tr>
        <tr>
            <td>Jumlah</td><td>: {{ number_format($transaksi->jumlah, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Harga Satuan</td><td>: Rp {{ number_format(($transaksi->total_harga / max($transaksi->jumlah,1)), 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Nama Pelanggan</td><td>: {{ $transaksi->nama_pelanggan ?? '-' }}</td>
        </tr>
        <tr>
            <td>Kontak</td><td>: {{ $transaksi->kontak_pelanggan ?? '-' }}</td>
        </tr>
        @if($transaksi->keterangan)
        <tr>
            <td>Keterangan</td><td>: {{ $transaksi->keterangan }}</td>
        </tr>
        @endif
    </table>

    <div class="divider"></div>

    <table class="totals">
        <tr>
            <td class="label">Total</td>
            <td class="value">Rp {{ number_format($transaksi->total_harga, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="label">Deposit</td>
            <td class="value">Rp {{ number_format($transaksi->deposit, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="label">Sisa Bayar</td>
            <td class="value">Rp {{ number_format(max($transaksi->pelunasan, 0), 0, ',', '.') }}</td>
        </tr>
    </table>

    <div class="divider"></div>
    <div style="text-align:center; margin-top:6px;">Terima kasih</div>
</body>
</html>
