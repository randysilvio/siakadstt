<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $pengajuanSurat->jenis_surat }} - {{ $mahasiswa->nim }}</title>
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
        .data-table .label { width: 170px; }
        .footer-sign { width: 100%; margin-top: 25px; page-break-inside: avoid; border-collapse: collapse; }
        .footer-sign td { width: 50%; text-align: center; vertical-align: bottom; border: none; }
        .sign-space { height: 65px; }
    </style>
</head>
<body>

    @include('partials._kop')

    @php
        $namaKetua = \App\Models\Pengaturan::where('key', 'nama_rektor')->value('value') ?: 'Wensly Peniel Raprap, S.Th., M.Pd';
        $noSuratFormatted = ($pengajuanSurat->nomor_surat ?: str_pad((string)$pengajuanSurat->id, 3, '0', STR_PAD_LEFT)) . '/STT-GPI/AK/' . $pengajuanSurat->created_at->format('Y');
    @endphp

    <div class="title-container">
        <div class="title">{{ strtoupper($pengajuanSurat->jenis_surat) }}</div>
        <div class="nomor-surat">Nomor: {{ $noSuratFormatted }}</div>
    </div>

    <div class="content">
        <p>Pimpinan Sekolah Tinggi Teologi (STT) Gereja Protestan Indonesia (GPI) Papua di Fakfak menerangkan dengan sesungguhnya bahwa:</p>

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

        <p>Adalah benar terdaftar sebagai mahasiswa pada Sekolah Tinggi Teologi (STT) Gereja Protestan Indonesia (GPI) Papua. Surat resmi ini diterbitkan guna memenuhi keperluan: <strong>{{ $pengajuanSurat->keperluan }}</strong>.</p>

        <p>Demikian surat ini diterbitkan untuk dapat dipergunakan sebagaimana mestinya.</p>
    </div>

    <table class="footer-sign">
        <tr>
            <td></td>
            <td>
                Fakfak, {{ ($pengajuanSurat->tanggal_disetujui ?? now())->translatedFormat('d F Y') }}<br>
                Ketua STT GPI Papua,<br>
                <div class="sign-space"></div>
                <strong><u>{{ $namaKetua }}</u></strong><br>
                NUPTK: 8935756657130152
            </td>
        </tr>
    </table>

    @include('partials._qr_verifikasi', ['customToken' => $pengajuanSurat->kode_verifikasi])

</body>
</html>