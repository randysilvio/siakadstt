<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Distribusi Nilai Akademik</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 11px; color: #000; }
        .judul-laporan { text-align: center; margin-bottom: 15px; }
        .judul-laporan h3 { margin: 0; font-size: 14px; text-transform: uppercase; text-decoration: underline; }
        .judul-laporan p { margin: 3px 0 0 0; font-size: 11px; font-weight: bold; }
        table.data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.data-table th, table.data-table td { border: 1px solid #000; padding: 5px 6px; }
        table.data-table th { background-color: #f0f0f0; text-align: center; font-weight: bold; text-transform: uppercase; font-size: 10px; }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .fw-bold { font-weight: bold; }
        .footer-ttd { width: 100%; margin-top: 30px; border: none; }
        .footer-ttd td { border: none; text-align: center; }
    </style>
</head>
<body>

    @include('partials._kop')

    <div class="judul-laporan">
        <h3>LAPORAN DISTRIBUSI NILAI MATA KULIAH (GRADE DISTRIBUTION)</h3>
        <p>{{ $judulLingkup }}</p>
        @if($tahunAkademikAktif)
            <p>TAHUN AKADEMIK AKTIF: {{ $tahunAkademikAktif->tahun }} ({{ strtoupper($tahunAkademikAktif->semester) }})</p>
        @endif
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th rowspan="2" style="width: 4%;">NO</th>
                <th rowspan="2" style="width: 10%;">KODE MK</th>
                <th rowspan="2" style="width: 26%;">NAMA MATA KULIAH</th>
                <th rowspan="2" style="width: 5%;">SMT</th>
                <th rowspan="2" style="width: 5%;">SKS</th>
                <th rowspan="2" style="width: 20%;">DOSEN PENGAMPU</th>
                <th colspan="5">SEBARAN NILAI HURUF</th>
                <th rowspan="2" style="width: 7%;">TOTAL DINILAI</th>
                <th rowspan="2" style="width: 8%;">% LULUS (A-D)</th>
            </tr>
            <tr>
                <th style="width: 3%;">A</th>
                <th style="width: 3%;">B</th>
                <th style="width: 3%;">C</th>
                <th style="width: 3%;">D</th>
                <th style="width: 3%;">E</th>
            </tr>
        </thead>
        <tbody>
            @forelse($dataDistribusi as $index => $row)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center fw-bold">{{ $row['kode_mk'] }}</td>
                    <td class="text-left">{{ $row['nama_mk'] }}</td>
                    <td class="text-center">{{ $row['semester'] }}</td>
                    <td class="text-center">{{ $row['sks'] }}</td>
                    <td class="text-left">{{ $row['dosen'] }}</td>
                    <td class="text-center">{{ $row['A'] }}</td>
                    <td class="text-center">{{ $row['B'] }}</td>
                    <td class="text-center">{{ $row['C'] }}</td>
                    <td class="text-center">{{ $row['D'] }}</td>
                    <td class="text-center">{{ $row['E'] }}</td>
                    <td class="text-center fw-bold">{{ $row['total'] }}</td>
                    <td class="text-center fw-bold">{{ $row['lulus_pct'] }}%</td>
                </tr>
            @empty
                <tr>
                    <td colspan="13" class="text-center">Data mata kuliah belum tersedia untuk lingkup ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="footer-ttd">
        <tr>
            <td style="width: 65%;"></td>
            <td style="width: 35%;">
                Fakfak, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
                Unit Penjaminan Mutu (UPM),
                <br><br><br><br>
                <strong><u>( ................................................. )</u></strong>
            </td>
        </tr>
    </table>

</body>
</html>