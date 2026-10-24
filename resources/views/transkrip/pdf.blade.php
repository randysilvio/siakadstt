<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Transkrip Nilai Akademik - {{ $mahasiswa->nim }}</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 10pt; background-color: #fff; color: #000; line-height: 1.3; }
        @page { size: A4 portrait; margin: 1.5cm; }
        .document-title { text-align: center; margin-top: 10px; margin-bottom: 16px; font-size: 13pt; font-weight: bold; text-transform: uppercase; text-decoration: underline; }
        .student-info { width: 100%; margin-bottom: 15px; border-collapse: collapse; font-size: 10pt; text-transform: uppercase; }
        .student-info td { padding: 3px 8px 3px 0; vertical-align: top; border: none; }
        .semester-title { font-size: 9.5pt; font-weight: bold; text-transform: uppercase; background-color: #212529; color: #ffffff; padding: 4px 8px; margin-top: 12px; margin-bottom: 0; }
        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        table.data-table th, table.data-table td { border: 1px solid #000; padding: 5px 8px; font-size: 9.5pt; vertical-align: middle; }
        table.data-table th { background-color: #f2f2f2; color: #000; font-weight: bold; text-align: center; text-transform: uppercase; }
        .summary-box { width: 100%; margin-top: 15px; margin-bottom: 20px; border: 2px solid #000; padding: 10px; background-color: #f8fafc; font-size: 10pt; text-transform: uppercase; page-break-inside: avoid; }
        .text-center { text-align: center; }
        .text-start { text-align: left; }
        .font-monospace { font-family: 'Courier New', Courier, monospace; font-weight: bold; }
        .uppercase { text-transform: uppercase; }
        .ttd { margin-top: 25px; width: 100%; border-collapse: collapse; font-size: 10pt; text-transform: uppercase; page-break-inside: avoid; }
        .ttd td { border: none; text-align: center; vertical-align: top; }
    </style>
</head>
<body onload="window.print()">

    @include('partials._kop')

    <div class="document-title">TRANSKRIP NILAI AKADEMIK {{ $mahasiswa->status_mahasiswa === 'Lulus' ? 'AKHIR' : 'SEMENTARA' }}</div>

    <table class="student-info">
        <tr>
            <td style="width: 18%;"><strong>NAMA LENGKAP</strong></td>
            <td style="width: 44%;">: <strong>{{ $mahasiswa->nama_lengkap }}</strong></td>
            <td style="width: 16%;"><strong>PROGRAM STUDI</strong></td>
            <td style="width: 22%;">: <strong>{{ optional($mahasiswa->programStudi)->nama_prodi ?? '-' }}</strong></td>
        </tr>
        <tr>
            <td><strong>NIM</strong></td>
            <td class="font-monospace">: {{ $mahasiswa->nim }}</td>
            <td><strong>TANGGAL CETAK</strong></td>
            <td class="font-monospace">: {{ now()->format('d/m/Y') }}</td>
        </tr>
    </table>

    @forelse ($krs_per_semester as $semester => $matkuls)
        <div class="semester-title">SEMESTER {{ $semester }}</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 15%;">KODE MK</th>
                    <th class="text-start" style="width: 55%;">NAMA MATA KULIAH</th>
                    <th style="width: 15%;">SKS</th>
                    <th style="width: 15%;">NILAI HURUF</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($matkuls as $mk)
                    <tr>
                        <td class="text-center font-monospace">{{ $mk->kode_mk }}</td>
                        <td class="uppercase" style="font-weight: bold;">{{ $mk->nama_mk }}</td>
                        <td class="text-center font-monospace">{{ $mk->sks }}</td>
                        <td class="text-center font-monospace">{{ optional($mk->pivot)->nilai ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @empty
        <p class="text-center uppercase font-monospace my-4">BELUM ADA DATA NILAI MATA KULIAH.</p>
    @endforelse

    <div class="summary-box">
        <table style="width: 100%; border: none;">
            <tr>
                <td style="border: none; width: 50%;">
                    <strong>TOTAL SKS LULUS:</strong> <span class="font-monospace">{{ $total_sks }} SKS</span>
                </td>
                <td style="border: none; width: 50%; text-align: right;">
                    <strong>INDEKS PRESTASI KUMULATIF (IPK):</strong> <span class="font-monospace">{{ number_format((float)$ipk, 2) }}</span>
                </td>
            </tr>
        </table>
    </div>

    <table class="ttd">
        <tr>
            <td style="width: 50%;"></td>
            <td style="width: 50%;">
                <p>FAKFAK, {{ now()->translatedFormat('d F Y') }}<br>KETUA PROGRAM STUDI,</p>
                <br><br><br><br>
                <p style="text-decoration: underline; font-weight: bold; margin-bottom: 2px;">
                    {{ optional(optional($mahasiswa->programStudi)->kaprodi)->nama_lengkap ?? '...........................................' }}
                </p>
                <p class="font-monospace" style="font-size: 9pt;">
                    NIDN. {{ optional(optional($mahasiswa->programStudi)->kaprodi)->nidn ?? '-' }}
                </p>
            </td>
        </tr>
    </table>

    @include('partials._qr_verifikasi', ['jenisDoc' => 'TRANSKRIP', 'idEntitas' => $mahasiswa->id])

</body>
</html>