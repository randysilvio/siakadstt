<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>KHS - {{ $mahasiswa->nama_lengkap }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11px; }
        
        /* Layout Utama */
        .student-info { margin-bottom: 15px; }
        .student-info table { width: 100%; }
        .student-info td { padding: 1px 5px; vertical-align: top; }
        .student-info .label { font-weight: bold; width: 120px; }
        
        /* Tabel Mata Kuliah */
        table.course-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .course-table th, .course-table td { border: 1px solid #333; padding: 6px; text-align: left; }
        .course-table th { background-color: #e9e9e9; font-weight: bold; }
        .course-table td.center { text-align: center; }
        
        /* Header Semester */
        .semester-header { background-color: #f2f2f2; padding: 8px; font-weight: bold; margin-top: 15px; border: 1px solid #333; border-bottom: none; }
        
        /* Tabel Ringkasan (IPK) */
        .summary-table { margin-top: 20px; width: 50%; border-collapse: collapse; }
        .summary-table td { border: 1px solid #333; padding: 6px; }
        .summary-table .label { font-weight: bold; }
        
        /* Judul Dokumen */
        .document-title { text-align: center; margin-bottom: 15px; font-weight: bold; text-transform: uppercase; font-size: 14px; }
        
        /* Setup Halaman PDF */
        @page { margin: 100px 50px 50px 50px; }
        header { position: fixed; top: -80px; left: 0px; right: 0px; height: 70px; }

        /* [BARU] Styling untuk Catatan Kaki Rumus */
        .calculation-note {
            margin-top: 40px;
            padding-top: 10px;
            border-top: 1px dashed #aaa;
            font-size: 9px;
            color: #444;
        }
        .calculation-note h5 {
            margin: 0 0 5px 0;
            font-size: 10px;
            text-transform: uppercase;
        }
        .calculation-note ul {
            padding-left: 15px;
            margin: 0;
        }
        .calculation-note li {
            margin-bottom: 3px;
        }
    </style>
</head>
<body>
    <header>
        @include('partials._kop')
    </header>

    <main>
        <h3 class="document-title">KARTU HASIL STUDI (KHS)</h3>

        <div class="student-info">
            <table>
                <tr>
                    <td class="label">NIM</td>
                    <td>: {{ $mahasiswa->nim }}</td>
                    <td class="label">Dosen Wali</td>
                    <td>: {{ optional(optional($mahasiswa->dosenWali)->user)->name ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Nama Lengkap</td>
                    <td>: {{ $mahasiswa->nama_lengkap }}</td>
                    <td class="label">Tahun Akademik</td>
                    <td>: {{ $tahunAkademiks->first() ? $tahunAkademiks->first()->tahun : '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Program Studi</td>
                    <td>: {{ optional($mahasiswa->programStudi)->nama_prodi }}</td>
                    <td></td><td></td>
                </tr>
            </table>
        </div>

        @forelse ($krsPerTahunAkademik as $tahunAkademikId => $krs)
            @php
                $tahun = $tahunAkademiks->find($tahunAkademikId);
                $ipsData = $mahasiswa->hitungIps($tahunAkademikId);
            @endphp
            <div class="semester-header">
                Semester {{ $tahun ? $tahun->semester : '' }} - T.A. {{ $tahun ? $tahun->tahun : 'Tidak Diketahui' }}
            </div>
            <table class="course-table">
                <thead>
                    <tr>
                        <th style="width: 15%;">Kode MK</th>
                        <th>Nama Mata Kuliah</th>
                        <th style="width: 10%;" class="center">SKS</th>
                        <th style="width: 10%;" class="center">Nilai</th>
                        <th style="width: 10%;" class="center">Bobot</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($krs as $mk)
                        <tr>
                            <td>{{ $mk->kode_mk }}</td>
                            <td>{{ $mk->nama_mk }}</td>
                            <td class="center">{{ $mk->sks }}</td>
                            <td class="center">{{ $mk->pivot->nilai }}</td>
                            <td class="center">{{ number_format($ipsData['nilaiBobot'][$mk->id] ?? 0, 2) }}</td>
                        </tr>
                    @endforeach
                    <tr style="font-weight: bold; background-color: #f9f9f9;">
                        <td colspan="2" style="text-align: right;">Total Semester Ini</td>
                        <td class="center">{{ $ipsData['total_sks'] }}</td>
                        <td style="text-align: right;">IPS</td>
                        <td class="center">{{ number_format($ipsData['ips'], 2) }}</td>
                    </tr>
                </tbody>
            </table>
        @empty
            <p style="text-align: center; padding: 20px;">Belum ada nilai yang diinput untuk semester manapun.</p>
        @endforelse

        <table class="summary-table">
            <tr>
                <td class="label">Total SKS Lulus</td>
                <td>{{ $mahasiswa->totalSksLulus() }}</td>
            </tr>
            <tr>
                <td class="label">Indeks Prestasi Kumulatif (IPK)</td>
                <td>{{ number_format($mahasiswa->hitungIpk(), 2) }}</td>
            </tr>
        </table>

        {{-- [BARU] Bagian Catatan Kaki Rumus --}}
        <div class="calculation-note">
            <h5>Keterangan Perhitungan:</h5>
            <ul>
                <li>
                    <strong>Bobot Nilai:</strong> 
                    A = 4.00, B = 3.00, C = 2.00, D = 1.00, E = 0.00
                </li>
                <li>
                    <strong>IPS (Indeks Prestasi Semester)</strong> = 
                    (Jumlah SKS Mata Kuliah x Bobot Nilai) / Total SKS yang diambil pada semester tersebut.
                </li>
                <li>
                    <strong>IPK (Indeks Prestasi Kumulatif)</strong> = 
                    (Total Seluruh (SKS x Bobot Nilai)) / Total Seluruh SKS yang telah ditempuh.
                </li>
            </ul>
        </div>

    </main>
</body>
</html>