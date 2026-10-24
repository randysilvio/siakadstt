<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kartu Kendali Bimbingan - {{ $bimbingan->mahasiswa->nim }}</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 10pt; color: #000; line-height: 1.35; }
        @page { size: A4 portrait; margin: 1.5cm; }
        .doc-title { text-align: center; font-weight: bold; font-size: 13pt; text-decoration: underline; text-transform: uppercase; margin: 8px 0 14px 0; }
        table.meta-table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        table.meta-table td { padding: 3px 4px; border: none; font-size: 10pt; text-transform: uppercase; vertical-align: top; }
        table.data-table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        table.data-table th, table.data-table td { border: 1px solid #000; padding: 6px; font-size: 9.5pt; vertical-align: top; }
        table.data-table th { background-color: #212529; color: #ffffff; text-align: center; font-weight: bold; text-transform: uppercase; font-size: 9pt; }
        .text-center { text-align: center; }
        .font-monospace { font-family: 'Courier New', Courier, monospace; font-weight: bold; }
        .ttd { margin-top: 30px; width: 100%; border-collapse: collapse; font-size: 10pt; text-transform: uppercase; page-break-inside: avoid; }
        .ttd td { border: none; text-align: center; vertical-align: top; }
    </style>
</head>
<body>

    @include('partials._kop')

    <div class="doc-title">KARTU KENDALI BIMBINGAN {{ strtoupper($bimbingan->jenis_bimbingan) }}</div>

    <table class="meta-table">
        <tr>
            <td style="width: 22%;"><strong>NAMA MAHASISWA</strong></td>
            <td style="width: 78%;">: <strong>{{ $bimbingan->mahasiswa->nama_lengkap }}</strong> (NIM: <span class="font-monospace">{{ $bimbingan->mahasiswa->nim }}</span>)</td>
        </tr>
        <tr>
            <td><strong>PROGRAM STUDI</strong></td>
            <td>: {{ optional($bimbingan->mahasiswa->programStudi)->nama_prodi ?? '-' }}</td>
        </tr>
        <tr>
            <td><strong>JUDUL KAJIAN</strong></td>
            <td>: <strong>{{ $bimbingan->judul }}</strong></td>
        </tr>
        <tr>
            <td><strong>DOSEN PEMBIMBING 1</strong></td>
            <td>: {{ optional($bimbingan->pembimbing1)->nama_lengkap ?? '-' }}</td>
        </tr>
        @if($bimbingan->pembimbing2)
        <tr>
            <td><strong>DOSEN PEMBIMBING 2</strong></td>
            <td>: {{ $bimbingan->pembimbing2->nama_lengkap }}</td>
        </tr>
        @endif
        <tr>
            <td><strong>STATUS KELAYAKAN</strong></td>
            <td class="font-monospace">: {{ strtoupper($bimbingan->status) }}</td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 7%;">NO</th>
                <th style="width: 15%;">TANGGAL</th>
                <th style="width: 28%;">BAB / TOPIK KONSULTASI</th>
                <th style="width: 35%;">ARAHAN / CATATAN REVISI PEMBIMBING</th>
                <th style="width: 15%;">STATUS PARAF</th>
            </tr>
        </thead>
        <tbody>
            @forelse($bimbingan->logs as $idx => $log)
                <tr>
                    <td class="text-center font-monospace">{{ $idx + 1 }}</td>
                    <td class="text-center font-monospace">{{ $log->tanggal_bimbingan->format('d/m/Y') }}</td>
                    <td><strong>{{ strtoupper($log->topik_bab) }}</strong></td>
                    <td>{{ $log->arahan_dosen ?? $log->catatan_mahasiswa ?? '-' }}</td>
                    <td class="text-center font-monospace">{{ strtoupper($log->status_paraf) }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center" style="padding: 15px;">Belum ada catatan logbook konsultasi.</td></tr>
            @endforelse
        </tbody>
    </table>

    <table class="ttd">
        <tr>
            <td style="width: 50%;">
                Mengetahui,<br>
                Ketua Program Studi<br><br><br><br>
                <strong><u>( {{ optional(optional($bimbingan->mahasiswa->programStudi)->kaprodi)->nama_lengkap ?? '...........................................' }} )</u></strong>
            </td>
            <td style="width: 50%;">
                Fakfak, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
                Dosen Pembimbing Utama,<br><br><br><br>
                <strong><u>( {{ optional($bimbingan->pembimbing1)->nama_lengkap ?? '...........................................' }} )</u></strong>
            </td>
        </tr>
    </table>

</body>
</html>