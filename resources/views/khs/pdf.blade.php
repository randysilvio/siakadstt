<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>KHS - {{ $mahasiswa->nim }} - {{ $tahunSelected->semester }} {{ $tahunSelected->tahun }}</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 10pt; line-height: 1.3; color: #000; margin: 0; padding: 0; }
        @page { size: A4 portrait; margin: 1.5cm; }
        
        .kop-container { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        .kop-container img { width: 50px; height: auto; display: block; margin: 0 auto 5px auto; }
        .kop-container h2 { margin: 0; font-size: 14pt; text-transform: uppercase; font-weight: bold; }
        .kop-container p { margin: 0; font-size: 9pt; }

        .document-title { text-align: center; font-weight: bold; font-size: 12pt; text-decoration: underline; text-transform: uppercase; margin-bottom: 20px; }
        
        .student-info { margin-bottom: 20px; }
        .student-info table { width: 100%; border-collapse: collapse; }
        .student-info td { padding: 3px 0; vertical-align: top; text-transform: uppercase; font-size: 10pt; }
        .student-info .label { font-weight: bold; width: 140px; }
        
        table.course-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .course-table th, .course-table td { border: 1px solid #000; padding: 6px 8px; vertical-align: middle; }
        .course-table th { background-color: #f0f0f0; text-transform: uppercase; font-size: 9pt; text-align: center; font-weight: bold; }
        .course-table td { font-size: 10pt; }
        .course-table td.center { text-align: center; }
        
        .summary-box { border: 1px solid #000; padding: 12px; margin-top: 20px; background-color: #fafafa; page-break-inside: avoid; }
        .summary-table { width: 100%; border-collapse: collapse; text-transform: uppercase; font-size: 10pt; font-weight: bold; }
        .summary-table td { padding: 4px 0; }
        
        .calculation-note { margin-top: 40px; padding-top: 10px; border-top: 1px dashed #000; font-size: 8pt; line-height: 1.4; page-break-inside: avoid; }
        .calculation-note h5 { margin: 0 0 5px 0; font-size: 9pt; text-transform: uppercase; font-weight: bold; }
        .calculation-note ul { padding-left: 15px; margin: 0; }
        .calculation-note li { margin-bottom: 3px; }

        .footer-sign { width: 100%; margin-top: 40px; page-break-inside: avoid; }
        .footer-sign td { text-align: center; width: 50%; vertical-align: bottom; font-size: 10pt; }
    </style>
</head>
<body>
    <div class="kop-container">
        <img src="{{ public_path('images/logo.png') }}" alt="Logo">
        <h2>Sekolah Tinggi Teologi (STT) GPI Papua</h2>
        <p>Jl. Ahmad Yani, Fakfak, Papua Barat. Terakreditasi BAN-PT</p>
    </div>

    <main>
        <div class="document-title">KARTU HASIL STUDI (KHS)</div>

        <div class="student-info">
            <table>
                <tr>
                    <td class="label">NIM</td>
                    <td>: <span style="font-weight: bold;">{{ $mahasiswa->nim }}</span></td>
                    <td class="label">DOSEN WALI</td>
                    <td>: {{ optional(optional($mahasiswa->dosenWali)->user)->name ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">NAMA LENGKAP</td>
                    <td>: <span style="font-weight: bold;">{{ $mahasiswa->nama_lengkap }}</span></td>
                    <td class="label">TAHUN AKADEMIK</td>
                    <td>: <span style="font-weight: bold;">{{ $tahunSelected->tahun }}</span></td>
                </tr>
                <tr>
                    <td class="label">PROGRAM STUDI</td>
                    <td>: {{ optional($mahasiswa->programStudi)->nama_prodi }}</td>
                    <td class="label">SEMESTER</td>
                    <td>: <span style="font-weight: bold;">{{ strtoupper($tahunSelected->semester) }}</span></td>
                </tr>
            </table>
        </div>

        <table class="course-table">
            <thead>
                <tr>
                    <th style="width: 12%;">KODE MK</th>
                    <th>NAMA MATA KULIAH</th>
                    <th style="width: 8%;" class="center">SKS</th>
                    <th style="width: 12%;" class="center">NILAI HURUF</th>
                    <th style="width: 12%;" class="center">NILAI ANGKA</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($krsSelected as $mk)
                    <tr>
                        <td class="center" style="font-family: monospace;">{{ $mk->kode_mk }}</td>
                        <td style="text-transform: uppercase; font-weight: bold;">{{ $mk->nama_mk }}</td>
                        <td class="center" style="font-family: monospace;">{{ $mk->sks }}</td>
                        <td class="center" style="font-weight: bold; font-family: monospace;">{{ $mk->pivot->nilai ?? '-' }}</td>
                        <td class="center" style="font-family: monospace;">{{ $mk->pivot->nilai ? number_format($ipsData['nilaiBobot'][$mk->id] ?? 0, 2) : '-' }}</td>
                    </tr>
                @endforeach
                <tr style="font-weight: bold; background-color: #f9f9f9;">
                    <td colspan="2" style="text-align: right; padding-right: 15px; text-transform: uppercase;">Total SKS / IPS Semester Ini</td>
                    <td class="center" style="font-family: monospace; font-size: 11pt;">{{ $ipsData['total_sks'] }}</td>
                    <td colspan="2" class="center" style="font-family: monospace; font-size: 11pt; color: #000;">{{ number_format($ipsData['ips'], 2) }}</td>
                </tr>
            </tbody>
        </table>

        <div class="summary-box">
            <table class="summary-table">
                <tr>
                    <td>TOTAL SKS KUMULATIF LULUS</td>
                    <td style="text-align: right; font-family: monospace;">{{ $mahasiswa->totalSksLulus() }} SKS</td>
                </tr>
                <tr>
                    <td style="padding-top: 8px;">INDEKS PRESTASI KUMULATIF (IPK) SAAT INI</td>
                    <td style="text-align: right; font-family: monospace; font-size: 14pt; padding-top: 8px;">{{ number_format($mahasiswa->hitungIpk(), 2) }}</td>
                </tr>
            </table>
        </div>

        <div class="calculation-note">
            <h5>KETERANGAN PERHITUNGAN:</h5>
            <ul>
                <li><strong>Bobot Nilai:</strong> A = 4.00, B = 3.00, C = 2.00, D = 1.00, E = 0.00</li>
                <li><strong>IPS (Indeks Prestasi Semester)</strong> = (Jumlah SKS Mata Kuliah x Bobot Nilai) / Total SKS yang diambil pada semester tersebut.</li>
                <li><strong>IPK (Indeks Prestasi Kumulatif)</strong> = (Total Seluruh (SKS x Bobot Nilai)) / Total Seluruh SKS yang telah ditempuh.</li>
            </ul>
        </div>

        <table class="footer-sign">
            <tr>
                <td></td>
                <td>
                    Fakfak, {{ date('d F Y') }}<br>Ketua Program Studi,<br><br><br><br><br>
                    <strong>( ........................................ )</strong>
                </td>
            </tr>
        </table>
    </main>
</body>
</html>