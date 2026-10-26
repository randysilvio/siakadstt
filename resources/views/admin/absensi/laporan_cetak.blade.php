<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    @php
        $cfg        = \App\Models\Pengaturan::allCached();
        $kotaTtd    = $cfg['kota_domisili'] ?? 'Fakfak';
        $pimpinan   = $cfg['nama_rektor'] ?? '........................................';
        $sebutan    = $cfg['sebutan_pimpinan'] ?? 'Ketua';
        $singkatan  = $cfg['singkatan_kampus'] ?? 'STT GPI PAPUA';
        $kepalaBaak = $cfg['nama_kepala_baak'] ?? '........................................';
    @endphp
    <title>Rekapitulasi Kehadiran Pegawai - {{ $singkatan }}</title>
    <style>
        body { 
            font-family: 'Times New Roman', Times, serif; 
            font-size: 10.5pt; 
            max-width: 210mm; 
            margin: 0 auto;
            padding: 20px;
            color: #000;
        }
        .header-title { text-align: center; margin-top: 10px; font-weight: bold; font-size: 12.5pt; text-decoration: underline; text-transform: uppercase; }
        .filter-info { text-align: center; margin-bottom: 20px; font-size: 10pt; font-weight: bold; text-transform: uppercase; }
        
        table.data-table { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 30px; }
        table.data-table th, table.data-table td { border: 1px solid #000; padding: 5px 8px; text-align: left; vertical-align: middle; font-size: 9.5pt; }
        table.data-table th { background-color: #212529; color: #ffffff; text-align: center; font-weight: bold; text-transform: uppercase; font-size: 9pt; }
        
        .text-center { text-align: center !important; }
        .text-bold { font-weight: bold; }

        .no-print { text-align: right; margin-bottom: 20px; border-bottom: 1px solid #ccc; padding-bottom: 15px; }
        .btn-print { background-color: #212529; color: #fff; border: none; padding: 8px 20px; font-family: Arial, sans-serif; cursor: pointer; text-transform: uppercase; font-size: 11px; font-weight: bold; }
        
        .signature-container { width: 100%; display: table; margin-top: 35px; page-break-inside: avoid; }
        .signature-box { display: table-cell; width: 50%; text-align: center; vertical-align: bottom; font-size: 10pt; }
        .signature-line { margin-top: 60px; font-weight: bold; text-decoration: underline; text-transform: uppercase; }

        @media print { 
            @page { size: A4 portrait; margin: 1.5cm; }
            .no-print { display: none !important; } 
            body { padding: 0; margin: 0; -webkit-print-color-adjust: exact; }
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

    <table class="data-table">
        <thead>
            <tr>
                <th rowspan="2" style="width: 5%;">No.</th>
                <th rowspan="2">Nama Lengkap Pegawai / Dosen</th>
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
                <td class="text-bold">{{ strtoupper($item['nama']) }}</td>
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
            {{ $sebutan }} {{ $singkatan }}
            <div class="signature-line">( {{ $pimpinan }} )</div>
        </div>
        <div class="signature-box">
            {{ $kotaTtd }}, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
            Kepala BAAK / Administrasi
            <div class="signature-line">( {{ $kepalaBaak }} )</div>
        </div>
    </div>

</body>
</html>