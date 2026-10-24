<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>KHS - {{ $mahasiswa->nim }} - {{ $tahunSelected->semester }} {{ $tahunSelected->tahun }}</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 10pt; line-height: 1.35; color: #000; margin: 0; padding: 0; }
        @page { size: A4 portrait; margin: 1.5cm; }

        .document-title { text-align: center; font-weight: bold; font-size: 13pt; text-decoration: underline; text-transform: uppercase; margin: 10px 0 18px 0; }
        
        .student-info { margin-bottom: 15px; }
        .student-info table { width: 100%; border-collapse: collapse; }
        .student-info td { padding: 3px 0; vertical-align: top; text-transform: uppercase; font-size: 10pt; border: none; }
        .student-info .label { font-weight: bold; width: 140px; }
        
        table.course-table { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
        .course-table th, .course-table td { border: 1px solid #000; padding: 6px 8px; vertical-align: middle; }
        .course-table th { background-color: #212529; color: #ffffff; text-transform: uppercase; font-size: 9pt; text-align: center; font-weight: bold; }
        .course-table td { font-size: 9.5pt; }
        .course-table td.center { text-align: center; }
        .font-monospace { font-family: 'Courier New', Courier, monospace; font-weight: bold; }
        
        .summary-box { border: 1px solid #000; padding: 10px 14px; margin-top: 15px; background-color: #f8fafc; page-break-inside: avoid; }
        .summary-table { width: 100%; border-collapse: collapse; text-transform: uppercase; font-size: 10pt; font-weight: bold; }
        .summary-table td { padding: 3px 0; border: none; }
        
        .calculation-note { margin-top: 15px; padding-top: 8px; border-top: 1px dashed #000; font-size: 8.5pt; line-height: 1.4; page-break-inside: avoid; }
        .calculation-note h5 { margin: 0 0 4px 0; font-size: 9pt; text-transform: uppercase; font-weight: bold; }
        .calculation-note ul { padding-left: 15px; margin: 0; }
        .calculation-note li { margin-bottom: 2px; }

        .footer-sign { width: 100%; margin-top: 25px; page-break-inside: avoid; border-collapse: collapse; }
        .footer-sign td { text-align: center; width: 50%; vertical-align: bottom; font-size: 10pt; border: none; }
    </style>
</head>
<body>

    @include('partials._kop')

    <main>
        <div class="document-title">KARTU HASIL STUDI (KHS)</div>

        <div class="student-info">
            <table>
                <tr>
                    <td class="label">NIM</td>
                    <td style="width: 38%;">: <span class="font-monospace">{{ $mahasiswa->nim }}</span></td>
                    <td class="label">DOSEN TUTOR</td>
                    <td>: {{ optional($mahasiswa->dosenWali)->nama_lengkap ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">NAMA LENGKAP</td>
                    <td>: <strong>{{ $mahasiswa->nama_lengkap }}</strong></td>
                    <td class="label">TAHUN AKADEMIK</td>
                    <td>: <span class="font-monospace">{{ $tahunSelected->tahun }}</span></td>
                </tr>
                <tr>
                    <td class="label">PROGRAM STUDI</td>
                    <td>: {{ optional($mahasiswa->programStudi)->nama_prodi ?? '-' }}</td>
                    <td class="label">SEMESTER</td>
                    <td>: <span class="font-monospace">{{ strtoupper($tahunSelected->semester) }}</span></td>
                </tr>
            </table>
        </div>

        <table class="course-table">
            <thead>
                <tr>
                    <th style="width: 7%;">NO</th>
                    <th style="width: 14%;">KODE MK</th>
                    <th>NAMA MATA KULIAH</th>
                    <th style="width: 9%;">SKS</th>
                    <th style="width: 13%;">NILAI HURUF</th>
                    <th style="width: 13%;">BOBOT (SKSxN)</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($krsSelected as $index => $mk)
                    <tr>
                        <td class="center font-monospace">{{ $index + 1 }}</td>
                        <td class="center font-monospace">{{ $mk->kode_mk }}</td>
                        <td style="text-transform: uppercase; font-weight: bold;">{{ $mk->nama_mk }}</td>
                        <td class="center font-monospace">{{ $mk->sks }}</td>
                        <td class="center font-monospace">{{ $mk->pivot->nilai ?? '-' }}</td>
                        <td class="center font-monospace">{{ $mk->pivot->nilai ? number_format($ipsData['nilaiBobot'][$mk->id] ?? 0, 2) : '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="center" style="padding: 15px; text-transform: uppercase;">Belum ada data mata kuliah pada semester ini.</td>
                    </tr>
                @endforelse
                <tr style="font-weight: bold; background-color: #f2f2f2;">
                    <td colspan="3" style="text-align: right; padding-right: 15px; text-transform: uppercase;">Total SKS / IPS Semester Ini</td>
                    <td class="center font-monospace" style="font-size: 10.5pt;">{{ $ipsData['total_sks'] }}</td>
                    <td colspan="2" class="center font-monospace" style="font-size: 10.5pt;">IPS: {{ number_format($ipsData['ips'], 2) }}</td>
                </tr>
            </tbody>
        </table>

        <div class="summary-box">
            <table class="summary-table">
                <tr>
                    <td>TOTAL SKS KUMULATIF LULUS</td>
                    <td style="text-align: right;" class="font-monospace">{{ $mahasiswa->totalSksLulus() }} SKS</td>
                </tr>
                <tr>
                    <td style="padding-top: 6px;">INDEKS PRESTASI KUMULATIF (IPK) SAAT INI</td>
                    <td style="text-align: right; font-size: 13pt; padding-top: 6px;" class="font-monospace">{{ number_format($mahasiswa->hitungIpk(), 2) }}</td>
                </tr>
            </table>
        </div>

        <div class="calculation-note">
            <h5>KETERANGAN PERHITUNGAN:</h5>
            <ul>
                <li><strong>Bobot Nilai:</strong> A = 4.00, B = 3.00, C = 2.00, D = 1.00, E = 0.00</li>
                <li><strong>IPS (Indeks Prestasi Semester)</strong> = Jumlah (SKS x Bobot Nilai) / Total SKS yang diambil pada semester berjalan.</li>
                <li><strong>IPK (Indeks Prestasi Kumulatif)</strong> = Total Seluruh (SKS x Bobot Nilai Terbaik) / Total Seluruh SKS yang telah ditempuh.</li>
            </ul>
        </div>

        <table class="footer-sign">
            <tr>
                <td></td>
                <td>
                    Fakfak, {{ now()->translatedFormat('d F Y') }}<br>
                    Ketua Program Studi {{ optional($mahasiswa->programStudi)->nama_prodi }},<br><br><br><br>
                    <strong style="text-transform: uppercase; text-decoration: underline;">{{ optional(optional($mahasiswa->programStudi)->kaprodi)->nama_lengkap ?? '........................................' }}</strong><br>
                    <span class="font-monospace" style="font-size: 9pt;">NIDN. {{ optional(optional($mahasiswa->programStudi)->kaprodi)->nidn ?? '-' }}</span>
                </td>
            </tr>
        </table>

        @include('partials._qr_verifikasi', ['jenisDoc' => 'KHS', 'idEntitas' => $mahasiswa->id])
    </main>
</body>
</html>