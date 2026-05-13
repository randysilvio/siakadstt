<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Hasil EDOM - {{ $dosen->nama_lengkap }}</title>
    <style>
        body { 
            font-family: 'Times New Roman', Times, serif; 
            font-size: 11pt; 
            line-height: 1.5;
            max-width: 210mm; 
            margin: 0 auto; 
            padding: 20px;
            color: #000;
        }
        .doc-title { text-align: center; font-weight: bold; font-size: 14pt; margin: 20px 0; text-transform: uppercase; text-decoration: underline; }
        
        .meta-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .meta-table td { padding: 4px; vertical-align: top; border: none; }
        .meta-label { width: 150px; font-weight: bold; }
        .meta-colon { width: 10px; text-align: center; }

        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .data-table th, .data-table td { border: 1px solid #000; padding: 6px 8px; vertical-align: middle; }
        .data-table th { background-color: #f0f0f0; text-align: center; font-weight: bold; }
        
        .score-box { text-align: center; font-weight: bold; }
        .total-row td { font-weight: bold; background-color: #e9ecef; }
        
        .section-title { font-weight: bold; font-size: 12pt; margin-bottom: 10px; margin-top: 20px;}
        .feedback-list { margin: 0; padding-left: 20px; }
        .feedback-item { margin-bottom: 8px; font-style: italic; }

        .signature-container { width: 100%; display: table; margin-top: 50px; page-break-inside: avoid; }
        .signature-box { display: table-cell; width: 40%; text-align: center; vertical-align: bottom; }
        .signature-spacer { display: table-cell; width: 20%; }
        .signature-line { margin-top: 80px; font-weight: bold; text-decoration: underline; }

        .page-break { page-break-before: always; }

        .no-print { text-align: right; margin-bottom: 20px; border-bottom: 1px solid #ccc; padding-bottom: 15px; }
        .btn-print { background-color: #000; color: #fff; border: none; padding: 8px 20px; font-family: Arial, sans-serif; cursor: pointer; text-transform: uppercase; font-size: 12px; }

        @media print { 
            @page { size: A4 portrait; margin: 1.5cm; }
            .no-print { display: none !important; } 
            body { padding: 0; margin: 0; }
        }
    </style>
</head>
<body>

    <div class="no-print">
        <button onclick="window.print()" class="btn-print">Cetak Laporan Akademik</button>
    </div>

    @include('partials._kop')

    <div class="doc-title">LAPORAN HASIL EVALUASI DOSEN OLEH MAHASISWA (EDOM)</div>

    <table class="meta-table">
        <tr>
            <td class="meta-label">Nama Lengkap Dosen</td>
            <td class="meta-colon">:</td>
            <td>{{ $dosen->nama_lengkap }}</td>
        </tr>
        <tr>
            <td class="meta-label">Nomor Induk (NIDN)</td>
            <td class="meta-colon">:</td>
            <td>{{ $dosen->nidn }}</td>
        </tr>
        <tr>
            <td class="meta-label">Periode Evaluasi</td>
            <td class="meta-colon">:</td>
            <td>{{ $sesi->nama_sesi }}</td>
        </tr>
        <tr>
            <td class="meta-label">Partisipasi Mahasiswa</td>
            <td class="meta-colon">:</td>
            <td>{{ $jumlahResponden }} Responden</td>
        </tr>
    </table>

    <div class="section-title">A. TABEL SKOR INDIKATOR PENILAIAN (SKALA 4.00)</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">No.</th>
                <th>Deskripsi Indikator Kinerja</th>
                <th style="width: 15%;">Nilai Rata-Rata</th>
                <th style="width: 20%;">Kualifikasi</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($detailPerPertanyaan as $item)
            <tr>
                <td style="text-align: center;">{{ $item->urutan }}</td>
                <td>{{ $item->pertanyaan }}</td>
                <td class="score-box">{{ number_format($item->skor_rata_rata, 2) }}</td>
                <td style="text-align: center;">
                    @if($item->skor_rata_rata >= 3.5) SANGAT BAIK
                    @elseif($item->skor_rata_rata >= 2.5) BAIK
                    @elseif($item->skor_rata_rata >= 1.5) CUKUP
                    @else KURANG
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="2" style="text-align: right; padding-right: 15px;">INDEKS KINERJA DOSEN (IKD) KESELURUHAN</td>
                <td class="score-box" style="font-size: 14pt;">{{ number_format($totalRataRata, 2) }}</td>
                <td style="text-align: center;">
                    @if($totalRataRata >= 3.5) SANGAT BAIK
                    @elseif($totalRataRata >= 2.5) BAIK
                    @elseif($totalRataRata >= 1.5) CUKUP
                    @else KURANG
                    @endif
                </td>
            </tr>
        </tfoot>
    </table>

    @if($masukanTeks->isNotEmpty())
        {{-- Hanya buat page break jika umpan balik sangat banyak, jika sedikit lanjutkan di halaman yang sama --}}
        @if($masukanTeks->count() > 10)
            <div class="page-break"></div>
        @endif
        
        <div class="section-title">B. CATATAN UMPAN BALIK KUALITATIF MAHASISWA</div>
        <ul class="feedback-list">
            @foreach ($masukanTeks as $masukan)
                <li class="feedback-item">"{{ $masukan->jawaban_teks }}"</li>
            @endforeach
        </ul>
    @endif

    <div class="signature-container">
        <div class="signature-spacer"></div>
        <div class="signature-box">
            Fakfak, {{ now()->translatedFormat('d F Y') }}<br>
            Ketua Lembaga Penjaminan Mutu
            <div class="signature-line">( ........................................ )</div>
        </div>
    </div>

</body>
</html>