<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Transkrip - {{ $mahasiswa->nama_lengkap }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .student-info { margin-top: 20px; margin-bottom: 20px; }
        .student-info p { margin: 3px 0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px;}
        th, td { border: 1px solid #000; padding: 6px; text-align: left; }
        th { background-color: #f2f2f2; }
        .summary { margin-top: 20px; font-weight: bold; }
        .document-title { text-align: center; margin-top: 20px; font-weight: bold; text-transform: uppercase; }
    </style>
</head>
<body>
    {{-- Memanggil komponen kop surat --}}
    @include('partials._kop')

    <h3 class="document-title">Transkrip Nilai Akademik</h3>

    <div class="student-info">
        <p><strong>NIM:</strong> {{ $mahasiswa->nim }}</p>
        <p><strong>Nama Lengkap:</strong> {{ $mahasiswa->nama_lengkap }}</p>
        <p><strong>Program Studi:</strong> {{ $mahasiswa->programStudi->nama_prodi }}</p>
    </div>

    @foreach ($krs_per_semester as $semester => $matkuls)
        <h4>Semester {{ $semester }}</h4>
        <table>
            <thead>
                <tr>
                    <th>Kode MK</th>
                    <th>Nama Mata Kuliah</th>
                    <th>SKS</th>
                    <th>Nilai</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($matkuls as $mk)
                    <tr>
                        <td>{{ $mk->kode_mk }}</td>
                        <td>{{ $mk->nama_mk }}</td>
                        <td>{{ $mk->sks }}</td>
                        <td>{{ $mk->pivot->nilai }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endforeach

    <div class="summary">
        <p>Total SKS Ditempuh: {{ $total_sks }}</p>
        <p>Indeks Prestasi Kumulatif (IPK): {{ $ipk }}</p>
    </div>
</body>
</html>
