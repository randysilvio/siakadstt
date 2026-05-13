<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Transkrip Nilai Akademik - {{ $mahasiswa->nim }}</title>
    <style>
        /* CSS STANDAR ENTERPRISE (CETAKAN FORMAL 0PX) */
        body { font-family: Arial, sans-serif; font-size: 11px; background-color: #fff; color: #000; line-height: 1.3; }
        
        .document-title { text-align: center; margin-top: 15px; margin-bottom: 20px; font-size: 14px; font-weight: bold; text-transform: uppercase; text-decoration: underline; }
        
        .student-info { width: 100%; margin-bottom: 20px; border-collapse: collapse; font-size: 11px; text-transform: uppercase; }
        .student-info td { padding: 3px 8px 3px 0; vertical-align: top; border: none; }
        
        .semester-title { font-size: 11px; font-weight: bold; text-transform: uppercase; background-color: #212529; color: #ffffff; padding: 4px 8px; margin-top: 15px; margin-bottom: 0; }
        
        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        table.data-table th, table.data-table td { border: 1px solid #000; padding: 5px 8px; font-size: 11px; vertical-align: middle; }
        table.data-table th { background-color: #f8fafc; color: #000; font-weight: bold; text-align: center; text-transform: uppercase; }
        
        .summary-box { width: 100%; margin-top: 20px; margin-bottom: 30px; border: 2px solid #000; padding: 10px; background-color: #f8fafc; font-size: 11px; text-transform: uppercase; }
        
        .text-center { text-align: center; }
        .text-start { text-align: left; }
        .font-monospace { font-family: 'Courier New', Courier, monospace; font-weight: bold; }
        .uppercase { text-transform: uppercase; }
        
        .ttd { margin-top: 40px; width: 100%; border-collapse: collapse; font-size: 11px; text-transform: uppercase; }
        .ttd td { border: none; text-align: center; vertical-align: top; }
    </style>
</head>
<body onload="window.print()">

    {{-- Memanggil komponen kop surat resmi --}}
    @include('partials._kop')

    <div class="document-title">TRANSKRIP NILAI AKADEMIK SEMENTARA</div>

    <table class="student-info">
        <tr>
            <td style="width: 18%;"><strong>NAMA LENGKAP</strong></td>
            <td style="width: 45%;">: <strong>{{ $mahasiswa->nama_lengkap }}</strong></td>
            <td style="width: 15%;"><strong>PROGRAM STUDI</strong></td>
            <td style="width: 22%;">: <strong>{{ optional($mahasiswa->programStudi)->nama_prodi ?? '-' }}</strong></td>
        </tr>
        <tr>
            <td><strong>NIM</strong></td>
            <td class="font-monospace">: {{ $mahasiswa->nim }}</td>
            <td><strong>TANGGAL CETAK</strong></td>
            <td class="font-monospace">: {{ now()->format('d.m.Y') }}</td>
        </tr>
    </table>

    @forelse ($krs_per_semester as $semester => $matkuls)
        <div class="semester-title">SEMESTER {{ $semester }}</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 15%;">KODE MK</th>
                    <th class="text-start" style="width: 55%;">NAMA MATA KULIAH</th>
                    <th style="width: 15%;">SKS</th>
                    <th style="width: 15%;">NILAI</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($matkuls as $mk)
                    <tr>
                        <td class="text-center font-monospace">{{ $mk->kode_mk }}</td>
                        <td class="uppercase fw-bold">{{ $mk->nama_mk }}</td>
                        <td class="text-center font-monospace">{{ $mk->sks }}</td>
                        <td class="text-center font-monospace fs-6">{{ optional($mk->pivot)->nilai ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @empty
        <p class="text-center uppercase font-monospace my-4">BELUM ADA DATA NILAI MATA KULIAH.</p>
    @endforelse

    {{-- Kotak Rangkuman Prestasi Akhir --}}
    <div class="summary-box">
        <table style="width: 100%; border: none;">
            <tr>
                <td style="border: none; width: 50%;">
                    TOTAL BEBAN STUDI DITEMPUH: <span class="font-monospace fs-5">{{ $total_sks }}</span> SKS
                </td>
                <td style="border: none; width: 50%; text-align: right;">
                    INDEKS PRESTASI KUMULATIF (IPK): <span class="font-monospace fs-5">{{ number_format((float)$ipk, 2) }}</span>
                </td>
            </tr>
        </table>
    </div>

    {{-- Otorisasi Tanda Tangan Resmi --}}
    <table class="ttd">
        <tr>
            <td style="width: 50%;"></td>
            <td style="width: 50%;">
                <p class="mb-1">FAKFAK, <span class="font-monospace">{{ now()->isoFormat('D MMMM Y') }}</span></p>
                <p style="margin-bottom: 60px;">KETUA PROGRAM STUDI,</p>
                <p style="text-decoration: underline; font-weight: bold; margin-bottom: 2px;">
                    {{ optional(optional($mahasiswa->programStudi)->kaprodi)->nama_lengkap ?? '...........................................' }}
                </p>
                <p class="font-monospace" style="font-size: 10px;">
                    NIDN. {{ optional(optional($mahasiswa->programStudi)->kaprodi)->nidn ?? '.....................' }}
                </p>
            </td>
        </tr>
    </table>

</body>
</html>