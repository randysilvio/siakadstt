<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Keterangan Aktif - {{ $mahasiswa->nim }}</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 11pt; color: #000; margin: 0; padding: 0; line-height: 1.5; }
        @page { size: A4 portrait; margin: 2cm; }
        
        table.kop-surat { width: 100%; border-bottom: 3px solid #000; padding-bottom: 5px; margin-bottom: 2px; }
        .kop-surat td { vertical-align: middle; text-align: center; }
        .kop-surat h2 { margin: 0 0 5px 0; font-size: 15pt; text-transform: uppercase; font-weight: bold; }
        .kop-surat h4 { margin: 0 0 5px 0; font-size: 11pt; text-transform: uppercase; font-weight: bold; }
        .kop-surat p { margin: 0; font-size: 10pt; }
        
        .title-container { text-align: center; margin: 30px 0; }
        .title { font-weight: bold; font-size: 12pt; text-decoration: underline; text-transform: uppercase; margin-bottom: 2px; }
        .nomor-surat { font-size: 11pt; }

        .content { margin-top: 10px; text-align: justify; }
        
        table.data-table { width: 100%; margin: 10px 0 15px 30px; }
        .data-table td { padding: 4px 0; vertical-align: top; }
        .data-table .label { width: 150px; }
        
        .footer-sign { width: 100%; margin-top: 50px; page-break-inside: avoid; }
        .footer-sign td { width: 50%; text-align: center; vertical-align: bottom; }
    </style>
</head>
<body>
    <table class="kop-surat">
        <tr>
            <td style="width: 15%;">
                <img src="{{ public_path('images/logo.png') }}" style="width: 80px; height: auto;">
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

    <div class="title-container">
        <div class="title">SURAT KETERANGAN AKTIF KULIAH</div>
        <div class="nomor-surat">Nomor: ........ /STT-GPI/AK/{{ \Carbon\Carbon::now()->format('Y') }}</div>
    </div>

    <div class="content">
        <p>Yang bertanda tangan di bawah ini:</p>
        
        <table class="data-table">
            <tr>
                <td class="label">Nama</td>
                <td>: <strong>Wensly Peniel Raprap, S.Th., M.Pd</strong></td>
            </tr>
            <tr>
                <td class="label">NUPTK</td>
                <td>: 8935756657130152</td>
            </tr>
            <tr>
                <td class="label">Jabatan</td>
                <td>: Ketua STT GPI Papua</td>
            </tr>
        </table>

        <p>Dengan ini menerangkan dengan sesungguhnya bahwa:</p>

        <table class="data-table">
            <tr>
                <td class="label">Nama Mahasiswa</td>
                <td>: <strong>{{ $mahasiswa->nama_lengkap }}</strong></td>
            </tr>
            <tr>
                <td class="label">NIM</td>
                <td>: {{ $mahasiswa->nim }}</td>
            </tr>
            <tr>
                <td class="label">Program Studi</td>
                <td>: {{ optional($mahasiswa->programStudi)->nama_prodi ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Semester</td>
                <td>: {{ strtoupper($periodeAktif->semester) }}</td>
            </tr>
            <tr>
                <td class="label">Tahun Akademik</td>
                <td>: {{ $periodeAktif->tahun }}</td>
            </tr>
        </table>

        <p>Adalah benar terdaftar sebagai mahasiswa <strong>Aktif</strong> di Sekolah Tinggi Theologia (STT) Gereja Protestan Indonesia (GPI) Papua pada semester dan tahun akademik tersebut di atas.</p>

        <p>Surat keterangan ini diberikan untuk keperluan: ........................................................................................................<br>............................................................................................................................................................................................</p>

        <p>Demikian surat keterangan ini dibuat dengan sesungguhnya untuk dapat dipergunakan sebagaimana mestinya.</p>
    </div>

    <table class="footer-sign">
        <tr>
            <td></td>
            <td>
                Fakfak, {{ \Carbon\Carbon::now()->locale('id')->isoFormat('D MMMM YYYY') }}<br>
                Ketua STT GPI Papua,<br><br><br><br><br>
                <strong><u>Wensly Peniel Raprap, S.Th., M.Pd</u></strong><br>
                NUPTK: 8935756657130152
            </td>
        </tr>
    </table>
</body>
</html>