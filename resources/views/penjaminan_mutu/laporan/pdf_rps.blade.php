<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Ketersediaan RPS - Mutu</title>
    <style>
        /* CSS STANDAR ENTERPRISE (CETAKAN FORMAL 0PX) */
        body { font-family: Arial, sans-serif; font-size: 11px; background-color: #fff; color: #000; }
        
        .header-table { width: 100%; border-bottom: 4px solid #000; padding-bottom: 12px; margin-bottom: 15px; border-collapse: collapse; }
        .header-table td { border: none; vertical-align: middle; }
        .logo { width: 80px; height: auto; }
        .title-block { text-align: center; }
        .title-block h2 { margin: 0; font-size: 16px; font-weight: bold; text-transform: uppercase; }
        .title-block h3 { margin: 3px 0; font-size: 13px; font-weight: bold; text-transform: uppercase; }
        .title-block p { margin: 3px 0; font-size: 12px; font-weight: bold; text-transform: uppercase; text-decoration: underline; }
        
        .summary-box { border: 1px solid #000; padding: 10px; background-color: #f8fafc; font-size: 11px; text-transform: uppercase; line-height: 1.5; margin-bottom: 15px; }
        
        table.data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.data-table th, table.data-table td { border: 1px solid #000; padding: 6px 8px; font-size: 11px; vertical-align: middle; }
        table.data-table th { background-color: #212529; color: #ffffff; text-align: center; font-weight: bold; text-transform: uppercase; }
        
        .text-center { text-align: center; }
        .text-start { text-align: left; }
        .font-monospace { font-family: 'Courier New', Courier, monospace; font-weight: bold; }
        .uppercase { text-transform: uppercase; }
        
        .status-ok { font-weight: bold; text-transform: uppercase; font-family: 'Courier New', Courier, monospace; }
        .status-no { font-weight: bold; text-transform: uppercase; font-family: 'Courier New', Courier, monospace; }
        
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
                <p>LAPORAN MONITORING KETERSEDIAAN RPS MATA KULIAH</p>
            </td>
        </tr>
    </table>

    <div class="summary-box">
        <div style="float: right; text-align: right;">
            TANGGAL CETAK:<br><span class="font-monospace">{{ \Carbon\Carbon::now()->translatedFormat('d.m.Y') }}</span>
        </div>
        <strong>LINGKUP DATA:</strong> {{ $judulLingkup }}<br>
        <strong>TOTAL MATA KULIAH:</strong> <span class="font-monospace">{{ $totalMK }}</span> | 
        <strong>STATUS TERISI:</strong> <span class="font-monospace">{{ $sudahUpload }} ({{ $persentase }}%)</span> | 
        <strong>KOSONG:</strong> <span class="font-monospace">{{ $totalMK - $sudahUpload }}</span>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">NO</th>
                <th style="width: 12%;">KODE MK</th>
                <th class="text-start" style="width: 33%;">NAMA MATA KULIAH</th>
                <th style="width: 6%;">SKS</th>
                <th style="width: 6%;">SMT</th>
                <th class="text-start" style="width: 23%;">DOSEN PENGAMPU</th>
                <th style="width: 15%;">STATUS RPS</th>
            </tr>
        </thead>
        <tbody>
            @forelse($dataMataKuliah as $index => $mk)
            <tr>
                <td class="text-center font-monospace">{{ $index + 1 }}</td>
                <td class="text-center font-monospace">{{ $mk->kode_mk }}</td>
                <td class="uppercase fw-bold">{{ $mk->nama_mk }}</td>
                <td class="text-center font-monospace">{{ $mk->sks }}</td>
                <td class="text-center font-monospace">{{ $mk->semester }}</td>
                <td class="uppercase">{{ $mk->dosen->nama_lengkap ?? 'BELUM DITENTUKAN' }}</td>
                <td class="text-center">
                    @if($mk->file_rps)
                        <span class="status-ok">TERSEDIA</span>
                    @else
                        <span class="status-no">BELUM ADA</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center uppercase fw-bold py-4">Data kurikulum mata kuliah tidak ditemukan.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <table class="ttd">
        <tr>
            <td style="width: 50%;"></td>
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