<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap Absensi - {{ $verum_kela->nama_kelas }}</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 11px; margin: 20px; }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .fw-bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }
        
        .header-table { width: 100%; border-bottom: 3px double #000; margin-bottom: 20px; padding-bottom: 10px; }
        .header-table td { border: none; vertical-align: middle; }
        .logo { width: 70px; }
        
        .info-table { width: 100%; margin-bottom: 15px; }
        .info-table td { padding: 3px 0; border: none; }
        
        table.data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.data-table th, table.data-table td { border: 1px solid #000; padding: 5px; text-align: center; }
        table.data-table th { background-color: #f0f0f0; }
        table.data-table td.nama-mhs { text-align: left; white-space: nowrap; }
        
        .ttd { margin-top: 40px; width: 100%; border: none; }
        .ttd td { border: none; text-align: center; }
        
        /* Hilangkan warna background saat di-print */
        @media print {
            body { -webkit-print-color-adjust: exact; }
            table.data-table th { background-color: #f0f0f0 !important; }
        }
    </style>
</head>
<body onload="window.print()">

    {{-- KOP SURAT --}}
    <table class="header-table">
        <tr>
            <td width="10%" class="text-center">
                <img src="{{ asset('images/logo.png') }}" class="logo" alt="Logo STT">
            </td>
            <td width="90%" class="text-center">
                <h3 style="margin:0">SEKOLAH TINGGI TEOLOGI (STT) GPI PAPUA</h3>
                <h4 style="margin:5px 0">REKAPITULASI KEHADIRAN KELAS E-LEARNING (VERUM)</h4>
            </td>
        </tr>
    </table>

    {{-- INFO KELAS --}}
    <table class="info-table uppercase fw-bold">
        <tr>
            <td width="15%">MATA KULIAH</td>
            <td width="2%">:</td>
            <td width="48%">{{ $verum_kela->mataKuliah->nama_mk }} ({{ $verum_kela->mataKuliah->kode_mk }})</td>
            <td width="15%">SEMESTER</td>
            <td width="2%">:</td>
            <td width="18%">{{ $verum_kela->mataKuliah->semester }}</td>
        </tr>
        <tr>
            <td>NAMA KELAS</td>
            <td>:</td>
            <td>{{ $verum_kela->nama_kelas }}</td>
            <td>TAHUN AKADEMIK</td>
            <td>:</td>
            <td>{{ optional($verum_kela->tahunAkademik)->nama_tahun ?? '-' }}</td>
        </tr>
        <tr>
            <td>DOSEN PENGAMPU</td>
            <td>:</td>
            <td>{{ optional($verum_kela->dosen)->nama_lengkap ?? '-' }}</td>
            <td>TOTAL MHS</td>
            <td>:</td>
            <td>{{ $mahasiswas->count() }} ORANG</td>
        </tr>
    </table>

    {{-- TABEL MATRIX ABSENSI --}}
    <table class="data-table uppercase">
        <thead>
            <tr>
                <th width="3%" rowspan="2">NO</th>
                <th width="10%" rowspan="2">NIM</th>
                <th width="25%" rowspan="2" class="text-left">NAMA MAHASISWA</th>
                <th colspan="{{ $presensis->count() > 0 ? $presensis->count() : 1 }}">PERTEMUAN KE-</th>
                <th width="5%" rowspan="2">TOTAL<br>HADIR</th>
                <th width="5%" rowspan="2">PERSEN<br>(%)</th>
            </tr>
            <tr>
                @forelse($presensis as $sesi)
                    <th width="3%">{{ $sesi->pertemuan_ke }}</th>
                @empty
                    <th>-</th>
                @endforelse
            </tr>
        </thead>
        <tbody>
            @forelse($mahasiswas as $index => $mhs)
                @php 
                    $totalHadir = 0; 
                    $totalPertemuan = $presensis->count();
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $mhs->nim }}</td>
                    <td class="nama-mhs">{{ $mhs->nama_lengkap }}</td>
                    
                    @forelse($presensis as $sesi)
                        @php
                            // Cek apakah mahasiswa ini ada di relasi kehadiran pada sesi ini
                            $hadir = $sesi->kehadiran->where('mahasiswa_id', $mhs->id)->isNotEmpty();
                            if($hadir) $totalHadir++;
                        @endphp
                        <td>{{ $hadir ? 'H' : '-' }}</td>
                    @empty
                        <td>-</td>
                    @endforelse
                    
                    <td class="fw-bold">{{ $totalHadir }}</td>
                    <td class="fw-bold">
                        {{ $totalPertemuan > 0 ? round(($totalHadir / $totalPertemuan) * 100) . '%' : '0%' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ 5 + $presensis->count() }}">Belum ada mahasiswa yang mengambil mata kuliah ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 15px; font-size: 10px;">
        <strong>Keterangan:</strong><br>
        H : Hadir / Terverifikasi oleh Sistem Verum<br>
        - : Tidak Hadir / Alpa
    </div>

    {{-- TANDA TANGAN --}}
    <table class="ttd">
        <tr>
            <td width="60%"></td>
            <td width="40%">
                Fakfak, {{ now()->format('d F Y') }} <br>
                Dosen Pengampu Mata Kuliah,
                <br><br><br><br>
                <b class="uppercase"><u>{{ optional($verum_kela->dosen)->nama_lengkap ?? '.....................................' }}</u></b>
                <br>
                NIDN. {{ optional($verum_kela->dosen)->nidn ?? '-' }}
            </td>
        </tr>
    </table>

</body>
</html>