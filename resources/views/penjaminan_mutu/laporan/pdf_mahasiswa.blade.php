<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Populasi Mahasiswa (Student Body)</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 10pt; background-color: #fff; color: #000; }
        .doc-title { text-align: center; font-weight: bold; font-size: 13pt; text-decoration: underline; text-transform: uppercase; margin: 8px 0 3px 0; }
        .doc-sub { text-align: center; font-weight: bold; font-size: 10pt; text-transform: uppercase; margin-bottom: 14px; }
        table.data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.data-table th, table.data-table td { border: 1px solid #000; padding: 6px 8px; font-size: 9.5pt; vertical-align: middle; text-align: center; }
        table.data-table th { background-color: #212529; color: #ffffff; font-weight: bold; text-transform: uppercase; font-size: 9pt; }
        .font-monospace { font-family: 'Courier New', Courier, monospace; font-weight: bold; }
        .meta-footer { margin-top: 12px; font-size: 8.5pt; font-family: 'Courier New', Courier, monospace; text-transform: uppercase; line-height: 1.4; }
        .ttd { margin-top: 30px; width: 100%; border-collapse: collapse; font-size: 10pt; text-transform: uppercase; page-break-inside: avoid; }
        .ttd td { border: none; text-align: center; vertical-align: top; }
    </style>
</head>
<body>

    @include('partials._kop')

    <div class="doc-title">METRIK SELEKSI PMB & POPULASI MAHASISWA AKTIF (STUDENT BODY)</div>
    <div class="doc-sub">{{ $judulLingkup }}</div>

    <table class="data-table">
        <thead>
            <tr>
                <th rowspan="2" style="width: 6%;">NO</th>
                <th rowspan="2" style="width: 18%;">TAHUN AKADEMIK</th>
                <th rowspan="2" style="width: 14%;">DAYA TAMPUNG</th>
                <th colspan="2">CALON MAHASISWA</th>
                <th colspan="2">MAHASISWA BARU</th>
                <th colspan="2">MAHASISWA AKTIF</th>
            </tr>
            <tr>
                <th>PENDAFTAR</th>
                <th>LULUS SELEKSI</th>
                <th>REGULER</th>
                <th>TRANSFER</th>
                <th>REGULER</th>
                <th>TRANSFER</th>
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
        * DOKUMEN BORANG DITARIK OTOMATIS DARI BASIS DATA SIAKAD PER TANGGAL: {{ date('d/m/Y H:i') }} WIT
    </div>

    <table class="ttd">
        <tr>
            <td style="width: 50%;">
                <p>MENGETAHUI,<br>KETUA STT GPI PAPUA</p>
                <br><br><br><br>
                <p><b><u>( {{ \App\Models\Pengaturan::where('key', 'nama_rektor')->value('value') ?: '...........................................' }} )</u></b></p>
            </td>
            <td style="width: 50%;">
                <p>FAKFAK, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>KEPALA UNIT PENJAMINAN MUTU (UPM)</p>
                <br><br><br><br>
                <p><b><u>( ........................................... )</u></b></p>
            </td>
        </tr>
    </table>
</body>
</html>