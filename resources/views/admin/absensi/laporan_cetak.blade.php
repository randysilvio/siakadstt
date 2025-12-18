<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap Absensi Pegawai</title>
    <style>
        body { 
            font-family: 'Times New Roman', Times, serif; 
            font-size: 12px; 
            max-width: 210mm; 
            margin: 0 auto;
            padding: 20px;
        }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 6px 8px; text-align: left; }
        th { background-color: #f0f0f0; text-align: center; vertical-align: middle; }
        .text-center { text-align: center; }
        
        /* Tombol Cetak Manual */
        .action-bar { text-align: right; margin-bottom: 20px; border-bottom: 1px dashed #ccc; padding-bottom: 10px; }
        .btn-print { background-color: #0d6efd; color: white; border: none; padding: 8px 16px; cursor: pointer; border-radius: 4px; display: inline-flex; align-items: center; gap: 5px; font-size: 14px;}
        .btn-print:hover { background-color: #0b5ed7; }

        @media print { 
            @page { size: A4; margin: 2cm; }
            .no-print { display: none !important; } 
            body { padding: 0; margin: 0; }
        }
    </style>
</head>
<body>

    <div class="action-bar no-print">
        <button onclick="window.print()" class="btn-print">🖨️ Cetak Rekapitulasi</button>
    </div>

    @include('partials._kop')

    <h3 style="text-align: center; margin-top: 10px; text-transform: uppercase;">REKAPITULASI KEHADIRAN PEGAWAI</h3>
    
    @if(!empty($filterInfo))
        <div style="text-align: center; margin-bottom: 20px; font-weight: bold; font-size: 14px;">
            {{ implode(' | ', $filterInfo) }}
        </div>
    @endif

    <table>
        <thead>
            <tr>
                <th rowspan="2" style="width: 5%;">No</th>
                <th rowspan="2">Nama Pegawai</th>
                <th colspan="5">Rincian Kehadiran (Jumlah Hari)</th>
                <th rowspan="2" style="width: 10%;">Total<br>Kehadiran</th>
            </tr>
            <tr>
                <th style="width: 10%;">Hadir</th>
                <th style="width: 10%;">Terlambat</th>
                <th style="width: 10%;">Sakit</th>
                <th style="width: 10%;">Izin</th>
                <th style="width: 10%;">Alpha</th>
            </tr>
        </thead>
        <tbody>
            @forelse($laporan as $item)
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td style="font-weight: bold;">{{ $item['nama'] }}</td>
                <td class="text-center">{{ $item['total_hadir'] }}</td>
                <td class="text-center" style="color: {{ $item['total_terlambat'] > 0 ? 'red' : 'black' }}">
                    {{ $item['total_terlambat'] }}
                </td>
                <td class="text-center">{{ $item['total_sakit'] }}</td>
                <td class="text-center">{{ $item['total_izin'] }}</td>
                <td class="text-center" style="font-weight: bold; color: {{ $item['total_alpha'] > 0 ? 'red' : 'black' }}">
                    {{ $item['total_alpha'] }}
                </td>
                <td class="text-center" style="background-color: #f9f9f9; font-weight: bold;">
                    {{-- Total Kehadiran = Hadir + Terlambat (Asumsi terlambat tetap masuk kerja) --}}
                    {{ $item['total_hadir'] + $item['total_terlambat'] }} 
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center" style="padding: 20px;">Tidak ada data absensi pada periode ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 50px; width: 100%; display: table;">
        <div style="display: table-cell; width: 33%; text-align: center; vertical-align: top;">
            <br>Mengetahui,<br>Ketua STT GPI Papua<br><br><br><br>
            <strong>( ........................... )</strong>
        </div>
        <div style="display: table-cell; width: 33%;"></div>
        <div style="display: table-cell; width: 33%; text-align: center; vertical-align: top;">
            Fakfak, {{ date('d F Y') }}<br>Kepala BAAK<br><br><br><br>
            <strong>( RANDY SILVIO )</strong>
        </div>
    </div>

</body>
</html>