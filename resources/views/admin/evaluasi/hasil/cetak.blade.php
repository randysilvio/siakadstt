<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Hasil EDOM - {{ $dosen->nama_lengkap }}</title>
    <style>
        @page { size: A4 portrait; margin: 1.5cm; }
        body { 
            font-family: 'Times New Roman', Times, serif; 
            font-size: 11pt; 
            line-height: 1.5;
            color: #000;
        }

        .doc-title { 
            text-align: center; font-weight: bold; font-size: 14pt; margin: 25px 0; 
            text-transform: uppercase; text-decoration: underline; 
        }

        .meta-box { 
            border: 1px solid #000; 
            padding: 10px 15px; 
            margin-bottom: 25px; 
            background-color: #f9f9f9; 
        }
        .meta-table { width: 100%; border-collapse: collapse; }
        .meta-table td { padding: 4px; vertical-align: top; }

        .section-title { font-weight: bold; font-size: 12pt; margin-bottom: 10px; margin-top: 20px; text-transform: uppercase; }
        
        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .data-table th, .data-table td { border: 1px solid #000; padding: 8px; vertical-align: middle; }
        .data-table th { background-color: #eaeaea; text-align: center; font-weight: bold; }
        
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }

        .feedback-list { padding-left: 20px; margin-top: 5px; }
        .feedback-item { margin-bottom: 8px; font-style: italic; text-align: justify; }

        .signature-table { width: 100%; margin-top: 50px; page-break-inside: avoid; }
    </style>
</head>
<body>

    @include('partials._kop')

    <div class="doc-title">Laporan Hasil Evaluasi Dosen Oleh Mahasiswa (EDOM)</div>

    <div class="meta-box">
        <table class="meta-table">
            <tr>
                <td style="width: 25%; font-weight: bold;">Nama Lengkap Dosen</td>
                <td style="width: 2%;">:</td>
                <td style="width: 73%; font-weight: bold; text-transform: uppercase;">{{ $dosen->nama_lengkap }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Nomor Induk (NIDN)</td>
                <td>:</td>
                <td>{{ $dosen->nidn ?? '-' }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Periode Evaluasi</td>
                <td>:</td>
                <td>{{ $sesi->nama_sesi }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Partisipasi Mahasiswa</td>
                <td>:</td>
                <td>{{ $jumlahResponden }} Responden Terverifikasi</td>
            </tr>
        </table>
    </div>

    <div class="section-title">A. Tabel Skor Indikator Penilaian (Skala 4.00)</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">No.</th>
                <th style="width: 50%;">Deskripsi Indikator Kinerja</th>
                <th style="width: 20%;">Nilai Rata-Rata</th>
                <th style="width: 25%;">Kualifikasi</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($detailPerPertanyaan as $item)
            <tr>
                <td class="text-center">{{ $item->urutan }}</td>
                <td>{{ $item->pertanyaan }}</td>
                <td class="text-center font-bold">{{ number_format($item->skor_rata_rata, 2) }}</td>
                <td class="text-center">
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
            <tr>
                <td colspan="2" class="text-right font-bold" style="background-color: #eaeaea; padding-right: 15px;">INDEKS KINERJA DOSEN (IKD) KESELURUHAN</td>
                <td class="text-center font-bold" style="background-color: #eaeaea; font-size: 13pt;">{{ number_format($totalRataRata, 2) }}</td>
                <td class="text-center font-bold" style="background-color: #eaeaea; font-size: 11pt;">
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
        <div class="section-title">B. Catatan Umpan Balik Kualitatif Mahasiswa</div>
        <ul class="feedback-list">
            @foreach ($masukanTeks as $masukan)
                <li class="feedback-item">"{{ $masukan->jawaban_teks }}"</li>
            @endforeach
        </ul>
    @endif

    <table class="signature-table">
        <tr>
            <td style="width: 60%;"></td>
            <td style="width: 40%; text-align: center;">
                Fakfak, {{ now()->translatedFormat('d F Y') }}<br>
                Ketua Lembaga Penjaminan Mutu
                <br><br><br><br><br>
                <span style="font-weight: bold; text-decoration: underline;">( ........................................ )</span>
            </td>
        </tr>
    </table>

</body>
</html>