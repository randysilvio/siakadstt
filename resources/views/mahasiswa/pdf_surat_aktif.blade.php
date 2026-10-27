<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Keterangan Aktif - {{ $mahasiswa->nim }}</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 10.5pt; color: #000; margin: 0; padding: 0; line-height: 1.4; }
        @page { size: A4 portrait; margin: 1.2cm 1.5cm; }
        .title-container { text-align: center; margin: 10px 0 16px 0; }
        .title { font-weight: bold; font-size: 12.5pt; text-decoration: underline; text-transform: uppercase; margin-bottom: 2px; }
        .nomor-surat { font-size: 10.5pt; font-weight: bold; }
        .content { margin-top: 8px; text-align: justify; }
        .content p { margin: 8px 0; }
        table.data-table { width: 92%; margin: 4px 0 10px 25px; border-collapse: collapse; }
        .data-table td { padding: 2.5px 0; vertical-align: top; border: none; }
        .data-table .label { width: 165px; }
        .footer-sign { width: 100%; margin-top: 18px; page-break-inside: avoid; border-collapse: collapse; }
        .footer-sign td { width: 50%; text-align: center; vertical-align: bottom; border: none; }
        .sign-space { height: 55px; }
    </style>
</head>
<body>

    @include('partials._kop')

    @php
        $cfg = \App\Models\Pengaturan::allCached();
        $namaKampus = $cfg['nama_kampus'] ?? 'Sekolah Tinggi Teologi Gereja Protestan Indonesia Papua';
        $singkatanPt = $cfg['singkatan_kampus'] ?? 'STT GPI PAPUA';
        $sebutanPimpinan = $cfg['sebutan_pimpinan'] ?? 'Ketua';
        $namaPimpinan = $cfg['nama_rektor'] ?? 'Wensly Peniel Raprap, S.Th., M.Pd';
        $labelIdPimpinan = $cfg['label_id_rektor'] ?? 'NUPTK';
        $nidnPimpinan = $cfg['nidn_rektor'] ?? '8935756657130152';
        $kotaTtd = $cfg['kota_domisili'] ?? 'Fakfak';
    @endphp

    <div class="title-container">
        <div class="title">SURAT KETERANGAN AKTIF KULIAH</div>
        <div class="nomor-surat">Nomor: {{ $nomorSurat }}/{{ strtoupper(str_replace(' ', '-', $singkatanPt)) }}/AK/{{ \Carbon\Carbon::now()->format('Y') }}</div>
    </div>

    <div class="content">
        <p>Yang bertanda tangan di bawah ini:</p>
        
        <table class="data-table">
            <tr>
                <td class="label">Nama</td>
                <td>: <strong>{{ $namaPimpinan }}</strong></td>
            </tr>
            <tr>
                <td class="label">{{ $labelIdPimpinan }}</td>
                <td>: {{ $nidnPimpinan }}</td>
            </tr>
            <tr>
                <td class="label">Jabatan</td>
                <td>: {{ $sebutanPimpinan }} {{ $singkatanPt }}</td>
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

        <p>Adalah benar terdaftar sebagai mahasiswa <strong>AKTIF</strong> pada {{ $namaKampus }} ({{ $singkatanPt }}) pada semester dan tahun akademik tersebut di atas.</p>

        <p>Surat keterangan ini diberikan untuk keperluan: <strong>{{ $keperluan }}</strong></p>

        <p>Demikian surat keterangan ini dibuat dengan sesungguhnya untuk dapat dipergunakan sebagaimana mestinya.</p>
    </div>

    <table class="footer-sign">
        <tr>
            <td></td>
            <td>
                {{ $kotaTtd }}, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
                {{ $sebutanPimpinan }} {{ $singkatanPt }},<br>
                <div class="sign-space"></div>
                <strong><u>{{ $namaPimpinan }}</u></strong><br>
                {{ $labelIdPimpinan }}: {{ $nidnPimpinan }}
            </td>
        </tr>
    </table>

    @include('partials._qr_verifikasi', ['jenisDoc' => 'SURATAKTIF', 'idEntitas' => $mahasiswa->id, 'customToken' => $tokenDokumen ?? null])

</body>
</html>