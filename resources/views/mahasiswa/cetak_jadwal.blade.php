<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Jadwal Kuliah - {{ $mahasiswa->nim }}</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 10pt; background-color: #fff; color: #000; line-height: 1.35; }
        @page { size: A4 portrait; margin: 1.5cm; }

        .header { text-align: center; margin-bottom: 18px; }
        .header h3 { margin: 0 0 4px 0; font-size: 13pt; text-transform: uppercase; font-weight: bold; text-decoration: underline; }
        .header p { margin: 0; font-size: 10pt; text-transform: uppercase; font-weight: bold; }
        
        .info-table { width: 100%; margin-bottom: 15px; border: none; border-collapse: collapse; }
        .info-table td { padding: 3px 0; vertical-align: top; text-transform: uppercase; font-size: 10pt; border: none; }
        
        .data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .data-table th, .data-table td { border: 1px solid #000; padding: 6px 8px; font-size: 9.5pt; }
        .data-table th { background-color: #212529; color: #ffffff; text-align: center; font-weight: bold; text-transform: uppercase; font-size: 9pt; }
        
        .text-center { text-align: center; }
        .text-start { text-align: left; }
        .font-monospace { font-family: 'Courier New', Courier, monospace; font-weight: bold; }
        .uppercase { text-transform: uppercase; }
        
        .footer { margin-top: 35px; width: 100%; page-break-inside: avoid; }
        .footer td { text-align: center; vertical-align: top; text-transform: uppercase; font-size: 10pt; border: none; }
    </style>
</head>
<body>

    @include('partials._kop')

    <div class="header">
        <h3>JADWAL PERKULIAHAN MAHASISWA</h3>
        <p>TAHUN AKADEMIK: <span class="font-monospace">{{ $tahunAkademik->tahun }}</span> - SEMESTER <span class="font-monospace">{{ strtoupper($tahunAkademik->semester) }}</span></p>
    </div>

    <table class="info-table">
        <tr>
            <td width="18%"><strong>NAMA MAHASISWA</strong></td>
            <td width="37%">: <strong>{{ $mahasiswa->nama_lengkap }}</strong></td>
            <td width="18%"><strong>PROGRAM STUDI</strong></td>
            <td width="27%">: <strong>{{ optional($mahasiswa->programStudi)->nama_prodi ?? '-' }}</strong></td>
        </tr>
        <tr>
            <td><strong>NIM</strong></td>
            <td class="font-monospace">: {{ $mahasiswa->nim }}</td>
            <td><strong>TANGGAL CETAK</strong></td>
            <td class="font-monospace">: {{ now()->format('d/m/Y') }}</td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th width="6%">NO</th>
                <th width="12%">HARI</th>
                <th width="16%">WAKTU (WIT)</th>
                <th width="13%">KODE MK</th>
                <th class="text-start">MATA KULIAH</th>
                <th width="7%">SKS</th>
                <th width="10%">RUANG</th>
                <th class="text-start" width="20%">DOSEN PENGAMPU</th>
            </tr>
        </thead>
        <tbody>
            @forelse($jadwals as $jadwal)
                <tr>
                    <td class="text-center font-monospace">{{ $loop->iteration }}</td>
                    <td class="text-center uppercase" style="font-weight: bold;">{{ $jadwal->hari }}</td>
                    <td class="text-center font-monospace">
                        {{ \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i') }} - 
                        {{ \Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i') }}
                    </td>
                    <td class="text-center font-monospace">{{ $jadwal->mataKuliah->kode_mk }}</td>
                    <td class="uppercase" style="font-weight: bold;">{{ $jadwal->mataKuliah->nama_mk }}</td>
                    <td class="text-center font-monospace">{{ $jadwal->mataKuliah->sks }}</td>
                    <td class="text-center font-monospace">{{ $jadwal->ruang ?? 'TBA' }}</td>
                    <td class="uppercase">{{ $jadwal->mataKuliah->dosen->nama_lengkap ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center uppercase" style="padding: 15px; font-weight: bold;">Belum ada jadwal kuliah yang diambil.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <table width="100%">
            <tr>
                <td width="55%"></td>
                <td width="45%">
                    <p style="margin-bottom: 4px;">Fakfak, {{ now()->translatedFormat('d F Y') }}</p>
                    <p style="margin-bottom: 60px;">Mahasiswa,</p>
                    <p style="text-decoration: underline; font-weight: bold; margin-bottom: 0;">{{ $mahasiswa->nama_lengkap }}</p>
                    <p class="font-monospace" style="margin-top: 2px; font-size: 9pt;">NIM. {{ $mahasiswa->nim }}</p>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>