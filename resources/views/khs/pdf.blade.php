<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>KHS - {{ $mahasiswa->nama_lengkap }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11px; }
        .student-info { margin-bottom: 15px; }
        .student-info table { width: 100%; }
        .student-info td { padding: 1px 5px; vertical-align: top; }
        .student-info .label { font-weight: bold; width: 120px; }
        table.course-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .course-table th, .course-table td { border: 1px solid #333; padding: 6px; text-align: left; }
        .course-table th { background-color: #e9e9e9; font-weight: bold; }
        .course-table td.center { text-align: center; }
        .semester-header { background-color: #f2f2f2; padding: 8px; font-weight: bold; margin-top: 15px; }
        .summary-table { margin-top: 20px; width: 50%; border-collapse: collapse; }
        .summary-table td { border: 1px solid #333; padding: 6px; }
        .summary-table .label { font-weight: bold; }
        .document-title { text-align: center; margin-bottom: 15px; font-weight: bold; text-transform: uppercase; font-size: 14px; }
        @page { margin: 100px 50px 50px 50px; }
        header { position: fixed; top: -80px; left: 0px; right: 0px; height: 70px; }
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
                    <tr style="font-weight: bold;">
                        <td colspan="2" style="text-align: right;">Total</td>
                        <td class="center">{{ $ipsData['total_sks'] }}</td>
                        <td style="text-align: right;">IPS</td>
                        <td class="center">{{ number_format($ipsData['ips'], 2) }}</td>
                    </tr>
                </tbody>
            </table>
        @empty
            <p style="text-align: center;">Belum ada nilai yang diinput untuk semester manapun.</p>
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
    </main>
</body>
</html>