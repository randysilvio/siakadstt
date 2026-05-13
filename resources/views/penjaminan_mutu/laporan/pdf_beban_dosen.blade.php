<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Beban Kerja Dosen - SPMI</title>
    <style>
        /* CSS STANDAR ENTERPRISE (CETAKAN FORMAL 0PX) */
        body { font-family: Arial, sans-serif; font-size: 11px; background-color: #fff; color: #000; }
        
        .header-table { width: 100%; border-bottom: 4px solid #000; padding-bottom: 12px; margin-bottom: 20px; border-collapse: collapse; }
        .header-table td { border: none; vertical-align: middle; }
        .logo { width: 80px; height: auto; }
        .title-block { text-align: center; }
        .title-block h2 { margin: 0; font-size: 16px; font-weight: bold; text-transform: uppercase; }
        .title-block h3 { margin: 4px 0 0 0; font-size: 14px; font-weight: bold; text-transform: uppercase; text-decoration: underline; }
        .title-block p { margin: 5px 0 0 0; font-size: 10px; font-family: 'Courier New', Courier, monospace; font-weight: bold; text-transform: uppercase; }
        
        .info-box { margin-bottom: 15px; font-size: 11px; text-transform: uppercase; line-height: 1.4; border: 1px solid #000; padding: 8px; background-color: #f8fafc; }
        
        table.data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.data-table th, table.data-table td { border: 1px solid #000; padding: 6px 8px; font-size: 11px; vertical-align: middle; }
        table.data-table th { background-color: #212529; color: #ffffff; text-align: center; font-weight: bold; text-transform: uppercase; }
        
        .text-center { text-align: center; }
        .text-start { text-align: left; }
        .font-monospace { font-family: 'Courier New', Courier, monospace; font-weight: bold; }
        .uppercase { text-transform: uppercase; }
        
        .status-solid { font-weight: bold; text-transform: uppercase; font-family: 'Courier New', Courier, monospace; }
        
        .ttd { margin-top: 40px; width: 100%; border-collapse: collapse; font-size: 11px; text-transform: uppercase; }
        .ttd td { border: none; text-align: center; vertical-align: top; }
    </style>
</head>
<body onload="window.print()">

    {{-- KOP SURAT FORMAL --}}
    <table class="header-table">
        <tr>
            <td style="width: 15%; text-align: center;">
                <img src="{{ public_path('images/logo.png') }}" class="logo" alt="Logo">
            </td>
            <td style="width: 85%;" class="title-block">
                <h2>SEKOLAH TINGGI TEOLOGI GEREJA PROTESTAN INDONESIA PAPUA</h2>
                <h2>(STT GPI PAPUA) FAKFAK</h2>
                <h3>LAPORAN REKAPITULASI BEBAN MENGAJAR DOSEN</h3>
                <p>SEMESTER BERJALAN | TANGGAL CETAK: {{ date('d.m.Y') }}</p>
            </td>
        </tr>
    </table>

    <div class="info-box">
        <strong>PARAMETER PENJAMINAN MUTU:</strong><br>
        Laporan ini mendokumentasikan akumulasi beban SKS pengajaran aktif bagi seluruh dosen. Berdasarkan Standar SDM Perguruan Tinggi, rasio beban normal berkisar antara batas ekuivalen yang ditetapkan institusi.
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">NO</th>
                <th style="width: 15%;">NIDN</th>
                <th class="text-start" style="width: 25%;">NAMA LENGKAP DOSEN</th>
                <th class="text-start" style="width: 20%;">JABATAN AKADEMIK</th>
                <th style="width: 10%;">JML KELAS</th>
                <th style="width: 10%;">TOTAL SKS</th>
                <th style="width: 15%;">STATUS BEBAN</th>
            </tr>
        </thead>
        <tbody>
            @forelse($laporanDosen as $index => $dosen)
            <tr>
                <td class="text-center font-monospace">{{ $index + 1 }}</td>
                <td class="text-center font-monospace">{{ $dosen['nidn'] ?? '-' }}</td>
                <td class="uppercase fw-bold">{{ $dosen['nama'] }}</td>
                <td class="uppercase">{{ $dosen['jabatan'] }}</td>
                <td class="text-center font-monospace">{{ $dosen['jumlah_mk'] }}</td>
                <td class="text-center font-monospace fs-6">{{ $dosen['total_sks'] }}</td>
                <td class="text-center">
                    <span class="status-solid">{{ $dosen['status'] }}</span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center uppercase fw-bold py-4">Data dosen pengampu aktif tidak ditemukan.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <table class="ttd">
        <tr>
            <td style="width: 50%;">
                <p class="mb-5">MENGETAHUI,<br>KETUA STT GPI PAPUA</p>
                <br><br><br>
                <p><b>( ........................................... )</b></p>
            </td>
            <td style="width: 50%;">
                <p class="mb-1">FAKFAK, <span class="font-monospace">{{ date('d F Y') }}</span></p>
                <p class="mb-5">KETUA PENJAMINAN MUTU (SPMI),</p>
                <br><br><br>
                <p><b>( ........................................... )</b></p>
            </td>
        </tr>
    </table>
</body>
</html>