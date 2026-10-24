<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Berita Acara Perkuliahan (BAP) - {{ $verum_kela->mataKuliah->nama_mk }}</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 10pt; margin: 20px; color: #000; line-height: 1.35; }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .fw-bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }
        .font-monospace { font-family: 'Courier New', Courier, monospace; font-weight: bold; }
        .doc-title { text-align: center; font-weight: bold; font-size: 13pt; text-decoration: underline; text-transform: uppercase; margin: 10px 0 15px 0; }
        .info-table { width: 100%; margin-bottom: 15px; border-collapse: collapse; }
        .info-table td { padding: 3px 0; border: none; font-size: 10pt; }
        table.data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.data-table th, table.data-table td { border: 1px solid #000; padding: 6px; font-size: 9.5pt; vertical-align: top; }
        table.data-table th { background-color: #212529; color: #ffffff; text-align: center; text-transform: uppercase; font-size: 9pt; }
        .ttd { margin-top: 35px; width: 100%; border: none; page-break-inside: avoid; }
        .ttd td { border: none; text-align: center; vertical-align: top; }
        .no-print { text-align: right; margin-bottom: 15px; border-bottom: 1px solid #ccc; padding-bottom: 10px; }
        .btn-print { background-color: #212529; color: #fff; border: none; padding: 7px 16px; font-family: Arial, sans-serif; cursor: pointer; text-transform: uppercase; font-size: 11px; font-weight: bold; }
        @media print {
            body { -webkit-print-color-adjust: exact; margin: 0; }
            .no-print { display: none !important; }
            @page { size: A4 portrait; margin: 1.5cm; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="no-print">
        <button onclick="window.print()" class="btn-print">Cetak Berita Acara Perkuliahan (BAP)</button>
    </div>

    @include('partials._kop')

    <div class="doc-title">BERITA ACARA PERKULIAHAN (BAP) & JURNAL MENGAJAR DOSEN</div>

    <table class="info-table uppercase fw-bold">
        <tr>
            <td width="18%">MATA KULIAH</td>
            <td width="2%">:</td>
            <td width="42%">{{ $verum_kela->mataKuliah->nama_mk }} ({{ $verum_kela->mataKuliah->kode_mk }})</td>
            <td width="18%">TAHUN AKADEMIK</td>
            <td width="2%">:</td>
            <td width="18%">{{ optional($verum_kela->tahunAkademik)->tahun ?? '-' }} ({{ strtoupper(optional($verum_kela->tahunAkademik)->semester ?? '-') }})</td>
        </tr>
        <tr>
            <td>PROGRAM STUDI</td>
            <td>:</td>
            <td>{{ optional(optional($verum_kela->mataKuliah->kurikulum)->programStudi)->nama_prodi ?? 'STT GPI PAPUA' }}</td>
            <td>BEBAN SKS / SMT</td>
            <td>:</td>
            <td>{{ $verum_kela->mataKuliah->sks }} SKS / SEMESTER {{ $verum_kela->mataKuliah->semester }}</td>
        </tr>
        <tr>
            <td>DOSEN PENGAMPU</td>
            <td>:</td>
            <td>{{ optional($verum_kela->dosen)->nama_lengkap ?? '-' }}</td>
            <td>JUMLAH PESERTA</td>
            <td>:</td>
            <td>{{ $totalPeserta }} MAHASISWA</td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 7%;">PERT.<br>KE-</th>
                <th style="width: 15%;">TANGGAL &<br>WAKTU</th>
                <th style="width: 38%;">POKOK BAHASAN & REALISASI MATERI RPS</th>
                <th style="width: 20%;">METODE PEMBELAJARAN</th>
                <th style="width: 10%;">HADIR</th>
                <th style="width: 10%;">ABSEN</th>
            </tr>
        </thead>
        <tbody>
            @forelse($presensis as $sesi)
                @php
                    $jmlHadir = $sesi->kehadiran->count();
                    $jmlAbsen = max(0, $totalPeserta - $jmlHadir);
                @endphp
                <tr>
                    <td class="text-center font-monospace">{{ $sesi->pertemuan_ke }}</td>
                    <td class="text-center font-monospace">
                        {{ $sesi->waktu_buka->format('d/m/Y') }}<br>
                        <small>{{ $sesi->waktu_buka->format('H:i') }} WIT</small>
                    </td>
                    <td>
                        <div class="fw-bold uppercase">{{ $sesi->judul_pertemuan }}</div>
                        @if($sesi->materi_aktual)
                            <div style="font-size: 8.5pt; margin-top: 2px;">{{ $sesi->materi_aktual }}</div>
                        @endif
                    </td>
                    <td class="text-center uppercase" style="font-size: 8.5pt;">{{ $sesi->metode_pembelajaran ?? 'Tatap Muka / Diskusi' }}</td>
                    <td class="text-center font-monospace">{{ $jmlHadir }}</td>
                    <td class="text-center font-monospace">{{ $jmlAbsen }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center" style="padding: 15px;">Belum ada catatan pertemuan perkuliahan yang terekam.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="ttd">
        <tr>
            <td width="50%">
                Mengetahui / Mengesahkan,<br>
                Ketua Program Studi<br><br><br><br>
                <strong><u>( {{ optional(optional(optional($verum_kela->mataKuliah->kurikulum)->programStudi)->kaprodi)->nama_lengkap ?? '...........................................' }} )</u></strong>
            </td>
            <td width="50%">
                Fakfak, {{ now()->translatedFormat('d F Y') }}<br>
                Dosen Pengampu Mata Kuliah,<br><br><br><br>
                <strong><u>{{ optional($verum_kela->dosen)->nama_lengkap ?? '...........................................' }}</u></strong><br>
                <span class="font-monospace" style="font-size: 9pt;">NIDN. {{ optional($verum_kela->dosen)->nidn ?? '-' }}</span>
            </td>
        </tr>
    </table>

</body>
</html>