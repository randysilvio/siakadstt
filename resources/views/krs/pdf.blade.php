<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>KRS - {{ $mahasiswa->nama_lengkap }}</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 10pt; line-height: 1.35; color: #000; margin: 0; padding: 0; }
        @page { size: A4 portrait; margin: 1.5cm; }

        .document-title { text-align: center; font-weight: bold; font-size: 13pt; text-decoration: underline; text-transform: uppercase; margin: 10px 0 18px 0; }
        
        .student-info { margin-bottom: 15px; width: 100%; }
        .student-info table { width: 100%; border-collapse: collapse; }
        .student-info td { padding: 3px 0; vertical-align: top; text-transform: uppercase; font-size: 10pt; border: none; }
        .student-info .label { font-weight: bold; width: 140px; }
        
        table.main-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .main-table th, .main-table td { border: 1px solid #000; padding: 6px 8px; vertical-align: middle; }
        .main-table th { background-color: #212529; color: #ffffff; text-transform: uppercase; font-size: 9pt; text-align: center; font-weight: bold; }
        .main-table td { font-size: 9.5pt; }
        .text-center { text-align: center; }
        .font-monospace { font-family: 'Courier New', Courier, monospace; font-weight: bold; }
        
        .signature-table { width: 100%; margin-top: 25px; border-collapse: collapse; page-break-inside: avoid; }
        .signature-table td { border: none; text-align: center; padding: 5px; vertical-align: bottom; font-size: 10pt; }
        .signature-space { height: 60px; }
    </style>
</head>
<body>

    @include('partials._kop')

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
                <th style="width: 8%;">NO</th>
                <th style="width: 17%;">KODE MK</th>
                <th>NAMA MATA KULIAH</th>
                <th style="width: 10%;">SMT</th>
                <th style="width: 12%;">SKS</th>
            </tr>
        </thead>
        <tbody>
            @php $totalSks = 0; @endphp
            @forelse ($krs as $index => $mk)
                <tr>
                    <td class="text-center font-monospace">{{ $index + 1 }}</td>
                    <td class="text-center font-monospace">{{ $mk->kode_mk }}</td>
                    <td style="text-transform: uppercase; font-weight: bold;">{{ $mk->nama_mk }}</td>
                    <td class="text-center font-monospace">{{ $mk->semester }}</td>
                    <td class="text-center font-monospace">{{ $mk->sks }}</td>
                </tr>
                @php $totalSks += $mk->sks; @endphp
            @empty
                <tr><td colspan="5" class="text-center" style="padding: 15px; text-transform: uppercase;">Belum ada mata kuliah yang diambil.</td></tr>
            @endforelse
            <tr style="background-color: #f2f2f2;">
                <td colspan="4" style="text-align: right; font-weight: bold; padding-right: 12px;">TOTAL BEBAN SKS DIAMBIL</td>
                <td class="text-center font-monospace" style="font-size: 10.5pt;">{{ $totalSks }}</td>
            </tr>
        </tbody>
    </table>

    <table class="signature-table">
        <tr>
            <td style="width: 50%;">
                Mahasiswa,<br>
                <div class="signature-space"></div>
                <strong style="text-transform: uppercase; text-decoration: underline;">{{ $mahasiswa->nama_lengkap }}</strong><br>
                <span class="font-monospace" style="font-size: 9pt;">NIM. {{ $mahasiswa->nim }}</span>
            </td>
            <td style="width: 50%;">
                Fakfak, {{ now()->translatedFormat('d F Y') }}<br>
                Dosen Pembimbing Akademik (Wali),<br>
                <div class="signature-space"></div>
                <strong style="text-transform: uppercase; text-decoration: underline;">{{ $mahasiswa->dosenWali->nama_lengkap ?? '........................................' }}</strong><br>
                <span class="font-monospace" style="font-size: 9pt;">NIDN. {{ $mahasiswa->dosenWali->nidn ?? '-' }}</span>
            </td>
        </tr>
        <tr>
            <td colspan="2" style="padding-top: 25px;">
                Mengetahui / Mengesahkan,<br>
                Ketua Program Studi {{ optional($mahasiswa->programStudi)->nama_prodi }}<br>
                <div class="signature-space"></div>
                <strong style="text-transform: uppercase; text-decoration: underline;">{{ optional(optional($mahasiswa->programStudi)->kaprodi)->nama_lengkap ?? '........................................' }}</strong><br>
                <span class="font-monospace" style="font-size: 9pt;">NIDN. {{ optional(optional($mahasiswa->programStudi)->kaprodi)->nidn ?? '-' }}</span>
            </td>
        </tr>
    </table>
</body>
</html>