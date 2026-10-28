<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Eksekutif Rektorat - STT GPI Papua</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 10pt; background-color: #fff; color: #000; line-height: 1.4; margin: 0; padding: 20px; }
        
        .doc-title { text-align: center; font-weight: bold; font-size: 13pt; text-decoration: underline; text-transform: uppercase; margin: 10px 0 4px 0; }
        .print-meta { text-align: center; font-family: 'Courier New', Courier, monospace; font-size: 9.5pt; font-weight: bold; margin-bottom: 18px; }
        
        .section-bar { font-size: 10pt; font-weight: bold; text-transform: uppercase; background-color: #212529; color: #ffffff; padding: 5px 8px; margin-top: 15px; margin-bottom: 8px; }
        
        table.grid-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        table.grid-table th, table.grid-table td { border: 1px solid #000; padding: 6px 8px; font-size: 9.5pt; text-align: center; vertical-align: middle; }
        table.grid-table th { background-color: #f2f2f2; font-weight: bold; text-transform: uppercase; color: #000; }
        
        .text-start { text-align: left !important; }
        .text-end { text-align: right !important; }
        .font-monospace { font-family: 'Courier New', Courier, monospace; font-weight: bold; }
        .uppercase { text-transform: uppercase; }
        .fs-6 { font-size: 11pt; }
        
        .ttd { margin-top: 35px; width: 100%; border-collapse: collapse; font-size: 10pt; text-transform: uppercase; page-break-inside: avoid; }
        .ttd td { border: none; text-align: center; vertical-align: top; }
        
        .no-print { margin-bottom: 15px; text-align: right; border-bottom: 1px solid #ccc; padding-bottom: 10px; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; -webkit-print-color-adjust: exact; }
            @page { size: A4 portrait; margin: 1.5cm; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="no-print">
        <button onclick="window.print()" style="padding: 6px 16px; background: #212529; color: #fff; font-weight: bold; border: none; cursor: pointer; text-transform: uppercase; font-size: 11px;">
            Cetak Dokumen
        </button>
        <button onclick="window.close()" style="padding: 6px 16px; background: #fff; color: #212529; font-weight: bold; border: 1px solid #212529; cursor: pointer; margin-left: 5px; text-transform: uppercase; font-size: 11px;">
            Tutup
        </button>
    </div>

    @include('partials._kop')

    <div class="doc-title">LAPORAN RINGKASAN KINERJA AKADEMIK & KEUANGAN EKSEKUTIF</div>
    <div class="print-meta">
        TANGGAL EKSTRAKSI DATA: {{ \Carbon\Carbon::now()->translatedFormat('d F Y - H:i') }} WIT
    </div>

    <div class="section-bar">A. RINGKASAN METRIK KINERJA UTAMA</div>
    <table class="grid-table">
        <thead>
            <tr>
                <th>MAHASISWA AKTIF</th>
                <th>MAHASISWA BARU ({{ date('Y') }})</th>
                <th>DANA MASUK (SEMESTER INI)</th>
                <th>TOTAL PIUTANG (SEMESTER INI)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="font-monospace fs-6">{{ number_format($totalMahasiswaAktif) }}</td>
                <td class="font-monospace fs-6">{{ number_format($pendaftarTahunIni) }}</td>
                <td class="font-monospace fs-6">Rp {{ number_format($pendapatanSemesterIni, 0, ',', '.') }}</td>
                <td class="font-monospace fs-6">Rp {{ number_format($piutangSemesterIni, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="section-bar">B. DISTRIBUSI PERFORMA & RASIO PROGRAM STUDI</div>
    <table class="grid-table">
        <thead>
            <tr>
                <th style="width: 7%;">NO</th>
                <th class="text-start">PROGRAM STUDI</th>
                <th class="text-start">KETUA PRODI (KAPRODI)</th>
                <th style="width: 13%;">MAHASISWA</th>
                <th style="width: 12%;">DOSEN TETAP</th>
                <th style="width: 18%;">RASIO (DOSEN : MHS)</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($kinerjaProdi as $index => $prodi)
                @php
                    $rasioDosenMhs = $prodi->jumlah_dosen > 0
                        ? "1 : " . round($prodi->jumlah_mahasiswa_aktif / $prodi->jumlah_dosen, 1)
                        : "N/A";
                @endphp
                <tr>
                    <td class="font-monospace">{{ $index + 1 }}</td>
                    <td class="text-start uppercase" style="font-weight: bold;">{{ $prodi->nama_prodi }}</td>
                    <td class="text-start uppercase">{{ $prodi->kaprodi->nama_lengkap ?? 'BELUM DITENTUKAN' }}</td>
                    <td class="font-monospace">{{ $prodi->jumlah_mahasiswa_aktif }}</td>
                    <td class="font-monospace">{{ $prodi->jumlah_dosen }}</td>
                    <td class="font-monospace">{{ $rasioDosenMhs }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="uppercase py-3 text-center" style="font-weight: bold;">Data program studi tidak ditemukan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="ttd">
        <tr>
            <td style="width: 55%;"></td>
            <td style="width: 45%;">
                <p>FAKFAK, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>MENGETAHUI,<br>KETUA STT GPI PAPUA</p>
                <br><br><br><br>
                <p><b><u>( {{ \App\Models\Pengaturan::where('key', 'nama_rektor')->value('value') ?: '...........................................' }} )</u></b></p>
            </td>
        </tr>
    </table>

</body>
</html>