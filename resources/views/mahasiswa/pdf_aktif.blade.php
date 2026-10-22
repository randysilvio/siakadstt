<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Mahasiswa Aktif</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 10pt; color: #000; margin: 0; padding: 0; line-height: 1.35; }
        @page { size: A4 portrait; margin: 1.5cm; }
        
        .title { text-align: center; font-weight: bold; font-size: 13pt; text-decoration: underline; text-transform: uppercase; margin: 10px 0 4px 0; }
        .subtitle { text-align: center; font-size: 10pt; font-weight: bold; text-transform: uppercase; margin-bottom: 18px; }

        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .data-table th, .data-table td { border: 1px solid #000; padding: 6px 8px; vertical-align: middle; }
        .data-table th { background-color: #212529; color: #ffffff; text-transform: uppercase; font-size: 9pt; text-align: center; font-weight: bold; }
        .data-table td { font-size: 9.5pt; text-transform: uppercase; }
        .font-monospace { font-family: 'Courier New', Courier, monospace; font-weight: bold; }
        
        .footer-sign { width: 100%; margin-top: 30px; page-break-inside: avoid; border-collapse: collapse; }
        .footer-sign td { width: 50%; text-align: center; vertical-align: bottom; border: none; }
    </style>
</head>
<body>

    @include('partials._kop')

    <div class="title">DAFTAR MAHASISWA AKTIF PERKULIAHAN</div>
    <div class="subtitle">
        SEMESTER {{ strtoupper($periodeAktif->semester) }} T.A. {{ $periodeAktif->tahun }} | ANGKATAN: {{ $angkatan }}
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 6%;">NO</th>
                <th style="width: 16%;">NIM</th>
                <th style="width: 36%;">NAMA LENGKAP</th>
                <th style="width: 8%;">L/P</th>
                <th style="width: 22%;">PROGRAM STUDI</th>
                <th style="width: 12%;">STATUS</th>
            </tr>
        </thead>
        <tbody>
            @forelse($mahasiswas as $index => $mhs)
                <tr>
                    <td style="text-align: center;" class="font-monospace">{{ $index + 1 }}</td>
                    <td style="text-align: center;" class="font-monospace">{{ $mhs->nim }}</td>
                    <td style="font-weight: bold;">{{ $mhs->nama_lengkap }}</td>
                    <td style="text-align: center;">{{ $mhs->jenis_kelamin ?? '-' }}</td>
                    <td style="text-align: center;">{{ optional($mhs->programStudi)->nama_prodi ?? '-' }}</td>
                    <td style="text-align: center; font-weight: bold;" class="font-monospace">AKTIF</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 15px;">TIDAK ADA DATA MAHASISWA AKTIF UNTUK KRITERIA INI</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="footer-sign">
        <tr>
            <td></td>
            <td>
                Fakfak, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
                Biro Administrasi Akademik & Kemahasiswaan (BAAK)<br><br><br><br>
                <strong><u>( ........................................ )</u></strong>
            </td>
        </tr>
    </table>
</body>
</html>