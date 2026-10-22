<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Beban Kerja Dosen - SPMI</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 10pt; background-color: #fff; color: #000; }
        .doc-title { text-align: center; font-weight: bold; font-size: 13pt; text-decoration: underline; text-transform: uppercase; margin: 8px 0 4px 0; }
        .doc-sub { text-align: center; font-size: 9.5pt; font-weight: bold; margin-bottom: 14px; font-family: 'Courier New', Courier, monospace; }
        table.data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.data-table th, table.data-table td { border: 1px solid #000; padding: 6px 8px; font-size: 9.5pt; vertical-align: middle; }
        table.data-table th { background-color: #212529; color: #ffffff; text-align: center; font-weight: bold; text-transform: uppercase; font-size: 9pt; }
        .text-center { text-align: center; }
        .text-start { text-align: left; }
        .font-monospace { font-family: 'Courier New', Courier, monospace; font-weight: bold; }
        .uppercase { text-transform: uppercase; }
        .ttd { margin-top: 30px; width: 100%; border-collapse: collapse; font-size: 10pt; text-transform: uppercase; page-break-inside: avoid; }
        .ttd td { border: none; text-align: center; vertical-align: top; }
    </style>
</head>
<body>

    @include('partials._kop')

    <div class="doc-title">LAPORAN REKAPITULASI BEBAN KERJA PENGAJARAN DOSEN</div>
    <div class="doc-sub">TANGGAL CETAK: {{ date('d/m/Y') }}</div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">NO</th>
                <th style="width: 15%;">NIDN</th>
                <th class="text-start" style="width: 30%;">NAMA LENGKAP DOSEN</th>
                <th class="text-start" style="width: 18%;">JABATAN AKADEMIK</th>
                <th style="width: 10%;">JML MK</th>
                <th style="width: 10%;">TOTAL SKS</th>
                <th style="width: 12%;">EVALUASI BEBAN</th>
            </tr>
        </thead>
        <tbody>
            @forelse($laporanDosen as $index => $dosen)
            <tr>
                <td class="text-center font-monospace">{{ $index + 1 }}</td>
                <td class="text-center font-monospace">{{ $dosen['nidn'] ?? '-' }}</td>
                <td class="uppercase" style="font-weight: bold;">{{ $dosen['nama'] }}</td>
                <td class="uppercase">{{ $dosen['jabatan'] }}</td>
                <td class="text-center font-monospace">{{ $dosen['jumlah_mk'] }}</td>
                <td class="text-center font-monospace">{{ $dosen['total_sks'] }}</td>
                <td class="text-center font-monospace">{{ $dosen['status'] }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center uppercase py-4">Data dosen pengampu aktif tidak ditemukan.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

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