<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Jadwal Mengajar - {{ $dosen->nama_lengkap }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12px; }
        
        /* Layout Header */
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .header h3 { margin: 0; text-transform: uppercase; text-decoration: underline; }
        
        /* Meta Info Dosen */
        .meta-info { margin-bottom: 15px; }
        .meta-info table { width: 100%; border: none; }
        .meta-info td { padding: 2px; vertical-align: top; border: none; }
        .label { font-weight: bold; width: 130px; }

        /* Tabel Jadwal */
        table.schedule { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.schedule th, table.schedule td { border: 1px solid #000; padding: 6px 8px; text-align: left; vertical-align: middle; }
        table.schedule th { background-color: #f0f0f0; font-weight: bold; text-align: center; }
        
        /* Utility */
        .text-center { text-align: center; }
        
        /* Footer */
        .footer { position: fixed; bottom: 0; width: 100%; text-align: right; font-size: 10px; color: #666; font-style: italic; }
    </style>
</head>
<body>

    {{-- Gunakan Kop Surat --}}
    @include('partials._kop')

    <div class="header" style="margin-top: 20px; border-bottom: none;">
        <h3>JADWAL MENGAJAR DOSEN</h3>
    </div>

    <div class="meta-info">
        <table>
            <tr>
                <td class="label">Nama Dosen</td>
                <td>: {{ $dosen->nama_lengkap }}</td>
                <td class="label">NIDN</td>
                <td>: {{ $dosen->nidn }}</td>
            </tr>
            <tr>
                <td class="label">Tahun Akademik</td>
                <td>: {{ $tahunAkademik->tahun ?? '-' }} / {{ $tahunAkademik->semester ?? '-' }}</td>
                <td class="label">Tanggal Cetak</td>
                <td>: {{ date('d F Y') }}</td>
            </tr>
        </table>
    </div>

    <table class="schedule">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 10%;">Hari</th>
                <th style="width: 15%;">Jam</th>
                <th style="width: 10%;">Kode MK</th>
                <th style="width: 35%;">Mata Kuliah</th>
                <th style="width: 5%;">SKS</th>
                <th style="width: 10%;">Ruang</th>
                <th style="width: 10%;">Program Studi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($jadwals as $index => $jadwal)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ $jadwal->hari }}</td>
                    <td class="text-center">
                        {{ \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i') }} - 
                        {{ \Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i') }}
                    </td>
                    <td class="text-center">{{ $jadwal->mataKuliah->kode_mk }}</td>
                    <td>{{ $jadwal->mataKuliah->nama_mk }}</td>
                    <td class="text-center">{{ $jadwal->mataKuliah->sks }}</td>
                    <td class="text-center">{{ $jadwal->ruangan ?? '-' }}</td>
                    <td class="text-center">{{ $jadwal->mataKuliah->programStudi->nama_prodi ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center" style="padding: 15px;">
                        <em>Tidak ada jadwal mengajar pada semester aktif ini.</em>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dicetak melalui Sistem Informasi Akademik STT GPI Papua
    </div>

</body>
</html>