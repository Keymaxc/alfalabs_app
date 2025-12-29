<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $pageTitle ?? 'Laporan Transaksi' }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }
        h2 {
            text-align: center;
            margin-bottom: 4px;
        }
        .subtitle {
            text-align: center;
            font-size: 11px;
            margin-bottom: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }
        th, td {
            border: 1px solid #444;
            padding: 4px 6px;
        }
        th {
            background: #eee;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .footer {
            margin-top: 20px;
            font-size: 11px;
            text-align: right;
        }
    </style>
</head>
<body>
    <h2>{{ $pageTitle ?? 'Laporan Transaksi' }}</h2>

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
                <th>Jenis</th>
                <th>Kategori</th>
                <th>Nama Pelanggan</th>
                <th class="text-right">Jumlah</th>
                <th class="text-right">Total</th>
                <th class="text-right">Deposit</th>
                <th class="text-right">Pelunasan</th>
                <th>Tanggal</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($transaksis as $trx)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>{{ $trx->nomor_transaksi }}</td>
                    <td>{{ ucfirst($trx->jenis_transaksi) }}</td>
                    <td>{{ $trx->kategoriProduk->nama_kategori ?? '-' }}</td>
                    <td>{{ $trx->nama_pelanggan ?? '-' }}</td>
                    <td class="text-right">{{ number_format($trx->jumlah, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($trx->total_harga, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($trx->deposit, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($trx->pelunasan, 0, ',', '.') }}</td>
                    <td>{{ $trx->created_at->format('d/m/Y H:i') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center">Belum ada transaksi.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dicetak oleh: {{ auth()->user()->name ?? 'Sistem' }}
    </div>
</body>
</html>
