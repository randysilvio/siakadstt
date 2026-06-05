<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Eksekutif - Institusi</title>
    <style>
        /* CSS STANDAR ENTERPRISE (CETAKAN FORMAL 0PX) */
        body { font-family: Arial, sans-serif; font-size: 11px; background-color: #fff; color: #000; line-height: 1.4; margin: 0; padding: 0; }
        
        /* KOP SURAT */
        .header-table { width: 100%; border-bottom: 4px solid #000; padding-bottom: 12px; margin-bottom: 15px; border-collapse: collapse; }
        .header-table td { border: none; vertical-align: middle; }
        .logo { width: 80px; height: auto; }
        .title-block { text-align: center; }
        .title-block h2 { margin: 0; font-size: 16px; font-weight: bold; text-transform: uppercase; }
        .title-block h3 { margin: 3px 0; font-size: 13px; font-weight: bold; text-transform: uppercase; }
        .title-block p { margin: 2px 0; font-size: 10px; text-transform: uppercase; }
        
        .print-meta { text-align: right; font-family: 'Courier New', Courier, monospace; font-size: 10px; font-weight: bold; margin-bottom: 15px; }
        
        /* KOMPONEN TABEL & SEKSI */
        .section-bar { font-size: 11px; font-weight: bold; text-transform: uppercase; background-color: #212529; color: #ffffff; padding: 5px 8px; margin-top: 15px; margin-bottom: 8px; border: none; }
        
        table.grid-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        table.grid-table th, table.grid-table td { border: 1px solid #000; padding: 6px 8px; font-size: 11px; text-align: center; vertical-align: middle; }
        table.grid-table th { background-color: #f8fafc; font-weight: bold; text-transform: uppercase; color: #000; }
        
        .text-start { text-align: left !important; }
        .font-monospace { font-family: 'Courier New', Courier, monospace; font-weight: bold; }
        .uppercase { text-transform: uppercase; }
        .fs-6 { font-size: 14px; }
        
        /* TANDA TANGAN */
        .ttd { margin-top: 40px; width: 100%; border-collapse: collapse; font-size: 11px; text-transform: uppercase; }
        .ttd td { border: none; text-align: center; vertical-align: top; }
        
        @media print {
            .no-print { display: none !important; }
            @page { margin: 1.5cm; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="text-end mb-4 no-print" style="margin-bottom: 20px; text-align: right;">
        <button onclick="window.print()" style="padding: 5px 15px; background: #212529; color: #fff; font-weight: bold; border: none; cursor: pointer;">
            Print Ulang
        </button>
        <button onclick="window.close()" style="padding: 5px 15px; background: #fff; color: #212529; font-weight: bold; border: 1px solid #212529; cursor: pointer; margin-left: 5px;">
            Tutup
        </button>
    </div>

    {{-- KOP SURAT --}}
    <table class="header-table">
        <tr>
            <td style="width: 15%; text-align: center;">
                <img src="{{ public_path('images/logo.png') }}" class="logo" alt="Logo">
            </td>
            <td style="width: 85%;" class="title-block">
                <h2>SEKOLAH TINGGI TEOLOGI GEREJA PROTESTAN INDONESIA PAPUA</h2>
                <h2>(STT GPI PAPUA) FAKFAK</h2>
                <h3>LAPORAN PIMPINAN</h3>
                <p style="text-decoration: underline; font-weight: bold; margin-top: 4px;">LAPORAN RINGKASAN KINERJA AKADEMIK EKSEKUTIF</p>
            </td>
        </tr>
    </table>

    <div class="print-meta">
        TANGGAL EKSTRAKSI DATA: {{ \Carbon\Carbon::now()->translatedFormat('d F Y H:i') }}
    </div>

    {{-- A. RINGKASAN METRIK --}}
    <div class="section-bar">A. RINGKASAN METRIK KINERJA UTAMA</div>
    <table class="grid-table">
        <thead>
            <tr>
                <th>MAHASISWA AKTIF</th>
                <th>PENDAFTAR BARU</th>
                <th>LULUSAN TAHUN INI</th>
                <th>PENDAPATAN SEMESTER INI</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="font-monospace fs-6">{{ $totalMahasiswaAktif }}</td>
                <td class="font-monospace fs-6">{{ $pendaftarTahunIni }}</td>
                <td class="font-monospace fs-6">{{ $mahasiswaLulusTahunIni }}</td>
                <td class="font-monospace fs-6">Rp {{ number_format($pendapatanSemesterIni, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    {{-- B. PERFORMA PROGRAM STUDI --}}
    <div class="section-bar">B. DISTRIBUSI PERFORMA PROGRAM STUDI</div>
    <table class="grid-table">
        <thead>
            <tr>
                <th style="width: 8%;">NO</th>
                <th class="text-start">PROGRAM STUDI</th>
                <th class="text-start">KEPALA PROGRAM STUDI</th>
                <th style="width: 20%;">MAHASISWA AKTIF</th>
                <th style="width: 15%;">JUMLAH DOSEN</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($kinerjaProdi as $index => $prodi)
                <tr>
                    <td class="font-monospace">{{ $index + 1 }}</td>
                    <td class="text-start uppercase fw-bold">{{ $prodi->nama_prodi }}</td>
                    <td class="text-start uppercase">{{ $prodi->kaprodi->nama_lengkap ?? 'BELUM DITENTUKAN' }}</td>
                    <td class="font-monospace">{{ $prodi->jumlah_mahasiswa_aktif }}</td>
                    <td class="font-monospace">{{ $prodi->jumlah_dosen }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="uppercase fw-bold py-3 text-center">Data program studi tidak ditemukan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- TANDA TANGAN --}}
    <table class="ttd">
        <tr>
            <td style="width: 50%;">
                {{-- Area kiri dikosongkan untuk perataan atau bisa diisi tanda tangan pihak lain jika perlu --}}
            </td>
            <td style="width: 50%;">
                <p class="mb-5">FAKFAK, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>MENGETAHUI,<br>PIMPINAN INSTITUSI</p>
                <br><br><br><br>
                <p><b>( ........................................... )</b></p>
            </td>
        </tr>
    </table>

</body>
</html>