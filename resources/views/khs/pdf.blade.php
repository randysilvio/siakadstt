<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>KHS - {{ $mahasiswa->nama_lengkap }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .student-info { margin-top: 20px; margin-bottom: 20px; }
        .student-info p { margin: 2px 0; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .footer { margin-top: 20px; }
        .document-title { text-align: center; margin-top: 20px; font-weight: bold; text-transform: uppercase; }
    </style>
</head>
<body>
    {{-- ======================================================= --}}
    {{-- ===== PERBAIKAN: Menyesuaikan path pemanggilan file ===== --}}
    {{-- ======================================================= --}}
    @include('partials._kop')

    <h2 class="document-title">Kartu Hasil Studi (KHS)</h2>

    <div class="student-info">
        <p><strong>NIM:</strong> {{ $mahasiswa->nim }}</p>
        <p><strong>Nama Lengkap:</strong> {{ $mahasiswa->nama_lengkap }}</p>
        <p><strong>Program Studi:</strong> {{ $mahasiswa->programStudi->nama_prodi }}</p>
    </div>

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
            @forelse ($krs as $mk)
                <tr>
                    <td>{{ $mk->kode_mk }}</td>
                    <td>{{ $mk->nama_mk }}</td>
                    <td>{{ $mk->sks }}</td>
                    <td>{{ $mk->pivot->nilai }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align: center;">Belum ada nilai yang diinput.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p><strong>Total SKS Ditempuh:</strong> {{ $total_sks }}</p>
        <p><strong>Indeks Prestasi Semester (IPS):</strong> {{ $ips }}</p>
    </div>
</body>
</html>
