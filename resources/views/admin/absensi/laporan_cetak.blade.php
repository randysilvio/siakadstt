<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekapitulasi Kehadiran Pegawai - STT GPI Papua</title>
    <style>
        body { 
            font-family: 'Times New Roman', Times, serif; 
            font-size: 11pt; 
            max-width: 210mm; 
            margin: 0 auto;
            padding: 20px;
            color: #000;
        }
        .header-title { text-align: center; margin-top: 15px; font-weight: bold; font-size: 12pt; text-decoration: underline; }
        .filter-info { text-align: center; margin-bottom: 25px; font-size: 11pt; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 30px; }
        th, td { border: 1px solid #000; padding: 5px 8px; text-align: left; vertical-align: middle; }
        th { text-align: center; font-weight: bold; }
        
        .text-center { text-align: center; }
        .text-bold { font-weight: bold; }

        /* Print Controls */
        .no-print { text-align: right; margin-bottom: 20px; border-bottom: 1px solid #ccc; padding-bottom: 15px; }
        .btn-print { background-color: #000; color: #fff; border: none; padding: 8px 20px; font-family: Arial, sans-serif; cursor: pointer; text-transform: uppercase; font-size: 12px; }
        
        .signature-container { width: 100%; display: table; margin-top: 40px; page-break-inside: avoid; }
        .signature-box { display: table-cell; width: 50%; text-align: center; vertical-align: bottom; }
        .signature-line { margin-top: 70px; font-weight: bold; text-decoration: underline; }

        @media print { 
            @page { size: A4 portrait; margin: 1.5cm; }
            .no-print { display: none !important; } 
            body { padding: 0; margin: 0; }
        }
    </style>
</head>
<body>

    <div class="no-print">
        <button onclick="window.print()" class="btn-print">Cetak Dokumen</button>
    </div>

    @include('partials._kop')

    <div class="header-title">REKAPITULASI KEHADIRAN PEGAWAI</div>
    
    @if(!empty($filterInfo))
        <div class="filter-info">
            Periode: {{ implode(' | ', $filterInfo) }}
        </div>
    @endif

    <table>
        <thead>
            <tr>
                <th rowspan="2" style="width: 5%;">No.</th>
                <th rowspan="2">Nama Lengkap</th>
                <th colspan="5">Distribusi Kehadiran (Hari)</th>
                <th rowspan="2" style="width: 12%;">Total Hadir</th>
            </tr>
            <tr>
                <th style="width: 9%;">Hadir</th>
                <th style="width: 9%;">Telat</th>
                <th style="width: 9%;">Sakit</th>
                <th style="width: 9%;">Izin</th>
                <th style="width: 9%;">Alpha</th>
            </tr>
        </thead>
        <tbody>
            @forelse($laporan as $item)
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td>{{ $item['nama'] }}</td>
                <td class="text-center">{{ $item['total_hadir'] }}</td>
                <td class="text-center">{{ $item['total_terlambat'] }}</td>
                <td class="text-center">{{ $item['total_sakit'] }}</td>
                <td class="text-center">{{ $item['total_izin'] }}</td>
                <td class="text-center">{{ $item['total_alpha'] }}</td>
                <td class="text-center text-bold">
                    {{ $item['total_hadir'] + $item['total_terlambat'] }} 
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center" style="padding: 15px;">Tidak ada data absensi yang tercatat pada periode ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="signature-container">
        <div class="signature-box">
            Mengetahui,<br>
            Pimpinan STT GPI Papua
            <div class="signature-line">( ........................................ )</div>
        </div>
        <div class="signature-box">
            Fakfak, {{ date('d F Y') }}<br>
            Kepala BAAK
            <div class="signature-line">( RANDY SILVIO )</div>
        </div>
    </div>

</body>
</html>