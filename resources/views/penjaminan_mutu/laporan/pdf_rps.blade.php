<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Ketersediaan RPS - Mutu</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 10pt; background-color: #fff; color: #000; }
        .doc-title { text-align: center; font-weight: bold; font-size: 13pt; text-decoration: underline; text-transform: uppercase; margin: 8px 0 4px 0; }
        .summary-box { border: 1px solid #000; padding: 8px 12px; background-color: #f8fafc; font-size: 9.5pt; text-transform: uppercase; line-height: 1.5; margin: 12px 0; }
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

    <div class="doc-title">LAPORAN MONITORING KETERSEDIAAN RPS MATA KULIAH</div>

    <div class="summary-box">
        <div style="float: right; text-align: right;">
            TANGGAL CETAK: <span class="font-monospace">{{ \Carbon\Carbon::now()->format('d/m/Y') }}</span>
        </div>
        <strong>LINGKUP DATA:</strong> {{ $judulLingkup }}<br>
        <strong>TOTAL MATA KULIAH:</strong> <span class="font-monospace">{{ $totalMK }}</span> | 
        <strong>RPS TERSEDIA:</strong> <span class="font-monospace">{{ $sudahUpload }} ({{ $persentase }}%)</span> | 
        <strong>BELUM UNGGAH:</strong> <span class="font-monospace">{{ $totalMK - $sudahUpload }}</span>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 6%;">NO</th>
                <th style="width: 13%;">KODE MK</th>
                <th class="text-start" style="width: 34%;">NAMA MATA KULIAH</th>
                <th style="width: 7%;">SKS</th>
                <th style="width: 7%;">SMT</th>
                <th class="text-start" style="width: 21%;">DOSEN PENGAMPU</th>
                <th style="width: 12%;">STATUS RPS</th>
            </tr>
        </thead>
        <tbody>
            @forelse($dataMataKuliah as $index => $mk)
            <tr>
                <td class="text-center font-monospace">{{ $index + 1 }}</td>
                <td class="text-center font-monospace">{{ $mk->kode_mk }}</td>
                <td class="uppercase" style="font-weight: bold;">{{ $mk->nama_mk }}</td>
                <td class="text-center font-monospace">{{ $mk->sks }}</td>
                <td class="text-center font-monospace">{{ $mk->semester }}</td>
                <td class="uppercase">{{ $mk->dosen->nama_lengkap ?? 'BELUM DITENTUKAN' }}</td>
                <td class="text-center font-monospace">{{ $mk->file_rps ? 'TERSEDIA' : 'BELUM ADA' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center uppercase py-4">Data mata kuliah tidak ditemukan.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <table class="ttd">
        <tr>
            <td style="width: 55%;"></td>
            <td style="width: 45%;">
                <p>FAKFAK, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>KEPALA UNIT PENJAMINAN MUTU (UPM)</p>
                <br><br><br><br>
                <p><b><u>( ........................................... )</u></b></p>
            </td>
        </tr>
    </table>
</body>
</html>