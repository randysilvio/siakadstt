<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Mahasiswa Aktif</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 10pt; color: #000; margin: 0; padding: 0; }
        @page { size: A4 portrait; margin: 1.5cm; }
        
        table.kop-surat { width: 100%; border-bottom: 3px solid #000; padding-bottom: 5px; margin-bottom: 2px; }
        .kop-surat td { vertical-align: middle; text-align: center; }
        .kop-surat h2 { margin: 0 0 5px 0; font-size: 14pt; text-transform: uppercase; font-weight: bold; }
        .kop-surat h4 { margin: 0 0 5px 0; font-size: 11pt; text-transform: uppercase; font-weight: bold; }
        .kop-surat p { margin: 0; font-size: 9pt; }
        
        .title { text-align: center; font-weight: bold; font-size: 12pt; text-decoration: underline; text-transform: uppercase; margin: 20px 0 5px 0; }
        .subtitle { text-align: center; font-size: 10pt; font-weight: bold; text-transform: uppercase; margin-bottom: 20px; }

        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .data-table th, .data-table td { border: 1px solid #000; padding: 5px 8px; vertical-align: middle; }
        .data-table th { background-color: #f0f0f0; text-transform: uppercase; font-size: 9pt; text-align: center; font-weight: bold; }
        .data-table td { font-size: 9pt; text-transform: uppercase; }
        
        .footer-sign { width: 100%; margin-top: 30px; page-break-inside: avoid; }
        .footer-sign td { width: 50%; text-align: center; vertical-align: bottom; }
    </style>
</head>
<body>
    <table class="kop-surat">
        <tr>
            <td style="width: 15%;">
                <img src="{{ public_path('images/logo.png') }}" style="width: 70px; height: auto;">
            </td>
            <td style="width: 85%;">
                <h2>STT GEREJA PROTESTAN INDONESIA (GPI) PAPUA</h2>
                <h4>TERAKREDITASI BAN-PT No. 1599/SK/BAN-PT/Ak.Ppj/PT/X/2022</h4>
                <p>JL. PATTIMURA NO.1 FAKFAK P. O. BOX 138 TELP. (0956) 22252</p>
                <p>FAKFAK - PAPUA BARAT</p>
            </td>
        </tr>
    </table>
    <div style="border-top: 1px solid #000; margin-bottom: 15px;"></div>

    <div class="title">DAFTAR MAHASISWA AKTIF PERKULIAHAN</div>
    <div class="subtitle">
        SEMESTER {{ strtoupper($periodeAktif->semester) }} T.A. {{ $periodeAktif->tahun }}<br>
        ANGKATAN: {{ $angkatan }}
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">NO</th>
                <th style="width: 15%;">NIM</th>
                <th style="width: 40%;">NAMA LENGKAP</th>
                <th style="width: 25%;">PROGRAM STUDI</th>
                <th style="width: 15%;">STATUS KRS</th>
            </tr>
        </thead>
        <tbody>
            @forelse($mahasiswas as $index => $mhs)
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td style="text-align: center; font-family: monospace;">{{ $mhs->nim }}</td>
                    <td style="font-weight: bold;">{{ $mhs->nama_lengkap }}</td>
                    <td style="text-align: center;">{{ optional($mhs->programStudi)->nama_prodi ?? '-' }}</td>
                    <td style="text-align: center;">{{ $mhs->status_krs }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center; padding: 15px;">TIDAK ADA DATA MAHASISWA AKTIF UNTUK KRITERIA INI</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="footer-sign">
        <tr>
            <td></td>
            <td>
                Fakfak, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
                Biro Administrasi Akademik (BAAK)<br><br><br><br><br>
                <strong>( ........................................ )</strong>
            </td>
        </tr>
    </table>
</body>
</html>