<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Penerimaan Mahasiswa Baru</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; margin: 20px; }
        .header { text-align: center; border-bottom: 3px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        .header h2, .header h3 { margin: 2px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table, th, td { border: 1px solid #000; }
        th, td { padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .text-center { text-align: center; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body onload="window.print()">
    <div class="no-print" style="margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 10px; background: #0d9488; color: white; border: none; cursor: pointer;">Cetak Sekarang</button>
    </div>

    <div class="header">
        <h2>SEKOLAH TINGGI TEOLOGI GPI PAPUA</h2>
        <h3>LAPORAN DATA PENDAFTAR MAHASISWA BARU</h3>
        <p>Dicetak pada: {{ \Carbon\Carbon::now()->translatedFormat('d F Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-center">No</th>
                <th>No Pendaftaran</th>
                <th>Nama Pendaftar</th>
                <th>L/P</th>
                <th>Prodi Pilihan 1</th>
                <th>Gelombang</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pendaftars as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $item->no_pendaftaran }}</td>
                <td>{{ $item->user->name ?? '-' }}</td>
                <td class="text-center">{{ $item->jenis_kelamin }}</td>
                <td>{{ $item->prodi1->nama_prodi ?? '-' }}</td>
                <td>{{ $item->period->nama_gelombang ?? '-' }}</td>
                <td>{{ strtoupper(str_replace('_', ' ', $item->status_pendaftaran)) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center">Tidak ada data pendaftar yang sesuai.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>