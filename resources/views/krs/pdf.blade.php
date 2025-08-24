<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>KRS - {{ $mahasiswa->nama_lengkap }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .student-info { margin-top: 20px; margin-bottom: 20px; }
        .student-info p { margin: 3px 0; }
        table.main-table { width: 100%; border-collapse: collapse; }
        .main-table th, .main-table td { border: 1px solid #000; padding: 6px; text-align: left; }
        .main-table th { background-color: #f2f2f2; }
        
        .signature-table { width: 100%; margin-top: 40px; border: none; }
        .signature-table td { border: none; text-align: center; padding: 10px; }
        .signature-space { height: 60px; }
        .document-title { text-align: center; margin-top: 20px; font-weight: bold; text-transform: uppercase; }
    </style>
</head>
<body>
    {{-- PERBAIKAN: Menyesuaikan path pemanggilan file --}}
    @include('partials._kop')

    <h3 class="document-title">Kartu Rencana Studi</h3>
    
    <div class="student-info">
        <p><strong>NIM:</strong> {{ $mahasiswa->nim }}</p>
        <p><strong>Nama Lengkap:</strong> {{ $mahasiswa->nama_lengkap }}</p>
        <p><strong>Program Studi:</strong> {{ $mahasiswa->programStudi->nama_prodi }}</p>
        <p><strong>Tahun Akademik:</strong> {{ $tahunAkademik->tahun }} - {{ $tahunAkademik->semester }}</p>
    </div>

    <table class="main-table">
        <thead>
            <tr>
                <th>Kode MK</th>
                <th>Nama Mata Kuliah</th>
                <th>SKS</th>
            </tr>
        </thead>
        <tbody>
            @php $totalSks = 0; @endphp
            @forelse ($krs as $mk)
                <tr>
                    <td>{{ $mk->kode_mk }}</td>
                    <td>{{ $mk->nama_mk }}</td>
                    <td>{{ $mk->sks }}</td>
                </tr>
                @php $totalSks += $mk->sks; @endphp
            @empty
                <tr><td colspan="3" style="text-align: center;">Belum ada mata kuliah yang diambil.</td></tr>
            @endforelse
            <tr>
                <td colspan="2" style="text-align: right; font-weight: bold;">Total SKS</td>
                <td style="font-weight: bold;">{{ $totalSks }}</td>
            </tr>
        </tbody>
    </table>

    <table class="signature-table">
        <tr>
            <td style="width: 50%;">
                <p>Mahasiswa,</p>
                <div class="signature-space"></div>
                <p><strong>( {{ $mahasiswa->nama_lengkap }} )</strong></p>
            </td>
            <td style="width: 50%;">
                <p>Dosen Wali,</p>
                <div class="signature-space"></div>
                <p><strong>( {{ $mahasiswa->dosenWali->nama_lengkap ?? '..............................' }} )</strong></p>
            </td>
        </tr>
        <tr>
            <td colspan="2" style="padding-top: 30px;">
                <p>Mengetahui,</p>
                <p>Ketua Program Studi</p>
                <div class="signature-space"></div>
                <p><strong>( {{ $mahasiswa->programStudi->kaprodi->nama_lengkap ?? '..............................' }} )</strong></p>
            </td>
        </tr>
    </table>
</body>
</html>
