<!DOCTYPE html>
<html>
<head>
    <title>Laporan Data Mahasiswa (Student Body)</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid black; padding-bottom: 10px; }
        .header h2, .header h3, .header p { margin: 2px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: center; }
        th { background-color: #f2f2f2; font-weight: bold; vertical-align: middle; }
        .text-left { text-align: left; }
        .ttd { margin-top: 40px; width: 100%; }
        .ttd td { border: none; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h2>SEKOLAH TINGGI TEOLOGI (STT) GPI PAPUA</h2>
        <h3>TABEL SELEKSI & JUMLAH MAHASISWA AKTIF</h3>
        <p>{{ $judulLingkup }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th rowspan="2" width="5%">No</th>
                <th rowspan="2" width="15%">Tahun Akademik</th>
                <th rowspan="2" width="15%">Daya Tampung</th>
                <th colspan="2">Jumlah Calon Mahasiswa</th>
                <th colspan="2">Jumlah Mahasiswa Baru</th>
                <th colspan="2">Jumlah Mahasiswa Aktif</th>
            </tr>
            <tr>
                <th>Pendaftar</th>
                <th>Lulus Seleksi</th>
                <th>Reguler</th>
                <th>Transfer</th>
                <th>Reguler</th>
                <th>Transfer</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach($years as $year)
            @php $data = $laporan[$year]; @endphp
            <tr>
                <td>{{ $no++ }}</td>
                <td>{{ $year }}/{{ $year + 1 }} 
                    @if($year == end($years)) (TS) 
                    @elseif($year == $years[count($years)-2]) (TS-1) 
                    @else (TS-2) @endif
                </td>
                <td>{{ $data['daya_tampung'] }}</td>
                <td>{{ $data['calon_pendaftar'] }}</td>
                <td>{{ $data['lulus_seleksi'] }}</td>
                <td>{{ $data['mahasiswa_baru'] }}</td>
                <td>0</td> {{-- Asumsi Transfer 0 dulu --}}
                <td>{{ $data['mahasiswa_aktif'] }}</td>
                <td>0</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 20px; font-size: 10px; color: #555;">
        * TS = Tahun Sekarang (Tahun Akademik Penuh terakhir)<br>
        * Data diambil dari database SIAKAD per tanggal {{ date('d-m-Y H:i') }}
    </div>

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
                Ketua Penjaminan Mutu (SPMI),
                <br><br><br><br>
                <b>( ........................................... )</b>
            </td>
        </tr>
    </table>
</body>
</html>