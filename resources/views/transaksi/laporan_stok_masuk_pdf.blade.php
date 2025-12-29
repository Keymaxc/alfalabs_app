<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $pageTitle ?? 'Laporan Stok Masuk' }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h2 { text-align: center; margin-bottom: 4px; }
        .subtitle { text-align: center; font-size: 11px; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 5px; }
        th, td { border: 1px solid #444; padding: 4px 6px; }
        th { background: #eee; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .footer { margin-top: 20px; font-size: 11px; text-align: right; }
    </style>
</head>
<body>
    <h2>{{ $pageTitle ?? 'Laporan Stok Masuk' }}</h2>

    <div class="subtitle">
        Dicetak pada: {{ now()->format('d/m/Y H:i') }}<br>
        @if(!empty($search))
            Filter pencarian: "<strong>{{ $search }}</strong>"
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-center" style="width: 30px;">No</th>
                <th>Nomor Transaksi</th>
                <th>Kategori</th>
                <th class="text-right">Jumlah Masuk</th>
                <th class="text-right">Estimasi Total</th>
                <th>Keterangan</th>
                <th>Tanggal</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($transaksis as $trx)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>{{ $trx->nomor_transaksi }}</td>
                    <td>{{ $trx->kategoriProduk->nama_kategori ?? '-' }}</td>
                    <td class="text-right">{{ number_format($trx->jumlah, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($trx->total_harga, 0, ',', '.') }}</td>
                    <td>{{ $trx->keterangan ?? '-' }}</td>
                    <td>{{ $trx->created_at->format('d/m/Y H:i') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">Belum ada stok masuk.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dicetak oleh: {{ auth()->user()->name ?? 'Sistem' }}
    </div>
</body>
</html>
