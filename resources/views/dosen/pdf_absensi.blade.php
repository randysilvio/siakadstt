<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Absensi - {{ $mataKuliah->nama_mk }}</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 9pt; color: #000; margin: 0; padding: 0; }
        @page { size: A4 landscape; margin: 1cm; }
        
        table.kop-surat { width: 100%; border-bottom: 3px solid #000; padding-bottom: 5px; margin-bottom: 2px; }
        .kop-surat td { vertical-align: middle; text-align: center; }
        .kop-surat h2 { margin: 0 0 5px 0; font-size: 14pt; text-transform: uppercase; font-weight: bold; }
        .kop-surat h4 { margin: 0 0 5px 0; font-size: 11pt; text-transform: uppercase; font-weight: bold; }
        .kop-surat p { margin: 0; font-size: 9pt; }
        
        .title { text-align: center; font-weight: bold; font-size: 12pt; text-decoration: underline; text-transform: uppercase; margin: 15px 0; }
        
        table.info-kelas { width: 100%; margin-bottom: 15px; font-weight: bold; text-transform: uppercase; }
        .info-kelas td { padding: 2px 0; }
        
        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .data-table th, .data-table td { border: 1px solid #000; padding: 4px; vertical-align: middle; }
        .data-table th { background-color: #f0f0f0; text-transform: uppercase; font-size: 8pt; text-align: center; font-weight: bold; }
        .data-table td { font-size: 8pt; }
        
        .kolom-pertemuan { width: 3.5%; text-align: center; }
        
        .footer-sign { width: 100%; margin-top: 15px; page-break-inside: avoid; }
        .footer-sign td { width: 33%; text-align: center; vertical-align: bottom; }
    </style>
</head>
<body>
    <table class="kop-surat">
        <tr>
            <td style="width: 10%;">
                <img src="{{ public_path('images/logo.png') }}" style="width: 70px; height: auto;">
            </td>
            <td style="width: 90%;">
                <h2>STT GEREJA PROTESTAN INDONESIA (GPI) PAPUA</h2>
                <h4>TERAKREDITASI BAN-PT No. 1599/SK/BAN-PT/Ak.Ppj/PT/X/2022</h4>
                <p>JL. PATTIMURA NO.1 FAKFAK P. O. BOX 138 TELP. (0956) 22252</p>
                <p>FAKFAK - PAPUA BARAT</p>
            </td>
        </tr>
    </table>
    <div style="border-top: 1px solid #000; margin-bottom: 15px;"></div>

    <div class="title">DAFTAR HADIR MAHASISWA DAN NILAI UJIAN</div>

    <table class="info-kelas">
        <tr>
            <td style="width: 15%;">MATA KULIAH</td>
            <td style="width: 45%;">: {{ $mataKuliah->nama_mk }} ({{ $mataKuliah->kode_mk }})</td>
            <td style="width: 15%;">DOSEN PENGAMPU</td>
            <td style="width: 25%;">: {{ $dosen->nama_lengkap }}</td>
        </tr>
        <tr>
            <td>SEMESTER / SKS</td>
            <td>: {{ strtoupper($tahunAkademik->semester) }} {{ $tahunAkademik->tahun }} / {{ $mataKuliah->sks }} SKS</td>
            <td>PROGRAM STUDI</td>
            <td>: {{ optional($mataKuliah->kurikulum->programStudi)->nama_prodi ?? 'UMUM' }}</td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th rowspan="2" style="width: 3%;">NO</th>
                <th rowspan="2" style="width: 10%;">NIM</th>
                <th rowspan="2" style="width: 20%;">NAMA MAHASISWA</th>
                <th rowspan="2" style="width: 3%;">L/P</th>
                <th colspan="16">PERTEMUAN KE / TANGGAL</th>
                <th rowspan="2" style="width: 8%;">KET</th>
            </tr>
            <tr>
                @for($i = 1; $i <= 16; $i++)
                    <th class="kolom-pertemuan">{{ $i }}</th>
                @endfor
            </tr>
        </thead>
        <tbody>
            @forelse($mahasiswas as $index => $mhs)
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td style="text-align: center; font-family: monospace;">{{ $mhs->nim }}</td>
                    <td style="font-weight: bold; text-transform: uppercase;">{{ $mhs->nama_lengkap }}</td>
                    <td style="text-align: center;">{{ $mhs->jenis_kelamin }}</td>
                    @for($i = 1; $i <= 16; $i++)
                        <td></td>
                    @endfor
                    <td></td>
                </tr>
            @empty
                <tr>
                    <td colspan="21" style="text-align: center; padding: 15px;">TIDAK ADA DATA MAHASISWA YANG TERDAFTAR</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="footer-sign">
        <tr>
            <td>
                Mengetahui,<br>
                Ketua Program Studi<br><br><br><br><br>
                <strong>( ........................................ )</strong>
            </td>
            <td></td>
            <td>
                Fakfak, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
                Dosen Pengampu<br><br><br><br><br>
                <strong>{{ $dosen->nama_lengkap }}</strong><br>
                NIDN: {{ $dosen->nidn ?? '-' }}
            </td>
        </tr>
    </table>
</body>
</html>