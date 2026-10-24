<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kartu Peserta Ujian PMB - {{ $camaba->no_pendaftaran }}</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 10.5pt; color: #000; line-height: 1.4; }
        @page { size: A4 portrait; margin: 1.5cm; }
        .doc-title { text-align: center; font-weight: bold; font-size: 14pt; text-decoration: underline; text-transform: uppercase; margin: 10px 0 4px 0; }
        .doc-sub { text-align: center; font-family: 'Courier New', Courier, monospace; font-size: 10pt; font-weight: bold; margin-bottom: 18px; }
        .card-box { border: 2px solid #000; padding: 15px; margin-bottom: 18px; }
        table.main-info { width: 100%; border-collapse: collapse; }
        table.main-info td { padding: 4px 6px; vertical-align: top; border: none; font-size: 10pt; text-transform: uppercase; }
        .label { font-weight: bold; width: 165px; }
        .photo-box { width: 3cm; height: 4cm; border: 2px solid #000; text-align: center; vertical-align: middle; font-size: 8pt; margin: 0 auto; overflow: hidden; }
        .photo-box img { width: 100%; height: 100%; object-fit: cover; }
        .schedule-box { border: 1px solid #000; background-color: #f8fafc; padding: 10px 14px; margin-top: 15px; }
        .schedule-title { font-weight: bold; text-transform: uppercase; font-size: 10pt; border-bottom: 1px solid #000; padding-bottom: 4px; margin-bottom: 8px; }
        .rules-box { margin-top: 16px; font-size: 9pt; line-height: 1.45; }
        .font-monospace { font-family: 'Courier New', Courier, monospace; font-weight: bold; }
        .ttd { width: 100%; margin-top: 25px; border-collapse: collapse; page-break-inside: avoid; }
        .ttd td { width: 50%; text-align: center; vertical-align: bottom; border: none; font-size: 10pt; text-transform: uppercase; }
    </style>
</head>
<body>

    @include('partials._kop')

    <div class="doc-title">KARTU TANDA PESERTA UJIAN SELEKSI PMB</div>
    <div class="doc-sub">NOMOR REGISTRASI: {{ $camaba->no_pendaftaran }} | GELOMBANG: {{ strtoupper(optional($camaba->period)->nama_gelombang ?? 'REGULER') }}</div>

    <div class="card-box">
        <table class="main-info">
            <tr>
                <td style="width: 75%;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td class="label">NOMOR PESERTA</td>
                            <td class="font-monospace" style="font-size: 12pt;">: {{ $camaba->no_pendaftaran }}</td>
                        </tr>
                        <tr>
                            <td class="label">NAMA LENGKAP</td>
                            <td>: <strong>{{ $camaba->user->name ?? '-' }}</strong></td>
                        </tr>
                        <tr>
                            <td class="label">TEMPAT, TGL LAHIR</td>
                            <td>: {{ $camaba->tempat_lahir ?? '-' }}, {{ $camaba->tanggal_lahir ? \Carbon\Carbon::parse($camaba->tanggal_lahir)->translatedFormat('d F Y') : '-' }}</td>
                        </tr>
                        <tr>
                            <td class="label">JENIS KELAMIN</td>
                            <td>: {{ $camaba->jenis_kelamin == 'L' ? 'LAKI-LAKI' : 'PEREMPUAN' }}</td>
                        </tr>
                        <tr>
                            <td class="label">ASAL SEKOLAH & NISN</td>
                            <td>: {{ $camaba->sekolah_asal ?? '-' }} ({{ $camaba->nisn ?? '-' }})</td>
                        </tr>
                        <tr>
                            <td class="label">PILIHAN PRODI 1</td>
                            <td>: <strong>{{ optional($camaba->prodi1)->nama_prodi ?? '-' }}</strong></td>
                        </tr>
                        <tr>
                            <td class="label">PILIHAN PRODI 2</td>
                            <td>: {{ optional($camaba->prodi2)->nama_prodi ?? '-' }}</td>
                        </tr>
                    </table>
                </td>
                <td style="width: 25%; text-align: center; vertical-align: middle;">
                    <div class="photo-box">
                        @if($pasFotoBase64)
                            <img src="{{ $pasFotoBase64 }}" alt="Pas Foto">
                        @else
                            <div style="padding-top: 45px;">PAS FOTO<br>3 x 4</div>
                        @endif
                    </div>
                </td>
            </tr>
        </table>

        <div class="schedule-box">
            <div class="schedule-title">JADWAL & LOKASI PELAKSANAAN UJIAN SELEKSI</div>
            <table style="width: 100%; border-collapse: collapse; font-size: 9.5pt; text-transform: uppercase;">
                <tr>
                    <td style="width: 22%; font-weight: bold;">TANGGAL UJIAN</td>
                    <td style="width: 33%;" class="font-monospace">: {{ optional($camaba->period)->tanggal_ujian ? \Carbon\Carbon::parse($camaba->period->tanggal_ujian)->translatedFormat('d F Y') : 'SESUAI PENGUMUMAN PANITIA' }}</td>
                    <td style="width: 20%; font-weight: bold;">METODE UJIAN</td>
                    <td style="width: 25%;" class="font-monospace">: {{ strtoupper(optional($camaba->period)->jenis_ujian ?? 'OFFLINE / WAWANCARA') }}</td>
                </tr>
                <tr>
                    <td style="font-weight: bold;">WAKTU (WIT)</td>
                    <td class="font-monospace">: {{ optional($camaba->period)->jam_mulai_ujian ? substr($camaba->period->jam_mulai_ujian, 0, 5) . ' - ' . substr($camaba->period->jam_selesai_ujian, 0, 5) . ' WIT' : '08:00 WIT - SELESAI' }}</td>
                    <td style="font-weight: bold;">LOKASI / RUANG</td>
                    <td>: <strong>{{ optional($camaba->period)->lokasi_ujian ?? 'KAMPUS STT GPI PAPUA FAKFAK' }}</strong></td>
                </tr>
            </table>
        </div>
    </div>

    <div class="rules-box">
        <strong>TATA TERTIB PESERTA UJIAN SELEKSI PMB:</strong>
        <ol style="margin: 4px 0 0 0; padding-left: 18px;">
            <li>Peserta wajib membawa dan menunjukkan Kartu Tanda Peserta Ujian ini (cetak fisik) beserta KTP/Kartu Identitas asli saat pelaksanaan ujian dan wawancara.</li>
            <li>Peserta wajib hadir di lokasi ujian minimal 30 menit sebelum jadwal ujian dimulai dengan mengenakan pakaian rapi dan sopan (kemeja putih dan celana/rok hitam).</li>
        </ol>
    </div>

    <table class="ttd">
        <tr>
            <td>
                Tanda Tangan Peserta,<br><br><br><br>
                <strong><u>( {{ $camaba->user->name ?? '........................................' }} )</u></strong>
            </td>
            <td>
                Fakfak, {{ now()->translatedFormat('d F Y') }}<br>
                Panitia Penerimaan Mahasiswa Baru,<br><br><br><br>
                <strong><u>( PANITIA PMB STT GPI PAPUA )</u></strong>
            </td>
        </tr>
    </table>

</body>
</html>