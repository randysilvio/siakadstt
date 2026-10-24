<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Tracer Study Alumni - STT GPI Papua</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 10pt; color: #000; line-height: 1.35; }
        @page { size: A4 landscape; margin: 1.2cm; }
        .doc-title { text-align: center; font-weight: bold; font-size: 13pt; text-decoration: underline; text-transform: uppercase; margin: 8px 0 3px 0; }
        .doc-sub { text-align: center; font-size: 10pt; font-weight: bold; text-transform: uppercase; margin-bottom: 12px; }
        .summary-box { border: 1px solid #000; padding: 8px 12px; background-color: #f8fafc; font-size: 9.5pt; text-transform: uppercase; margin-bottom: 12px; }
        table.data-table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        table.data-table th, table.data-table td { border: 1px solid #000; padding: 5px 6px; font-size: 9pt; vertical-align: middle; }
        table.data-table th { background-color: #212529; color: #ffffff; text-align: center; font-weight: bold; text-transform: uppercase; }
        .text-center { text-align: center; }
        .font-monospace { font-family: 'Courier New', Courier, monospace; font-weight: bold; }
        .ttd { margin-top: 25px; width: 100%; border-collapse: collapse; font-size: 10pt; text-transform: uppercase; page-break-inside: avoid; }
        .ttd td { border: none; text-align: center; vertical-align: top; }
    </style>
</head>
<body>

    @include('partials._kop')

    <div class="doc-title">LAPORAN PELACAKAN LULUSAN (TRACER STUDY ALUMNI)</div>
    <div class="doc-sub">{{ $judulLingkup }}</div>

    <div class="summary-box">
        <strong>TOTAL RESPONDEN TERDATA:</strong> <span class="font-monospace">{{ $totalResponden }} ALUMNI</span> |
        <strong>RATA-RATA MASA TUNGGU KERJA/PELAYANAN:</strong> <span class="font-monospace">{{ $rataWaktuTunggu }} BULAN</span> |
        <strong>KESESUAIAN BIDANG ILMU:</strong> <span class="font-monospace">{{ $persenSesuai }}%</span>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 4%;">NO</th>
                <th style="width: 11%;">NIM</th>
                <th style="width: 20%;">NAMA LULUSAN</th>
                <th style="width: 15%;">PROGRAM STUDI</th>
                <th style="width: 7%;">THN LULUS</th>
                <th style="width: 14%;">STATUS PEKERJAAN</th>
                <th style="width: 19%;">INSTANSI / GEREJA & JABATAN</th>
                <th style="width: 5%;">TUNGGU (BLN)</th>
                <th style="width: 10%;">KESESUAIAN</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tracers as $index => $item)
                <tr>
                    <td class="text-center font-monospace">{{ $index + 1 }}</td>
                    <td class="text-center font-monospace">{{ $item->mahasiswa->nim ?? '-' }}</td>
                    <td style="font-weight: bold; text-transform: uppercase;">{{ $item->mahasiswa->nama_lengkap ?? '-' }}</td>
                    <td class="text-center">{{ strtoupper(optional(optional($item->mahasiswa)->programStudi)->nama_prodi ?? '-') }}</td>
                    <td class="text-center font-monospace">{{ $item->tahun_lulus }}</td>
                    <td class="text-center">{{ strtoupper($item->status_pekerjaan) }}</td>
                    <td><strong>{{ strtoupper($item->nama_instansi_gereja ?? '-') }}</strong><br><small>{{ strtoupper($item->jabatan_pekerjaan ?? '-') }}</small></td>
                    <td class="text-center font-monospace">{{ $item->waktu_tunggu_bulan }}</td>
                    <td class="text-center font-monospace">{{ strtoupper($item->kesesuaian_bidang) }}</td>
                </tr>
            @empty
                <tr><td colspan="9" class="text-center">Belum ada data Tracer Study yang tercatat.</td></tr>
            @endforelse
        </tbody>
    </table>

    <table class="ttd">
        <tr>
            <td style="width: 60%;"></td>
            <td style="width: 40%;">
                Fakfak, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
                Kepala Unit Penjaminan Mutu (UPM),<br><br><br><br>
                <strong><u>( ................................................. )</u></strong>
            </td>
        </tr>
    </table>

</body>
</html>