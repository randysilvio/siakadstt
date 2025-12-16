<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Jadwal Kuliah - {{ $mahasiswa->nim }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 0; font-size: 16px; text-transform: uppercase; }
        .header p { margin: 2px 0; }
        
        .info-table { width: 100%; margin-bottom: 15px; }
        .info-table td { padding: 3px; vertical-align: top; }
        
        .data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .data-table th, .data-table td { border: 1px solid #000; padding: 6px; }
        .data-table th { background-color: #f0f0f0; text-align: center; font-weight: bold; }
        .text-center { text-align: center; }
        
        .footer { margin-top: 40px; width: 100%; }
        .footer td { text-align: center; vertical-align: top; }
    </style>
</head>
<body>

    @include('profile.partials._kop')

    <div class="header">
        <h3 style="text-decoration: underline; margin-bottom: 5px;">JADWAL KULIAH</h3>
        <p>Tahun Akademik: {{ $tahunAkademik->tahun }} - Semester {{ $tahunAkademik->semester }}</p>
    </div>

    <table class="info-table">
        <tr>
            <td width="15%"><strong>Nama</strong></td>
            <td width="35%">: {{ $mahasiswa->nama_lengkap }}</td>
            <td width="15%"><strong>Program Studi</strong></td>
            <td width="35%">: {{ $mahasiswa->programStudi->nama_prodi }}</td>
        </tr>
        <tr>
            <td><strong>NIM</strong></td>
            <td>: {{ $mahasiswa->nim }}</td>
            <td><strong>Tanggal Cetak</strong></td>
            <td>: {{ now()->format('d-m-Y') }}</td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th width="30">No</th>
                <th width="60">Hari</th>
                <th width="80">Waktu</th>
                <th>Mata Kuliah</th>
                <th width="30">SKS</th>
                <th width="60">Kode MK</th>
                <th>Dosen Pengampu</th>
            </tr>
        </thead>
        <tbody>
            @forelse($jadwals as $jadwal)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td class="text-center">{{ $jadwal->hari }}</td>
                    <td class="text-center">
                        {{ \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i') }} - 
                        {{ \Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i') }}
                    </td>
                    <td>{{ $jadwal->mataKuliah->nama_mk }}</td>
                    <td class="text-center">{{ $jadwal->mataKuliah->sks }}</td>
                    <td class="text-center">{{ $jadwal->mataKuliah->kode_mk }}</td>
                    <td>{{ $jadwal->mataKuliah->dosen->nama_lengkap ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">Belum ada jadwal kuliah yang diambil.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <table width="100%">
            <tr>
                <td width="50%"></td>
                <td width="50%">
                    <p>Fakfak, {{ now()->isoFormat('D MMMM Y') }}</p>
                    <p>Mahasiswa,</p>
                    <br><br><br>
                    <p><strong>{{ $mahasiswa->nama_lengkap }}</strong></p>
                    <p>NIM. {{ $mahasiswa->nim }}</p>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>