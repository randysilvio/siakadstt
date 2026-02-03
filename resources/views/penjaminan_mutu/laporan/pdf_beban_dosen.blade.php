<!DOCTYPE html>
<html>
<head>
    <title>Laporan Beban Kerja Dosen</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; }
        .header-table { width: 100%; border-bottom: 3px double #000; margin-bottom: 20px; padding-bottom: 10px; }
        .header-table td { border: none; vertical-align: middle; }
        .logo { width: 70px; }
        
        table.data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.data-table th, table.data-table td { border: 1px solid #000; padding: 6px; text-align: left; }
        table.data-table th { background-color: #f2f2f2; text-align: center; font-weight: bold; }
        
        .badge-warning { color: orange; font-weight: bold; }
        .badge-danger { color: red; font-weight: bold; }
        .badge-success { color: green; font-weight: bold; }
        
        .ttd { margin-top: 40px; width: 100%; border: none; }
        .ttd td { border: none; text-align: center; }
    </style>
</head>
<body>
    {{-- KOP SURAT --}}
    <table class="header-table">
        <tr>
            <td width="10%" align="center">
                <img src="{{ public_path('images/logo.png') }}" class="logo">
            </td>
            <td width="90%" align="center">
                <h2 style="margin:0">SEKOLAH TINGGI TEOLOGI (STT) GPI PAPUA</h2>
                <h3 style="margin:5px 0">LAPORAN BEBAN MENGAJAR DOSEN</h3>
                <small>Semester Berjalan | Cetak: {{ date('d F Y') }}</small>
            </td>
        </tr>
    </table>

    <div style="margin-bottom: 15px;">
        <strong>Keterangan:</strong><br>
        Laporan ini merangkum total SKS Pengajaran yang diampu oleh Dosen Tetap dan Tidak Tetap.
        <br><i>Standar Nasional Pendidikan Tinggi: Maksimal 16 SKS per semester (Termasuk Penelitian & Pengabdian).</i>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="15%">NIDN</th>
                <th width="25%">Nama Dosen</th>
                <th width="15%">Jabatan Akademik</th>
                <th width="10%">Jml Kelas</th>
                <th width="10%">Total SKS</th>
                <th width="20%">Status Beban</th>
            </tr>
        </thead>
        <tbody>
            @forelse($laporanDosen as $index => $dosen)
            <tr>
                <td align="center">{{ $index + 1 }}</td>
                <td>{{ $dosen['nidn'] ?? '-' }}</td>
                <td>{{ $dosen['nama'] }}</td>
                <td>{{ $dosen['jabatan'] }}</td>
                <td align="center">{{ $dosen['jumlah_mk'] }}</td>
                <td align="center" style="font-weight: bold;">{{ $dosen['total_sks'] }}</td>
                <td align="center">
                    @if(str_contains($dosen['status'], 'Kelebihan'))
                        <span class="badge-danger">{{ $dosen['status'] }}</span>
                    @elseif(str_contains($dosen['status'], 'Kurang'))
                        <span class="badge-warning">{{ $dosen['status'] }}</span>
                    @else
                        <span class="badge-success">{{ $dosen['status'] }}</span>
                    @endif
                </td>
            </tr>
            {{-- Opsional: Tampilkan detail MK jika perlu --}}
            {{-- 
            @foreach($dosen['mata_kuliahs'] as $mk)
            <tr>
                <td></td>
                <td colspan="6" style="font-size: 9px; color: #555; border-top: none;">
                    - {{ $mk->kode_mk }} {{ $mk->nama_mk }} ({{ $mk->sks }} SKS)
                </td>
            </tr>
            @endforeach 
            --}}
            @empty
            <tr>
                <td colspan="7" align="center">Data Dosen tidak ditemukan.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <table class="ttd">
        <tr>
            <td width="33%">
                Mengetahui,<br>
                Ketua STT GPI Papua
                <br><br><br><br>
                <b>( ........................................... )</b>
            </td>
            <td width="33%"></td>
            <td width="33%">
                Fakfak, {{ date('d F Y') }} <br>
                Ketua Penjaminan Mutu,
                <br><br><br><br>
                <b>( ........................................... )</b>
            </td>
        </tr>
    </table>
</body>
</html>