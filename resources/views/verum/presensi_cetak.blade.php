<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap Absensi Verum - {{ $verum_kela->nama_kelas }}</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 10pt; margin: 20px; color: #000; }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .fw-bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }
        .font-monospace { font-family: 'Courier New', Courier, monospace; font-weight: bold; }
        
        .doc-title { text-align: center; font-weight: bold; font-size: 13pt; text-decoration: underline; text-transform: uppercase; margin: 10px 0 15px 0; }
        
        .info-table { width: 100%; margin-bottom: 15px; border-collapse: collapse; }
        .info-table td { padding: 3px 0; border: none; font-size: 10pt; }
        
        table.data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.data-table th, table.data-table td { border: 1px solid #000; padding: 5px; text-align: center; font-size: 9pt; }
        table.data-table th { background-color: #212529; color: #ffffff; }
        table.data-table td.nama-mhs { text-align: left; white-space: nowrap; }
        
        .ttd { margin-top: 35px; width: 100%; border: none; page-break-inside: avoid; }
        .ttd td { border: none; text-align: center; }

        .no-print { text-align: right; margin-bottom: 15px; border-bottom: 1px solid #ccc; padding-bottom: 10px; }
        .btn-print { background-color: #212529; color: #fff; border: none; padding: 7px 16px; font-family: Arial, sans-serif; cursor: pointer; text-transform: uppercase; font-size: 11px; font-weight: bold; }
        
        @media print {
            body { -webkit-print-color-adjust: exact; margin: 0; }
            .no-print { display: none !important; }
            @page { size: A4 landscape; margin: 1.2cm; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="no-print">
        <button onclick="window.print()" class="btn-print">Cetak Dokumen</button>
    </div>

    @include('partials._kop')

    <div class="doc-title">REKAPITULASI KEHADIRAN KELAS E-LEARNING (VERUM)</div>

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
            <td>
                @if($verum_kela->tahunAkademik)
                    {{ $verum_kela->tahunAkademik->tahun }} ({{ $verum_kela->tahunAkademik->semester }})
                @else
                    -
                @endif
            </td>
        </tr>
        <tr>
            <td>DOSEN PENGAMPU</td>
            <td>:</td>
            <td>{{ optional($verum_kela->dosen)->nama_lengkap ?? '-' }}</td>
            <td>TOTAL PESERTA</td>
            <td>:</td>
            <td>{{ $mahasiswas->count() }} MAHASISWA</td>
        </tr>
    </table>

    <table class="data-table uppercase">
        <thead>
            <tr>
                <th width="4%" rowspan="2">NO</th>
                <th width="12%" rowspan="2">NIM</th>
                <th width="26%" rowspan="2" class="text-left">NAMA MAHASISWA</th>
                <th colspan="{{ $presensis->count() > 0 ? $presensis->count() : 1 }}">PERTEMUAN KE-</th>
                <th width="7%" rowspan="2">TOTAL<br>HADIR</th>
                <th width="8%" rowspan="2">PERSEN<br>(%)</th>
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
                    <td class="font-monospace">{{ $index + 1 }}</td>
                    <td class="font-monospace">{{ $mhs->nim }}</td>
                    <td class="nama-mhs fw-bold">{{ $mhs->nama_lengkap }}</td>
                    
                    @forelse($presensis as $sesi)
                        @php
                            $hadir = $sesi->kehadiran->where('mahasiswa_id', $mhs->id)->isNotEmpty();
                            if($hadir) $totalHadir++;
                        @endphp
                        <td class="font-monospace">{{ $hadir ? 'H' : '-' }}</td>
                    @empty
                        <td>-</td>
                    @endforelse
                    
                    <td class="fw-bold font-monospace">{{ $totalHadir }}</td>
                    <td class="fw-bold font-monospace">
                        {{ $totalPertemuan > 0 ? round(($totalHadir / $totalPertemuan) * 100) . '%' : '0%' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ 5 + max(1, $presensis->count()) }}">Belum ada mahasiswa yang mengambil mata kuliah ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 12px; font-size: 9pt;">
        <strong>Keterangan:</strong> H = Hadir (Terverifikasi Sistem E-Learning Verum) | - = Tidak Hadir / Alpha
    </div>

    <table class="ttd">
        <tr>
            <td width="60%"></td>
            <td width="40%">
                Fakfak, {{ now()->translatedFormat('d F Y') }} <br>
                Dosen Pengampu Mata Kuliah,
                <br><br><br><br>
                <b class="uppercase"><u>{{ optional($verum_kela->dosen)->nama_lengkap ?? '.....................................' }}</u></b>
                <br>
                <span class="font-monospace" style="font-size: 9pt;">NIDN. {{ optional($verum_kela->dosen)->nidn ?? '-' }}</span>
            </td>
        </tr>
    </table>

</body>
</html>