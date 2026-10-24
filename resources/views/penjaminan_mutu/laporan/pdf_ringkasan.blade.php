<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Eksekutif Mutu Akademik</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 10pt; background-color: #fff; color: #000; line-height: 1.35; }
        @page { size: A4 portrait; margin: 1.5cm; }
        .doc-title { text-align: center; font-weight: bold; font-size: 13pt; text-decoration: underline; text-transform: uppercase; margin: 8px 0 4px 0; }
        .print-meta { text-align: center; font-family: 'Courier New', Courier, monospace; font-size: 9pt; font-weight: bold; margin-bottom: 14px; }
        .section-bar { font-size: 9.5pt; font-weight: bold; text-transform: uppercase; background-color: #212529; color: #ffffff; padding: 4px 8px; margin-top: 12px; margin-bottom: 6px; }
        table.grid-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        table.grid-table th, table.grid-table td { border: 1px solid #000; padding: 5px 8px; font-size: 9pt; text-align: center; vertical-align: middle; }
        table.grid-table th { background-color: #f2f2f2; font-weight: bold; text-transform: uppercase; color: #000; }
        .text-start { text-align: left !important; }
        .font-monospace { font-family: 'Courier New', Courier, monospace; font-weight: bold; }
        .uppercase { text-transform: uppercase; }
        .ttd { margin-top: 25px; width: 100%; border-collapse: collapse; font-size: 10pt; text-transform: uppercase; page-break-inside: avoid; }
        .ttd td { border: none; text-align: center; vertical-align: top; }
    </style>
</head>
<body>

    @include('partials._kop')

    <div class="doc-title">LAPORAN RINGKASAN KINERJA MUTU AKADEMIK (SPMI)</div>
    <div class="print-meta">TANGGAL EKSTRAKSI DATA: {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</div>

    <div class="section-bar">A. INDIKATOR KINERJA STRATEGIS UTAMA</div>
    <table class="grid-table">
        <thead>
            <tr>
                <th>MAHASISWA AKTIF</th>
                <th>DOSEN PENGAMPU</th>
                <th>RASIO DOSEN : MAHASISWA</th>
                <th>RATA-RATA IPK INSTITUSI</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="font-monospace">{{ number_format($jumlahMahasiswaAktif) }}</td>
                <td class="font-monospace">{{ number_format($jumlahDosen) }}</td>
                <td class="font-monospace">{{ $rasio }}</td>
                <td class="font-monospace">{{ number_format($rataIPK, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="section-bar">B. PRODUKTIVITAS TRIDHARMA DOSEN & PELACAKAN ALUMNI (TRACER STUDY)</div>
    <table class="grid-table">
        <thead>
            <tr>
                <th>PUBLIKASI & RISET DOSEN</th>
                <th>KEGIATAN PKM DOSEN</th>
                <th>RESPONDEN TRACER ALUMNI</th>
                <th>RATA-RATA WAKTU TUNGGU</th>
                <th>KESESUAIAN BIDANG KERJA</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="font-monospace">{{ $ringkasanTridharma['total_penelitian'] ?? 0 }} KARYA</td>
                <td class="font-monospace">{{ $ringkasanTridharma['total_pengabdian'] ?? 0 }} KEGIATAN</td>
                <td class="font-monospace">{{ $ringkasanTracer['responden'] ?? 0 }} ALUMNI</td>
                <td class="font-monospace">{{ $ringkasanTracer['waktu_tunggu'] ?? 0 }} BULAN</td>
                <td class="font-monospace">{{ $ringkasanTracer['kesesuaian'] ?? 0 }}%</td>
            </tr>
        </tbody>
    </table>

    <div class="section-bar">C. HASIL EVALUASI DOSEN OLEH MAHASISWA (EDOM)</div>
    <table class="grid-table">
        <thead>
            <tr>
                <th style="width: 10%;">PERINGKAT</th>
                <th class="text-start">NAMA LENGKAP DOSEN</th>
                <th style="width: 20%;">SKOR RATA-RATA</th>
                <th style="width: 25%;">PREDIKAT KINERJA</th>
            </tr>
        </thead>
        <tbody>
            @forelse($hasilEdom as $index => $hasil)
            @php
                $skor = $hasil->rata_rata_skor;
                $predikat = $skor >= 3.5 ? 'SANGAT BAIK' : ($skor >= 3.0 ? 'BAIK' : 'CUKUP');
            @endphp
            <tr>
                <td class="font-monospace">{{ $index + 1 }}</td>
                <td class="text-start uppercase" style="font-weight: bold;">{{ $hasil->nama_lengkap }}</td>
                <td class="font-monospace">{{ number_format($skor, 2) }}</td>
                <td class="uppercase" style="font-weight: bold;">{{ $predikat }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="uppercase py-3 text-center">Data survei EDOM belum tersedia pada sesi aktif.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-bar">D. PROFIL KINERJA KELULUSAN</div>
    <table class="grid-table">
        <thead>
            <tr>
                <th style="width: 20%;">TAHUN LULUS</th>
                <th style="width: 25%;">JUMLAH LULUSAN</th>
                <th style="width: 25%;">RATA-RATA IPK</th>
                <th style="width: 30%;">RATA-RATA MASA STUDI</th>
            </tr>
        </thead>
        <tbody>
            @forelse($dataLulusan as $lulusan)
            <tr>
                <td class="font-monospace">{{ $lulusan->tahun_lulus }}</td>
                <td class="font-monospace">{{ $lulusan->jumlah }} ORANG</td>
                <td class="font-monospace">{{ number_format($lulusan->rata_ipk, 2) }}</td>
                <td class="font-monospace">{{ $lulusan->rata_masa_studi }} TAHUN</td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="uppercase py-3 text-center">Belum ada data lulusan yang tercatat di dalam sistem.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <table class="ttd">
        <tr>
            <td style="width: 50%;">
                <p>MENGETAHUI,<br>KETUA STT GPI PAPUA</p>
                <br><br><br><br>
                <p><b><u>( {{ \App\Models\Pengaturan::where('key', 'nama_rektor')->value('value') ?: '...........................................' }} )</u></b></p>
            </td>
            <td style="width: 50%;">
                <p>FAKFAK, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>KEPALA UNIT PENJAMINAN MUTU (UPM)</p>
                <br><br><br><br>
                <p><b><u>( ........................................... )</u></b></p>
            </td>
        </tr>
    </table>
</body>
</html>