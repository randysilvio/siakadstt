<!DOCTYPE html>
<html>
<head>
    <title>Ringkasan Kinerja Penjaminan Mutu</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; line-height: 1.4; }
        .header-table { width: 100%; border-bottom: 3px double #000; margin-bottom: 20px; padding-bottom: 10px; }
        .header-table td { border: none; }
        .logo { width: 80px; height: auto; }
        .title { font-size: 16px; font-weight: bold; margin: 0; }
        .subtitle { font-size: 14px; margin: 0; }
        
        .section-title { font-size: 14px; font-weight: bold; background-color: #eee; padding: 5px; margin-top: 20px; border-left: 5px solid #4e73df; }
        
        .kpi-table { width: 100%; margin-top: 10px; border-collapse: collapse; }
        .kpi-table th, .kpi-table td { border: 1px solid #ddd; padding: 8px; text-align: center; }
        .kpi-table th { background-color: #f8f9fa; }
        
        .edom-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .edom-table th, .edom-table td { border: 1px solid #333; padding: 5px; }
        .edom-table th { background-color: #e2e3e5; }
        
        .ttd { margin-top: 50px; width: 100%; text-align: center; }
    </style>
</head>
<body>
    {{-- KOP SURAT DENGAN LOGO --}}
    <table class="header-table">
        <tr>
            <td width="15%" align="center">
                <img src="{{ public_path('images/logo.png') }}" class="logo">
            </td>
            <td width="85%" align="center">
                <h2 class="title">SEKOLAH TINGGI TEOLOGI (STT) GPI PAPUA</h2>
                <h3 class="subtitle">SISTEM PENJAMINAN MUTU INTERNAL (SPMI)</h3>
                <p style="margin:2px; font-size:11px;">Jl. Jenderal Sudirman, Fakfak, Papua Barat | Website: sttgpipapua.ac.id</p>
                <p style="margin:2px; font-weight:bold;">LAPORAN RINGKASAN KINERJA AKADEMIK</p>
            </td>
        </tr>
    </table>

    <div style="text-align: right; font-size: 11px;">
        Tanggal Cetak: {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}
    </div>

    {{-- 1. INDIKATOR UTAMA (RASIO & JUMLAH) --}}
    <div class="section-title">A. Indikator Kinerja Utama</div>
    <table class="kpi-table">
        <thead>
            <tr>
                <th>Total Mahasiswa Aktif</th>
                <th>Total Dosen Tetap</th>
                <th>Rasio Dosen : Mahasiswa</th>
                <th>Rata-rata IPK Institusi</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ number_format($jumlahMahasiswaAktif) }}</td>
                <td>{{ number_format($jumlahDosen) }}</td>
                <td><strong>1 : {{ $rasio }}</strong></td>
                <td>{{ $rataIPK }}</td>
            </tr>
        </tbody>
    </table>

    {{-- 2. TREN PERTUMBUHAN --}}
    <div class="section-title">B. Tren Pertumbuhan Mahasiswa (5 Tahun Terakhir)</div>
    <table class="kpi-table" style="width: 60%;">
        <thead>
            <tr>
                <th>Tahun Angkatan</th>
                <th>Jumlah Mahasiswa</th>
            </tr>
        </thead>
        <tbody>
            @foreach($trenData as $data)
            <tr>
                <td>{{ $data->tahun_masuk }}</td>
                <td>{{ $data->total }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- 3. HASIL EVALUASI DOSEN (EDOM) --}}
    <div class="section-title">C. Hasil Evaluasi Kinerja Dosen (EDOM)</div>
    <p style="font-size: 11px;">
        Periode Evaluasi: <strong>{{ $sesiEdomAktif ? $sesiEdomAktif->nama_sesi : 'Tidak Ada Sesi Aktif' }}</strong><br>
        Berikut adalah peringkat 10 besar dosen berdasarkan kepuasan mahasiswa:
    </p>
    <table class="edom-table">
        <thead>
            <tr>
                <th width="5%">Rank</th>
                <th>Nama Dosen</th>
                <th width="15%">Skor Rata-rata</th>
                <th width="20%">Predikat</th>
            </tr>
        </thead>
        <tbody>
            @forelse($hasilEdom as $index => $hasil)
            @php
                $skor = $hasil->rata_rata_skor;
                $predikat = $skor >= 3.5 ? 'Sangat Baik' : ($skor >= 3.0 ? 'Baik' : 'Cukup');
            @endphp
            <tr>
                <td align="center">{{ $index + 1 }}</td>
                <td>{{ $hasil->nama_lengkap }}</td>
                <td align="center"><strong>{{ number_format($skor, 2) }}</strong></td>
                <td align="center">{{ $predikat }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="4" align="center">Belum ada data evaluasi untuk periode ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- TANDA TANGAN --}}
    <table class="ttd">
        <tr>
            <td width="50%">
                Mengetahui,<br>
                Ketua STT GPI Papua
                <br><br><br><br>
                <b>( ........................................... )</b>
            </td>
            <td width="50%">
                Disahkan Oleh,<br>
                Ketua Penjaminan Mutu (SPMI)
                <br><br><br><br>
                <b>( ........................................... )</b>
            </td>
        </tr>
    </table>
</body>
</html>