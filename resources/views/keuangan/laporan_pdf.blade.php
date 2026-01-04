<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Keuangan {{ $monthLabel }}</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color: #111827; }
        h2 { margin: 0 0 8px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { padding: 8px; border: 1px solid #e5e7eb; }
        th { background: #f3f4f6; text-align: left; }
        .badge { padding: 4px 8px; border-radius: 6px; font-size: 11px; }
        .badge-green { background: #dcfce7; color: #166534; }
        .badge-red { background: #fee2e2; color: #991b1b; }
        .bar-track { background: #eef2f7; height: 10px; border-radius: 999px; overflow: hidden; }
        .bar-fill { height: 100%; border-radius: 999px; }
        .bar-income { background: linear-gradient(90deg, #22c55e, #52e499); }
        .bar-expense { background: linear-gradient(90deg, #ef4444, #fb7185); }
        .small { font-size: 11px; color: #6b7280; }
    </style>
</head>
@php
    $maxWeekly = max(
        max($weeklyIncome ?? [0]),
        max($weeklyExpense ?? [0]),
        1
    );
    $isProfit = $labaRugi >= 0;
@endphp
<body>
    <h2>Laporan Keuangan Bulanan</h2>
    <div style="margin-bottom: 6px;">Periode: <strong>{{ $monthLabel }}</strong></div>

    <table>
        <tr>
            <th style="width: 40%;">Ringkasan</th>
            <th>Nilai</th>
            <th>Status</th>
        </tr>
        <tr>
            <td>Total Pemasukan</td>
            <td>Rp {{ number_format($totalPemasukan, 0, ',', '.') }}</td>
            <td><span class="badge badge-green">Pemasukan</span></td>
        </tr>
        <tr>
            <td>Total Pengeluaran / Kerugian</td>
            <td>Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</td>
            <td><span class="badge badge-red">Pengeluaran</span></td>
        </tr>
        <tr>
            <td>Laba / Rugi</td>
            <td>Rp {{ number_format($labaRugi, 0, ',', '.') }}</td>
            <td>
                <span class="badge {{ $isProfit ? 'badge-green' : 'badge-red' }}">
                    {{ $isProfit ? 'Laba' : 'Rugi' }}
                </span>
            </td>
        </tr>
    </table>

    <h3 style="margin-top:18px; margin-bottom:8px;">Grafik Mingguan (Pemasukan vs Pengeluaran)</h3>
    <table>
        <thead>
            <tr>
                <th style="width: 120px;">Minggu</th>
                <th>Pemasukan</th>
                <th>Pengeluaran</th>
            </tr>
        </thead>
        <tbody>
            @foreach($weeklyIncome as $week => $incomeValue)
                @php
                    $expenseValue = $weeklyExpense[$week] ?? 0;
                    $incomePct  = $maxWeekly > 0 ? ($incomeValue / $maxWeekly) * 100 : 0;
                    $expensePct = $maxWeekly > 0 ? ($expenseValue / $maxWeekly) * 100 : 0;
                @endphp
                <tr>
                    <td>Minggu {{ $week }}</td>
                    <td>
                        <div class="bar-track">
                            <div class="bar-fill bar-income" style="width: {{ $incomePct }}%;"></div>
                        </div>
                        <div class="small">Rp {{ number_format($incomeValue, 0, ',', '.') }}</div>
                    </td>
                    <td>
                        <div class="bar-track">
                            <div class="bar-fill bar-expense" style="width: {{ $expensePct }}%;"></div>
                        </div>
                        <div class="small">Rp {{ number_format($expenseValue, 0, ',', '.') }}</div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
