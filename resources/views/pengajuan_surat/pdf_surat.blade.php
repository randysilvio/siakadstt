<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $pengajuanSurat->jenis_surat }} - {{ $mahasiswa->nim }}</title>
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
        .data-table .label { width: 170px; }
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
        $noSuratFormatted = ($pengajuanSurat->nomor_surat ?: str_pad((string)$pengajuanSurat->id, 3, '0', STR_PAD_LEFT)) . '/' . strtoupper(str_replace(' ', '-', $singkatanPt)) . '/AK/' . $pengajuanSurat->created_at->format('Y');
    @endphp

    <div class="title-container">
        <div class="title">{{ strtoupper($pengajuanSurat->jenis_surat) }}</div>
        <div class="nomor-surat">Nomor: {{ $noSuratFormatted }}</div>
    </div>

    <div class="content">
        <p>Pimpinan {{ $namaKampus }} ({{ $singkatanPt }}) di {{ $kotaTtd }} menerangkan dengan sesungguhnya bahwa:</p>

        <table class="data-table">
            <tr>
                <td class="label">Nama Lengkap</td>
                <td>: <strong>{{ strtoupper($mahasiswa->nama_lengkap) }}</strong></td>
            </tr>
            <tr>
                <td class="label">Nomor Induk Mahasiswa</td>
                <td>: <strong>{{ $mahasiswa->nim }}</strong></td>
            </tr>
            <tr>
                <td class="label">Tempat, Tanggal Lahir</td>
                <td>: {{ $mahasiswa->tempat_lahir ?? '-' }}, {{ $mahasiswa->tanggal_lahir ? $mahasiswa->tanggal_lahir->translatedFormat('d F Y') : '-' }}</td>
            </tr>
            <tr>
                <td class="label">Program Studi</td>
                <td>: {{ optional($mahasiswa->programStudi)->nama_prodi ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Tahun Akademik</td>
                <td>: {{ optional($periodeAktif)->tahun ?? date('Y') }} ({{ strtoupper(optional($periodeAktif)->semester ?? 'AKTIF') }})</td>
            </tr>
            @if($pengajuanSurat->tujuan_instansi)
            <tr>
                <td class="label">Instansi / Tujuan Surat</td>
                <td>: <strong>{{ $pengajuanSurat->tujuan_instansi }}</strong></td>
            </tr>
            @endif
        </table>

        <p>Adalah benar terdaftar sebagai mahasiswa pada {{ $namaKampus }} ({{ $singkatanPt }}). Surat resmi ini diterbitkan guna memenuhi keperluan: <strong>{{ $pengajuanSurat->keperluan }}</strong>.</p>

        <p>Demikian surat ini diterbitkan untuk dapat dipergunakan sebagaimana mestinya.</p>
    </div>

    <table class="footer-sign">
        <tr>
            <td></td>
            <td>
                {{ $kotaTtd }}, {{ ($pengajuanSurat->tanggal_disetujui ?? now())->translatedFormat('d F Y') }}<br>
                {{ $sebutanPimpinan }} {{ $singkatanPt }},<br>
                <div class="sign-space"></div>
                <strong><u>{{ $namaPimpinan }}</u></strong><br>
                {{ $labelIdPimpinan }}: {{ $nidnPimpinan }}
            </td>
        </tr>
    </table>

    @include('partials._qr_verifikasi', ['customToken' => $pengajuanSurat->kode_verifikasi])

</body>
</html>