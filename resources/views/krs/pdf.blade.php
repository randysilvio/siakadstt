<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>KRS - {{ $mahasiswa->nama_lengkap }}</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 10pt; line-height: 1.3; color: #000; margin: 0; padding: 0; }
        @page { size: A4 portrait; margin: 1.5cm; }
        
        /* Kop Surat Terpusat */
        .kop-container { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        .kop-container img { width: 50px; height: auto; display: block; margin: 0 auto 5px auto; }
        .kop-container h2 { margin: 0; font-size: 14pt; text-transform: uppercase; font-weight: bold; }
        .kop-container p { margin: 0; font-size: 9pt; }

        .document-title { text-align: center; font-weight: bold; font-size: 12pt; text-decoration: underline; text-transform: uppercase; margin-bottom: 20px; }
        
        /* Informasi Mahasiswa */
        .student-info { margin-bottom: 20px; }
        .student-info table { width: 100%; border-collapse: collapse; }
        .student-info td { padding: 3px 0; vertical-align: top; text-transform: uppercase; font-size: 9pt; }
        .student-info .label { font-weight: bold; width: 130px; }
        
        /* Tabel Mata Kuliah */
        table.main-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .main-table th, .main-table td { border: 1px solid #000; padding: 6px 8px; vertical-align: middle; }
        .main-table th { background-color: #f0f0f0; text-transform: uppercase; font-size: 8pt; text-align: center; font-weight: bold; }
        .main-table td { font-size: 9pt; }
        .text-center { text-align: center; }
        
        /* Kolom Tanda Tangan */
        .signature-table { width: 100%; margin-top: 30px; border-collapse: collapse; page-break-inside: avoid; }
        .signature-table td { border: none; text-align: center; padding: 5px; vertical-align: bottom; font-size: 10pt; }
        .signature-space { height: 65px; }
    </style>
</head>
<body>
    {{-- Kop Surat Rata Tengah Eksklusif --}}
    <div class="kop-container">
        <img src="{{ public_path('images/logo.png') }}" alt="Logo">
        <h2>Sekolah Tinggi Teologi (STT) GPI Papua</h2>
        <p>Jl. Ahmad Yani, Fakfak, Papua Barat. Terakreditasi BAN-PT</p>
    </div>

    <div class="document-title">KARTU RENCANA STUDI (KRS)</div>
    
    <div class="student-info">
        <table>
            <tr>
                <td class="label">NIM</td>
                <td>: <span style="font-family: monospace; font-weight: bold;">{{ $mahasiswa->nim }}</span></td>
                <td class="label">TAHUN AKADEMIK</td>
                <td>: <span style="font-family: monospace; font-weight: bold;">{{ $tahunAkademik->tahun }} - {{ strtoupper($tahunAkademik->semester) }}</span></td>
            </tr>
            <tr>
                <td class="label">NAMA LENGKAP</td>
                <td>: {{ $mahasiswa->nama_lengkap }}</td>
                <td class="label">PROGRAM STUDI</td>
                <td>: {{ $mahasiswa->programStudi->nama_prodi }}</td>
            </tr>
        </table>
    </div>

    <table class="main-table">
        <thead>
            <tr>
                <th style="width: 15%;">KODE MK</th>
                <th>NAMA MATA KULIAH</th>
                <th style="width: 12%;">SKS</th>
            </tr>
        </thead>
        <tbody>
            @php $totalSks = 0; @endphp
            @forelse ($krs as $mk)
                <tr>
                    <td class="text-center" style="font-family: monospace; font-weight: bold;">{{ $mk->kode_mk }}</td>
                    <td style="text-transform: uppercase; font-weight: bold;">{{ $mk->nama_mk }}</td>
                    <td class="text-center" style="font-family: monospace; font-weight: bold;">{{ $mk->sks }}</td>
                </tr>
                @php $totalSks += $mk->sks; @endphp
            @empty
                <tr><td colspan="3" class="text-center" style="padding: 15px; text-transform: uppercase;">Belum ada mata kuliah yang diambil.</td></tr>
            @endforelse
            <tr style="background-color: #f9f9f9;">
                <td colspan="2" style="text-align: right; font-weight: bold; padding-right: 10px;">TOTAL SKS</td>
                <td class="text-center" style="font-family: monospace; font-weight: bold; font-size: 10pt;">{{ $totalSks }}</td>
            </tr>
        </tbody>
    </table>

    <table class="signature-table">
        <tr>
            <td style="width: 50%;">
                Mahasiswa,<br>
                <div class="signature-space"></div>
                <strong style="text-transform: uppercase;">( {{ $mahasiswa->nama_lengkap }} )</strong>
            </td>
            <td style="width: 50%;">
                Dosen Wali,<br>
                <div class="signature-space"></div>
                <strong style="text-transform: uppercase;">( {{ $mahasiswa->dosenWali->nama_lengkap ?? '..............................' }} )</strong>
            </td>
        </tr>
        <tr>
            <td colspan="2" style="padding-top: 30px;">
                Mengetahui,<br>
                Ketua Program Studi<br>
                <div class="signature-space"></div>
                <strong style="text-transform: uppercase;">( {{ $mahasiswa->programStudi->kaprodi->nama_lengkap ?? '..............................' }} )</strong>
            </td>
        </tr>
    </table>
</body>
</html>