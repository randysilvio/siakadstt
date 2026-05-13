<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Jadwal Mengajar - {{ $dosen->nama_lengkap }}</title>
    <style>
        body { 
            font-family: 'Times New Roman', Times, serif; 
            font-size: 11pt; 
            color: #000;
            line-height: 1.4;
        }
        .text-center { text-align: center; }
        .text-bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }
        
        /* Layout Header */
        .doc-title { 
            text-align: center; 
            margin: 20px 0; 
            font-weight: bold; 
            font-size: 14pt;
            text-decoration: underline;
        }
        
        /* Meta Info */
        .meta-table { width: 100%; margin-bottom: 20px; border: none; }
        .meta-table td { padding: 2px; vertical-align: top; }
        .label { font-weight: bold; width: 140px; }

        /* Tabel Jadwal */
        table.schedule { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 10px; 
        }
        table.schedule th, table.schedule td { 
            border: 1px solid #000; 
            padding: 6px 8px; 
        }
        table.schedule th { 
            background-color: #f2f2f2; 
            text-align: center; 
            font-weight: bold;
            font-size: 10pt;
        }
        
        .footer { 
            position: fixed; 
            bottom: 0; 
            width: 100%; 
            text-align: right; 
            font-size: 9pt; 
            font-style: italic; 
        }

        @media print {
            @page { size: A4 portrait; margin: 1.5cm; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    @include('partials._kop')

    <div class="doc-title uppercase">Jadwal Perkuliahan Dosen</div>

    <table class="meta-table">
        <tr>
            <td class="label">Nama Tenaga Pendidik</td>
            <td>: {{ $dosen->nama_lengkap }}</td>
            <td class="label">Tahun Akademik</td>
            <td>: {{ $tahunAkademik->tahun ?? '-' }} ({{ $tahunAkademik->semester ?? '-' }})</td>
        </tr>
        <tr>
            <td class="label">NIDN</td>
            <td>: {{ $dosen->nidn }}</td>
            <td class="label">Tanggal Cetak</td>
            <td>: {{ now()->translatedFormat('d F Y') }}</td>
        </tr>
    </table>

    <table class="schedule">
        <thead>
            <tr>
                <th style="width: 5%;">NO</th>
                <th style="width: 10%;">HARI</th>
                <th style="width: 15%;">WAKTU</th>
                <th style="width: 35%;">MATA KULIAH</th>
                <th style="width: 10%;">SKS</th>
                <th style="width: 10%;">RUANG</th>
                <th style="width: 15%;">PROGRAM STUDI</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($jadwals as $index => $jadwal)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center text-bold">{{ strtoupper($jadwal->hari) }}</td>
                    <td class="text-center">
                        {{ \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i') }} - 
                        {{ \Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i') }}
                    </td>
                    <td>
                        <div class="text-bold">{{ $jadwal->mataKuliah->nama_mk }}</div>
                        <small>{{ $jadwal->mataKuliah->kode_mk }}</small>
                    </td>
                    <td class="text-center">{{ $jadwal->mataKuliah->sks }}</td>
                    <td class="text-center">{{ $jadwal->ruangan ?? '-' }}</td>
                    <td class="text-center">{{ $jadwal->mataKuliah->programStudi->nama_prodi ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 20px;">Belum terdapat jadwal mengajar yang terdaftar pada semester ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dokumen ini dihasilkan secara otomatis melalui Sistem Informasi Akademik STT GPI Papua.
    </div>

</body>
</html>