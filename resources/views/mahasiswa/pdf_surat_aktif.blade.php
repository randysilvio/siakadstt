<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Keterangan Aktif - {{ $mahasiswa->nim }}</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 11pt; color: #000; margin: 0; padding: 0; line-height: 1.45; }
        @page { size: A4 portrait; margin: 1.5cm; }
        
        .title-container { text-align: center; margin: 15px 0 20px 0; }
        .title { font-weight: bold; font-size: 13pt; text-decoration: underline; text-transform: uppercase; margin-bottom: 3px; }
        .nomor-surat { font-size: 11pt; font-weight: bold; }

        .content { margin-top: 10px; text-align: justify; }
        .content p { margin: 10px 0; }
        
        table.data-table { width: 92%; margin: 6px 0 12px 30px; border-collapse: collapse; }
        .data-table td { padding: 3px 0; vertical-align: top; border: none; }
        .data-table .label { width: 160px; }
        
        .footer-sign { width: 100%; margin-top: 30px; page-break-inside: avoid; border-collapse: collapse; }
        .footer-sign td { width: 50%; text-align: center; vertical-align: bottom; border: none; }
        .sign-space { height: 70px; }
    </style>
</head>
<body>

    @include('partials._kop')

    @php
        $namaKetua = \App\Models\Pengaturan::where('key', 'nama_rektor')->value('value') ?: 'Wensly Peniel Raprap, S.Th., M.Pd';
    @endphp

    <div class="title-container">
        <div class="title">SURAT KETERANGAN AKTIF KULIAH</div>
        <div class="nomor-surat">Nomor: {{ $nomorSurat }}/STT-GPI/AK/{{ \Carbon\Carbon::now()->format('Y') }}</div>
    </div>

    <div class="content">
        <p>Yang bertanda tangan di bawah ini:</p>
        
        <table class="data-table">
            <tr>
                <td class="label">Nama</td>
                <td>: <strong>{{ $namaKetua }}</strong></td>
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
                <td>: <strong>{{ strtoupper($mahasiswa->nama_lengkap) }}</strong></td>
            </tr>
            <tr>
                <td class="label">NIM</td>
                <td>: <strong>{{ $mahasiswa->nim }}</strong></td>
            </tr>
            <tr>
                <td class="label">Program Studi</td>
                <td>: {{ optional($mahasiswa->programStudi)->nama_prodi ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Semester / Tahun Akademik</td>
                <td>: {{ strtoupper($periodeAktif->semester) }} / {{ $periodeAktif->tahun }}</td>
            </tr>
        </table>

        <p>Adalah benar terdaftar sebagai mahasiswa <strong>AKTIF</strong> pada Sekolah Tinggi Teologi (STT) Gereja Protestan Indonesia (GPI) Papua pada semester dan tahun akademik tersebut di atas.</p>

        <p>Surat keterangan ini diberikan untuk keperluan: <strong>{{ $keperluan }}</strong></p>

        <p>Demikian surat keterangan ini dibuat dengan sesungguhnya untuk dapat dipergunakan sebagaimana mestinya.</p>
    </div>

    <table class="footer-sign">
        <tr>
            <td></td>
            <td>
                Fakfak, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
                Ketua STT GPI Papua,<br>
                <div class="sign-space"></div>
                <strong><u>{{ $namaKetua }}</u></strong><br>
                NUPTK: 8935756657130152
            </td>
        </tr>
    </table>
</body>
</html>