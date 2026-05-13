<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Populasi Mahasiswa (Student Body)</title>
    <style>
        /* CSS STANDAR ENTERPRISE (CETAKAN FORMAL 0PX) */
        body { font-family: Arial, sans-serif; font-size: 11px; background-color: #fff; color: #000; }
        
        .header { text-align: center; margin-bottom: 20px; border-bottom: 4px solid #000; padding-bottom: 12px; }
        .header h2 { margin: 0; font-size: 16px; font-weight: bold; text-transform: uppercase; }
        .header h3 { margin: 5px 0 2px 0; font-size: 14px; font-weight: bold; text-transform: uppercase; text-decoration: underline; }
        .header p { margin: 5px 0 0 0; font-size: 11px; font-weight: bold; text-transform: uppercase; }
        
        table.data-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        table.data-table th, table.data-table td { border: 1px solid #000; padding: 6px 8px; font-size: 11px; vertical-align: middle; text-align: center; }
        table.data-table th { background-color: #212529; color: #ffffff; font-weight: bold; text-transform: uppercase; }
        
        .font-monospace { font-family: 'Courier New', Courier, monospace; font-weight: bold; }
        .uppercase { text-transform: uppercase; }
        
        .meta-footer { margin-top: 15px; font-size: 10px; font-family: 'Courier New', Courier, monospace; text-transform: uppercase; line-height: 1.4; }
        
        .ttd { margin-top: 40px; width: 100%; border-collapse: collapse; font-size: 11px; text-transform: uppercase; }
        .ttd td { border: none; text-align: center; vertical-align: top; }
    </style>
</head>
<body onload="window.print()">

    <div class="header">
        <h2>SEKOLAH TINGGI TEOLOGI GEREJA PROTESTAN INDONESIA PAPUA</h2>
        <h2>(STT GPI PAPUA) FAKFAK</h2>
        <h3>METRIK SELEKSI PMB & POPULASI MAHASISWA AKTIF</h3>
        <p>{{ $judulLingkup }}</p>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th rowspan="2" style="width: 5%;">NO</th>
                <th rowspan="2" style="width: 15%;">TAHUN AKADEMIK</th>
                <th rowspan="2" style="width: 12%;">DAYA TAMPUNG</th>
                <th colspan="2">CALON MAHASISWA</th>
                <th colspan="2">MAHASISWA BARU</th>
                <th colspan="2">MAHASISWA AKTIF</th>
            </tr>
            <tr>
                <th style="background-color: #343a40;">PENDAFTAR</th>
                <th style="background-color: #343a40;">LULUS SELEKSI</th>
                <th style="background-color: #343a40;">REGULER</th>
                <th style="background-color: #343a40;">TRANSFER</th>
                <th style="background-color: #343a40;">REGULER</th>
                <th style="background-color: #343a40;">TRANSFER</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach($years as $year)
            @php $data = $laporan[$year]; @endphp
            <tr>
                <td class="font-monospace">{{ $no++ }}</td>
                <td class="font-monospace">
                    {{ $year }}/{{ $year + 1 }} 
                    @if($year == end($years)) (TS) 
                    @elseif($year == $years[count($years)-2]) (TS-1) 
                    @else (TS-2) @endif
                </td>
                <td class="font-monospace">{{ $data['daya_tampung'] }}</td>
                <td class="font-monospace">{{ $data['calon_pendaftar'] }}</td>
                <td class="font-monospace">{{ $data['lulus_seleksi'] }}</td>
                <td class="font-monospace">{{ $data['mahasiswa_baru'] }}</td>
                <td class="font-monospace">0</td>
                <td class="font-monospace">{{ $data['mahasiswa_aktif'] }}</td>
                <td class="font-monospace">0</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="meta-footer">
        * TS = TAHUN SEKARANG (TAHUN AKADEMIK PENUH TERAKHIR)<br>
        * DOKUMEN BORANG DITARIK OTOMATIS DARI BASIS DATA SIAKAD PER TANGGAL: {{ date('d.m.Y - H:i') }} WIT
    </div>

    <table class="ttd">
        <tr>
            <td style="width: 50%;">
                <p class="mb-5">MENGETAHUI,<br>KETUA STT GPI PAPUA</p>
                <br><br><br>
                <p><b>( ........................................... )</b></p>
            </td>
            <td style="width: 50%;">
                <p class="mb-1">FAKFAK, <span class="font-monospace">{{ date('d F Y') }}</span></p>
                <p class="mb-5">KETUA PENJAMINAN MUTU (SPMI),</p>
                <br><br><br>
                <p><b>( ........................................... )</b></p>
            </td>
        </tr>
    </table>
</body>
</html>