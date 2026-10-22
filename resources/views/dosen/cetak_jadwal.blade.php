<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Jadwal Mengajar - {{ $dosen->nama_lengkap }}</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 10pt; color: #000; line-height: 1.4; }
        @page { size: A4 portrait; margin: 1.5cm; }
        .text-center { text-align: center; }
        .text-bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }
        .font-monospace { font-family: 'Courier New', Courier, monospace; font-weight: bold; }
        
        .doc-title { text-align: center; margin: 10px 0 18px 0; font-weight: bold; font-size: 13pt; text-decoration: underline; text-transform: uppercase; }
        
        .meta-table { width: 100%; margin-bottom: 15px; border: none; border-collapse: collapse; }
        .meta-table td { padding: 3px 0; vertical-align: top; border: none; text-transform: uppercase; font-size: 10pt; }
        .label { font-weight: bold; width: 160px; }

        table.schedule { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.schedule th, table.schedule td { border: 1px solid #000; padding: 6px 8px; font-size: 9.5pt; }
        table.schedule th { background-color: #212529; color: #ffffff; text-align: center; font-weight: bold; font-size: 9pt; text-transform: uppercase; }
        
        .signature-table { width: 100%; margin-top: 35px; page-break-inside: avoid; border-collapse: collapse; }
        .signature-table td { border: none; text-align: center; vertical-align: bottom; }
    </style>
</head>
<body>

    @include('partials._kop')

    <div class="doc-title">JADWAL MENGAJAR DOSEN</div>

    <table class="meta-table">
        <tr>
            <td class="label">NAMA TENAGA PENDIDIK</td>
            <td style="width: 38%;">: <strong>{{ $dosen->nama_lengkap }}</strong></td>
            <td class="label">TAHUN AKADEMIK</td>
            <td>: <span class="font-monospace">{{ $tahunAkademik->tahun ?? '-' }} ({{ strtoupper($tahunAkademik->semester ?? '-') }})</span></td>
        </tr>
        <tr>
            <td class="label">NIDN</td>
            <td class="font-monospace">: {{ $dosen->nidn ?? '-' }}</td>
            <td class="label">TANGGAL CETAK</td>
            <td class="font-monospace">: {{ now()->format('d/m/Y') }}</td>
        </tr>
    </table>

    <table class="schedule">
        <thead>
            <tr>
                <th style="width: 6%;">NO</th>
                <th style="width: 12%;">HARI</th>
                <th style="width: 16%;">WAKTU (WIT)</th>
                <th style="width: 34%;">MATA KULIAH</th>
                <th style="width: 8%;">SKS</th>
                <th style="width: 10%;">RUANG</th>
                <th style="width: 14%;">PRODI</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($jadwals as $index => $jadwal)
                <tr>
                    <td class="text-center font-monospace">{{ $index + 1 }}</td>
                    <td class="text-center text-bold uppercase">{{ $jadwal->hari }}</td>
                    <td class="text-center font-monospace">
                        {{ \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i') }} - 
                        {{ \Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i') }}
                    </td>
                    <td class="uppercase">
                        <div class="text-bold">{{ $jadwal->mataKuliah->nama_mk }}</div>
                        <small class="font-monospace">{{ $jadwal->mataKuliah->kode_mk }} | SMT {{ $jadwal->mataKuliah->semester }}</small>
                    </td>
                    <td class="text-center font-monospace">{{ $jadwal->mataKuliah->sks }}</td>
                    <td class="text-center font-monospace">{{ $jadwal->ruang ?? 'TBA' }}</td>
                    <td class="text-center uppercase">{{ optional(optional($jadwal->mataKuliah->kurikulum)->programStudi)->nama_prodi ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center uppercase" style="padding: 20px; font-weight: bold;">Belum terdapat jadwal mengajar yang terdaftar pada semester ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="signature-table">
        <tr>
            <td style="width: 55%;"></td>
            <td style="width: 45%;">
                Fakfak, {{ now()->translatedFormat('d F Y') }}<br>
                Dosen Pengampu,<br><br><br><br>
                <strong class="uppercase" style="text-decoration: underline;">{{ $dosen->nama_lengkap }}</strong><br>
                <span class="font-monospace" style="font-size: 9pt;">NIDN. {{ $dosen->nidn ?? '-' }}</span>
            </td>
        </tr>
    </table>

</body>
</html>