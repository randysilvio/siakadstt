<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>KHS - {{ $mahasiswa->nim }} - {{ $tahunSelected->semester }} {{ $tahunSelected->tahun }}</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 9.5pt; line-height: 1.25; color: #000; margin: 0; padding: 0; }
        @page { size: A4 portrait; margin: 1.0cm 1.2cm; }

        .document-title { text-align: center; font-weight: bold; font-size: 12pt; text-decoration: underline; text-transform: uppercase; margin: 6px 0 10px 0; }
        
        .student-info { margin-bottom: 8px; }
        .student-info table { width: 100%; border-collapse: collapse; }
        .student-info td { padding: 2px 0; vertical-align: top; text-transform: uppercase; font-size: 9pt; border: none; }
        .student-info .label { font-weight: bold; width: 125px; }
        
        table.course-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .course-table th, .course-table td { border: 1px solid #000; padding: 4px 6px; vertical-align: middle; }
        .course-table th { background-color: #212529; color: #ffffff; text-transform: uppercase; font-size: 8.5pt; text-align: center; font-weight: bold; }
        .course-table td { font-size: 8.8pt; }
        .course-table td.center { text-align: center; }
        .font-monospace { font-family: 'Courier New', Courier, monospace; font-weight: bold; }
        
        .summary-box { border: 1px solid #000; padding: 6px 10px; margin-top: 6px; background-color: #f8fafc; page-break-inside: avoid; }
        .summary-table { width: 100%; border-collapse: collapse; text-transform: uppercase; font-size: 9pt; font-weight: bold; }
        .summary-table td { padding: 2px 0; border: none; }
        
        .calculation-note { margin-top: 6px; font-size: 7.5pt; line-height: 1.25; page-break-inside: avoid; color: #333; }

        .footer-sign { width: 100%; margin-top: 10px; page-break-inside: avoid; border-collapse: collapse; }
        .footer-sign td { text-align: center; width: 50%; vertical-align: top; font-size: 9pt; border: none; }
    </style>
</head>
<body>

    @include('partials._kop')

    @php
        $kotaTtd = \App\Models\Pengaturan::get('kota_domisili', 'Fakfak');
        $istilahPembimbing = \App\Models\Pengaturan::get('istilah_pembimbing', 'Dosen Tutor');
    @endphp

    <main>
        <div class="document-title">KARTU HASIL STUDI (KHS)</div>

        <div class="student-info">
            <table>
                <tr>
                    <td class="label">NIM</td>
                    <td style="width: 38%;">: <span class="font-monospace">{{ $mahasiswa->nim }}</span></td>
                    <td class="label">{{ strtoupper($istilahPembimbing) }}</td>
                    <td>: {{ optional($mahasiswa->dosenWali)->nama_lengkap ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">NAMA LENGKAP</td>
                    <td>: <strong>{{ $mahasiswa->nama_lengkap }}</strong></td>
                    <td class="label">TAHUN AKADEMIK</td>
                    <td>: <span class="font-monospace">{{ $tahunSelected->tahun }} ({{ strtoupper($tahunSelected->semester) }})</span></td>
                </tr>
                <tr>
                    <td class="label">PROGRAM STUDI</td>
                    <td colspan="3">: {{ optional($mahasiswa->programStudi)->nama_prodi ?? '-' }}</td>
                </tr>
            </table>
        </div>

        <table class="course-table">
            <thead>
                <tr>
                    <th style="width: 6%;">NO</th>
                    <th style="width: 14%;">KODE MK</th>
                    <th>NAMA MATA KULIAH</th>
                    <th style="width: 9%;">SKS</th>
                    <th style="width: 13%;">NILAI HURUF</th>
                    <th style="width: 14%;">BOBOT (SKSxN)</th>
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
                        <td colspan="6" class="center" style="padding: 10px; text-transform: uppercase;">Belum ada data mata kuliah pada semester ini.</td>
                    </tr>
                @endforelse
                <tr style="font-weight: bold; background-color: #f2f2f2;">
                    <td colspan="3" style="text-align: right; padding-right: 12px; text-transform: uppercase;">Total SKS / IPS Semester Ini</td>
                    <td class="center font-monospace">{{ $ipsData['total_sks'] }}</td>
                    <td colspan="2" class="center font-monospace">IPS: {{ number_format($ipsData['ips'], 2) }}</td>
                </tr>
            </tbody>
        </table>

        <div class="summary-box">
            <table class="summary-table">
                <tr>
                    <td>TOTAL SKS KUMULATIF LULUS: <span class="font-monospace">{{ $mahasiswa->totalSksLulus() }} SKS</span></td>
                    <td style="text-align: right;">INDEKS PRESTASI KUMULATIF (IPK) SAAT INI: <span class="font-monospace" style="font-size: 10.5pt;">{{ number_format($mahasiswa->hitungIpk(), 2) }}</span></td>
                </tr>
            </table>
        </div>

        <div class="calculation-note">
            <strong>Keterangan:</strong> Bobot Nilai: A = 4.00, B = 3.00, C = 2.00, D = 1.00, E = 0.00 | IPS = Jumlah (SKS x Bobot) / SKS Semester | IPK = Total (SKS x Bobot Terbaik) / Total SKS.
        </div>

        <table class="footer-sign">
            <tr>
                <td></td>
                <td>
                    {{ $kotaTtd }}, {{ now()->translatedFormat('d F Y') }}<br>
                    Ketua Program Studi {{ optional($mahasiswa->programStudi)->nama_prodi }},<br>
                    <div style="height: 42px;"></div>
                    <strong style="text-transform: uppercase; text-decoration: underline;">{{ optional(optional($mahasiswa->programStudi)->kaprodi)->nama_lengkap ?? '........................................' }}</strong><br>
                    <span class="font-monospace" style="font-size: 8pt;">NIDN. {{ optional(optional($mahasiswa->programStudi)->kaprodi)->nidn ?? '-' }}</span>
                </td>
            </tr>
        </table>

        @include('partials._qr_verifikasi', ['jenisDoc' => 'KHS', 'idEntitas' => $mahasiswa->id, 'customToken' => $tokenDokumen ?? null])
    </main>
</body>
</html>