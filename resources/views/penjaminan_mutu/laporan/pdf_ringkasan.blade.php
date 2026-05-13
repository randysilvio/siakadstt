<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Eksekutif Mutu Akademik</title>
    <style>
        /* CSS STANDAR ENTERPRISE (CETAKAN FORMAL 0PX) */
        body { font-family: Arial, sans-serif; font-size: 11px; background-color: #fff; color: #000; line-height: 1.4; }
        
        .header-table { width: 100%; border-bottom: 4px solid #000; padding-bottom: 12px; margin-bottom: 15px; border-collapse: collapse; }
        .header-table td { border: none; vertical-align: middle; }
        .logo { width: 80px; height: auto; }
        .title-block { text-align: center; }
        .title-block h2 { margin: 0; font-size: 16px; font-weight: bold; text-transform: uppercase; }
        .title-block h3 { margin: 3px 0; font-size: 13px; font-weight: bold; text-transform: uppercase; }
        .title-block p { margin: 2px 0; font-size: 10px; text-transform: uppercase; }
        
        .print-meta { text-align: right; font-family: 'Courier New', Courier, monospace; font-size: 10px; font-weight: bold; margin-bottom: 15px; }
        
        .section-bar { font-size: 11px; font-weight: bold; text-transform: uppercase; background-color: #212529; color: #ffffff; padding: 5px 8px; margin-top: 15px; margin-bottom: 8px; border: none; }
        
        table.grid-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        table.grid-table th, table.grid-table td { border: 1px solid #000; padding: 6px 8px; font-size: 11px; text-align: center; vertical-align: middle; }
        table.grid-table th { background-color: #f8fafc; font-weight: bold; text-transform: uppercase; color: #000; }
        
        .text-start { text-align: left !important; }
        .font-monospace { font-family: 'Courier New', Courier, monospace; font-weight: bold; }
        .uppercase { text-transform: uppercase; }
        
        .ttd { margin-top: 40px; width: 100%; border-collapse: collapse; font-size: 11px; text-transform: uppercase; }
        .ttd td { border: none; text-align: center; vertical-align: top; }
    </style>
</head>
<body onload="window.print()">

    {{-- KOP SURAT --}}
    <table class="header-table">
        <tr>
            <td style="width: 15%; text-align: center;">
                <img src="{{ public_path('images/logo.png') }}" class="logo" alt="Logo">
            </td>
            <td style="width: 85%;" class="title-block">
                <h2>SEKOLAH TINGGI TEOLOGI GEREJA PROTESTAN INDONESIA PAPUA</h2>
                <h2>(STT GPI PAPUA) FAKFAK</h2>
                <h3>SISTEM PENJAMINAN MUTU INTERNAL (SPMI)</h3>
                <p style="text-decoration: underline; font-weight: bold; margin-top: 4px;">LAPORAN RINGKASAN KINERJA AKADEMIK EKSEKUTIF</p>
            </td>
        </tr>
    </table>

    <div class="print-meta">
        TANGGAL EKSTRAKSI DATA: {{ \Carbon\Carbon::now()->translatedFormat('d.m.Y') }}
    </div>

    {{-- A. INDIKATOR UTAMA --}}
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
                <td class="font-monospace fs-6">{{ number_format($jumlahMahasiswaAktif) }}</td>
                <td class="font-monospace fs-6">{{ number_format($jumlahDosen) }}</td>
                <td class="font-monospace fs-6">{{ $rasio }}</td>
                <td class="font-monospace fs-6">{{ number_format($rataIPK, 2) }}</td>
            </tr>
        </tbody>
    </table>

    {{-- B. TREN PERTUMBUHAN --}}
    <div class="section-bar">B. DISTRIBUSI ANGKATAN (5 TAHUN TERAKHIR)</div>
    <table class="grid-table" style="width: 50%;">
        <thead>
            <tr>
                <th>TAHUN MASUK</th>
                <th>TOTAL REGISTRASI</th>
            </tr>
        </thead>
        <tbody>
            @foreach($trenData as $data)
            <tr>
                <td class="font-monospace">{{ $data->tahun_masuk }}</td>
                <td class="font-monospace">{{ $data->total }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- C. HASIL EDOM --}}
    <div class="section-bar">C. HASIL EVALUASI DOSEN OLEH MAHASISWA (EDOM)</div>
    <p class="uppercase small mb-2" style="font-size: 10px;">
        PERIODE AKTIF: <strong class="font-monospace">{{ $sesiEdomAktif ? $sesiEdomAktif->nama_sesi : 'TIDAK ADA SESI AKTIF' }}</strong><br>
        REKAPITULASI 10 BESAR DOSEN BERDASARKAN INDEKS KEPUASAN PENGAJARAN:
    </p>
    <table class="grid-table">
        <thead>
            <tr>
                <th style="width: 8%;">PERINGKAT</th>
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
                <td class="text-start uppercase fw-bold">{{ $hasil->nama_lengkap }}</td>
                <td class="font-monospace">{{ number_format($skor, 2) }}</td>
                <td class="uppercase fw-bold">{{ $predikat }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="uppercase fw-bold py-3 text-center">Data survei EDOM belum tersedia pada pangkalan data.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- TANDA TANGAN --}}
    <table class="ttd">
        <tr>
            <td style="width: 50%;">
                <p class="mb-5">MENGETAHUI,<br>KETUA STT GPI PAPUA</p>
                <br><br><br>
                <p><b>( ........................................... )</b></p>
            </td>
            <td style="width: 50%;">
                <p class="mb-5">DISAHKAN OLEH,<br>KETUA PENJAMINAN MUTU (SPMI)</p>
                <br><br><br>
                <p><b>( ........................................... )</b></p>
            </td>
        </tr>
    </table>
</body>
</html>