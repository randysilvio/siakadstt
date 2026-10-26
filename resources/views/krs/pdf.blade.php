<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>KRS - {{ $mahasiswa->nama_lengkap }}</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 9.5pt; line-height: 1.25; color: #000; margin: 0; padding: 0; }
        @page { size: A4 portrait; margin: 1.0cm 1.2cm; }

        .document-title { text-align: center; font-weight: bold; font-size: 12pt; text-decoration: underline; text-transform: uppercase; margin: 6px 0 10px 0; }
        
        .student-info { margin-bottom: 8px; width: 100%; }
        .student-info table { width: 100%; border-collapse: collapse; }
        .student-info td { padding: 2px 0; vertical-align: top; text-transform: uppercase; font-size: 9pt; border: none; }
        .student-info .label { font-weight: bold; width: 125px; }
        
        table.main-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .main-table th, .main-table td { border: 1px solid #000; padding: 4px 6px; vertical-align: middle; }
        .main-table th { background-color: #212529; color: #ffffff; text-transform: uppercase; font-size: 8.5pt; text-align: center; font-weight: bold; }
        .main-table td { font-size: 8.8pt; }
        .text-center { text-align: center; }
        .font-monospace { font-family: 'Courier New', Courier, monospace; font-weight: bold; }
        
        .signature-table { width: 100%; margin-top: 10px; border-collapse: collapse; page-break-inside: avoid; }
        .signature-table td { border: none; text-align: center; padding: 2px; vertical-align: top; font-size: 9pt; width: 33.33%; }
        .signature-space { height: 42px; }
    </style>
</head>
<body>

    @include('partials._kop')

    @php
        $kotaTtd = \App\Models\Pengaturan::get('kota_domisili', 'Fakfak');
        $istilahPembimbing = \App\Models\Pengaturan::get('istilah_pembimbing', 'Dosen Tutor');
    @endphp

    <div class="document-title">KARTU RENCANA STUDI (KRS)</div>
    
    <div class="student-info">
        <table>
            <tr>
                <td class="label">NIM</td>
                <td style="width: 38%;">: <span class="font-monospace">{{ $mahasiswa->nim }}</span></td>
                <td class="label">TAHUN AKADEMIK</td>
                <td>: <span class="font-monospace">{{ $tahunAkademik->tahun }} ({{ strtoupper($tahunAkademik->semester) }})</span></td>
            </tr>
            <tr>
                <td class="label">NAMA LENGKAP</td>
                <td>: <strong>{{ $mahasiswa->nama_lengkap }}</strong></td>
                <td class="label">PROGRAM STUDI</td>
                <td>: {{ optional($mahasiswa->programStudi)->nama_prodi ?? '-' }}</td>
            </tr>
        </table>
    </div>

    <table class="main-table">
        <thead>
            <tr>
                <th style="width: 6%;">NO</th>
                <th style="width: 15%;">KODE MK</th>
                <th>NAMA MATA KULIAH</th>
                <th style="width: 9%;">KELAS</th>
                <th style="width: 8%;">SMT</th>
                <th style="width: 8%;">SKS</th>
            </tr>
        </thead>
        <tbody>
            @php $totalSks = 0; @endphp
            @forelse ($krs as $index => $mk)
                <tr>
                    <td class="text-center font-monospace">{{ $index + 1 }}</td>
                    <td class="text-center font-monospace">{{ $mk->kode_mk }}</td>
                    <td style="text-transform: uppercase; font-weight: bold;">{{ $mk->nama_mk }}</td>
                    <td class="text-center font-monospace">{{ $mk->pivot->nama_kelas ?? 'A' }}</td>
                    <td class="text-center font-monospace">{{ $mk->semester }}</td>
                    <td class="text-center font-monospace">{{ $mk->sks }}</td>
                </tr>
                @php $totalSks += $mk->sks; @endphp
            @empty
                <tr><td colspan="6" class="text-center" style="padding: 10px; text-transform: uppercase;">Belum ada mata kuliah yang diambil.</td></tr>
            @endforelse
            <tr style="background-color: #f2f2f2;">
                <td colspan="5" style="text-align: right; font-weight: bold; padding-right: 10px;">TOTAL BEBAN SKS DIAMBIL</td>
                <td class="text-center font-monospace" style="font-size: 9.5pt;">{{ $totalSks }}</td>
            </tr>
        </tbody>
    </table>

    <table class="signature-table">
        <tr>
            <td>
                Mengetahui,<br>
                Ketua Program Studi<br>
                <div class="signature-space"></div>
                <strong style="text-transform: uppercase; text-decoration: underline;">{{ optional(optional($mahasiswa->programStudi)->kaprodi)->nama_lengkap ?? '........................................' }}</strong><br>
                <span class="font-monospace" style="font-size: 8pt;">NIDN. {{ optional(optional($mahasiswa->programStudi)->kaprodi)->nidn ?? '-' }}</span>
            </td>
            <td>
                Menyetujui,<br>
                {{ $istilahPembimbing }}<br>
                <div class="signature-space"></div>
                <strong style="text-transform: uppercase; text-decoration: underline;">{{ $mahasiswa->dosenWali->nama_lengkap ?? '........................................' }}</strong><br>
                <span class="font-monospace" style="font-size: 8pt;">NIDN. {{ $mahasiswa->dosenWali->nidn ?? '-' }}</span>
            </td>
            <td>
                {{ $kotaTtd }}, {{ now()->translatedFormat('d F Y') }}<br>
                Mahasiswa Yang Bersangkutan,<br>
                <div class="signature-space"></div>
                <strong style="text-transform: uppercase; text-decoration: underline;">{{ $mahasiswa->nama_lengkap }}</strong><br>
                <span class="font-monospace" style="font-size: 8pt;">NIM. {{ $mahasiswa->nim }}</span>
            </td>
        </tr>
    </table>

    @include('partials._qr_verifikasi', ['jenisDoc' => 'KRS', 'idEntitas' => $mahasiswa->id])
</body>
</html>