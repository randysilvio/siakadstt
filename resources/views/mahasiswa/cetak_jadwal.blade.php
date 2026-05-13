<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Jadwal Kuliah - {{ $mahasiswa->nim }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; background-color: #fff; color: #000; }
        
        /* Kop Surat Styling */
        .kop-surat { width: 100%; border-bottom: 3px double #000; padding-bottom: 10px; margin-bottom: 20px; }
        .kop-logo { width: 80px; height: auto; }
        .kop-text { text-align: center; }
        .kop-text h2 { margin: 0; font-size: 18px; text-transform: uppercase; font-weight: bold; }
        .kop-text h3 { margin: 2px 0; font-size: 14px; font-weight: bold; }
        .kop-text p { margin: 0; font-size: 11px; font-style: italic; }

        .header { text-align: center; margin-bottom: 20px; }
        .header h3 { margin: 0 0 5px 0; font-size: 16px; text-transform: uppercase; font-weight: bold; text-decoration: underline; }
        .header p { margin: 0; font-size: 12px; text-transform: uppercase; font-weight: bold; }
        
        .info-table { width: 100%; margin-bottom: 15px; border: none; }
        .info-table td { padding: 4px; vertical-align: top; text-transform: uppercase; font-size: 11px; }
        
        .data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .data-table th, .data-table td { border: 1px solid #000; padding: 6px; font-size: 11px; }
        .data-table th { background-color: #212529; color: #ffffff; text-align: center; font-weight: bold; text-transform: uppercase; }
        
        .text-center { text-align: center; }
        .text-start { text-align: left; }
        .font-monospace { font-family: 'Courier New', Courier, monospace; font-weight: bold; }
        .uppercase { text-transform: uppercase; }
        
        .footer { margin-top: 40px; width: 100%; }
        .footer td { text-align: center; vertical-align: top; text-transform: uppercase; font-size: 11px; }
    </style>
</head>
<body>

    {{-- Header Kop Surat Manual --}}
    <table class="kop-surat">
        <tr>
            <td style="width: 15%; text-align: center;">
                <img src="{{ public_path('images/logo.png') }}" class="kop-logo" alt="Logo">
            </td>
            <td style="width: 85%;" class="kop-text">
                <h2>SEKOLAH TINGGI TEOLOGI</h2>
                <h2>GEREJA PROTESTAN INDONESIA PAPUA</h2>
                <h3>(STT GPI PAPUA) FAKFAK</h3>
                <p>Jalan Ahmad Yani, Kabupaten Fakfak, Papua Barat</p>
                <p>Website: sttgpipapua.ac.id | Email: info@sttgpipapua.ac.id</p>
            </td>
        </tr>
    </table>

    <div class="header">
        <h3>JADWAL KULIAH</h3>
        <p>Tahun Akademik: <span class="font-monospace">{{ $tahunAkademik->tahun }}</span> - Semester <span class="font-monospace">{{ $tahunAkademik->semester }}</span></p>
    </div>

    <table class="info-table">
        <tr>
            <td width="15%"><strong>Nama</strong></td>
            <td width="35%">: <strong>{{ $mahasiswa->nama_lengkap }}</strong></td>
            <td width="15%"><strong>Program Studi</strong></td>
            <td width="35%">: <strong>{{ $mahasiswa->programStudi->nama_prodi }}</strong></td>
        </tr>
        <tr>
            <td><strong>NIM</strong></td>
            <td class="font-monospace">: {{ $mahasiswa->nim }}</td>
            <td><strong>Tanggal Cetak</strong></td>
            <td class="font-monospace">: {{ now()->format('d-m-Y') }}</td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th width="5%">NO</th>
                <th width="12%">HARI</th>
                <th width="15%">WAKTU</th>
                <th class="text-start">MATA KULIAH</th>
                <th width="8%">SKS</th>
                <th width="15%">KODE MK</th>
                <th class="text-start">DOSEN PENGAMPU</th>
            </tr>
        </thead>
        <tbody>
            @forelse($jadwals as $jadwal)
                <tr>
                    <td class="text-center font-monospace">{{ $loop->iteration }}</td>
                    <td class="text-center uppercase fw-bold">{{ $jadwal->hari }}</td>
                    <td class="text-center font-monospace">
                        {{ \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i') }} - 
                        {{ \Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i') }}
                    </td>
                    <td class="uppercase fw-bold">{{ $jadwal->mataKuliah->nama_mk }}</td>
                    <td class="text-center font-monospace">{{ $jadwal->mataKuliah->sks }}</td>
                    <td class="text-center font-monospace">{{ $jadwal->mataKuliah->kode_mk }}</td>
                    <td class="uppercase">{{ $jadwal->mataKuliah->dosen->nama_lengkap ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center uppercase fw-bold">Belum ada jadwal kuliah yang diambil.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <table width="100%">
            <tr>
                <td width="50%"></td>
                <td width="50%">
                    <p class="mb-1">Fakfak, <span class="font-monospace">{{ now()->isoFormat('D MMMM Y') }}</span></p>
                    <p class="mb-5">Mahasiswa,</p>
                    <br><br>
                    <p style="text-decoration: underline; font-weight: bold; margin-bottom: 0;">{{ $mahasiswa->nama_lengkap }}</p>
                    <p class="font-monospace" style="margin-top: 2px;">NIM. {{ $mahasiswa->nim }}</p>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>