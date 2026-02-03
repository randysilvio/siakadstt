<!DOCTYPE html>
<html>
<head>
    <title>Laporan Ketersediaan RPS</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        
        /* Style Header dengan Logo */
        .header-table { width: 100%; border-bottom: 3px double #000; margin-bottom: 20px; padding-bottom: 10px; }
        .header-table td { border: none; vertical-align: middle; }
        .logo { width: 75px; height: auto; }
        .title { font-size: 16px; font-weight: bold; margin: 0; }
        .subtitle { font-size: 14px; margin: 0; font-weight: bold; }
        
        /* Style Tabel Data */
        table.data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.data-table th, table.data-table td { border: 1px solid #000; padding: 6px; text-align: left; }
        table.data-table th { background-color: #f2f2f2; text-align: center; font-weight: bold; }
        
        .text-center { text-align: center; }
        .badge-success { color: green; font-weight: bold; text-transform: uppercase; }
        .badge-danger { color: red; font-weight: bold; text-transform: uppercase; }
        
        .summary { margin-bottom: 15px; font-weight: bold; border: 1px solid #ddd; padding: 10px; background-color: #f9f9f9; }
        
        .ttd { margin-top: 40px; width: 100%; border: none; }
        .ttd td { border: none; text-align: center; }
    </style>
</head>
<body>
    {{-- KOP SURAT --}}
    <table class="header-table">
        <tr>
            <td width="15%" align="center">
                <img src="{{ public_path('images/logo.png') }}" class="logo">
            </td>
            <td width="85%" align="center">
                <h2 class="title">SEKOLAH TINGGI TEOLOGI (STT) GPI PAPUA</h2>
                <h3 class="subtitle">SISTEM PENJAMINAN MUTU INTERNAL (SPMI)</h3>
                <p style="margin:2px; font-size:11px;">Jl. Jenderal Sudirman, Fakfak, Papua Barat | Website: sttgpipapua.ac.id</p>
                <p style="margin:5px 0 0 0; font-weight:bold; text-decoration: underline;">LAPORAN MONITORING KETERSEDIAAN RPS</p>
            </td>
        </tr>
    </table>

    <div style="font-size: 11px; margin-bottom: 10px;">
        <strong>Lingkup Data:</strong> {{ $judulLingkup }} <br>
        <strong>Tanggal Cetak:</strong> {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}
    </div>

    <div class="summary">
        Total Mata Kuliah: {{ $totalMK }} <br>
        Sudah Upload RPS: <span style="color: green">{{ $sudahUpload }} ({{ $persentase }}%)</span> <br>
        Belum Upload: <span style="color: red">{{ $totalMK - $sudahUpload }}</span>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="10%">Kode MK</th>
                <th width="30%">Nama Mata Kuliah</th>
                <th width="5%">SKS</th>
                <th width="5%">Smt</th>
                <th width="25%">Dosen Pengampu</th>
                <th width="20%">Status RPS</th>
            </tr>
        </thead>
        <tbody>
            @forelse($dataMataKuliah as $index => $mk)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $mk->kode_mk }}</td>
                <td>{{ $mk->nama_mk }}</td>
                <td class="text-center">{{ $mk->sks }}</td>
                <td class="text-center">{{ $mk->semester }}</td>
                <td>{{ $mk->dosen->nama_lengkap ?? 'Belum diset' }}</td>
                <td class="text-center">
                    @if($mk->file_rps)
                        <span class="badge-success">Tersedia</span>
                        <br><small style="color:#555; font-size:9px;">(Terupload)</small>
                    @else
                        <span class="badge-danger">Belum Ada</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center">Tidak ada data mata kuliah untuk ditampilkan.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <table class="ttd">
        <tr>
            <td width="50%"></td>
            <td width="50%">
                Fakfak, {{ date('d F Y') }} <br>
                Ketua Penjaminan Mutu (SPMI),
                <br><br><br><br>
                <b>( ........................................... )</b>
            </td>
        </tr>
    </table>
</body>
</html>