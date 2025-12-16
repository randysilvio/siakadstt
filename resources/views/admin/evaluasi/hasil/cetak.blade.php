<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Evaluasi Dosen - {{ $dosen->nama_lengkap }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .title { text-align: center; font-weight: bold; margin-bottom: 20px; font-size: 14px; text-transform: uppercase; text-decoration: underline; }
        
        .info-table { width: 100%; margin-bottom: 20px; }
        .info-table td { padding: 3px; vertical-align: top; }
        
        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .data-table th, .data-table td { border: 1px solid #000; padding: 5px; }
        .data-table th { background-color: #f0f0f0; text-align: center; }
        
        .comment-section { margin-top: 20px; }
        .comment-box { border: 1px solid #ccc; padding: 10px; margin-bottom: 5px; background-color: #f9f9f9; }
        
        .score-big { font-size: 14px; font-weight: bold; }
        .page-break { page-break-after: always; }
    </style>
</head>
<body>

    @include('partials._kop')

    <div class="title">Laporan Hasil Evaluasi Kinerja Dosen</div>

    <table class="info-table">
        <tr>
            <td width="150"><strong>Nama Dosen</strong></td>
            <td>: {{ $dosen->nama_lengkap }}</td>
            <td width="120"><strong>Periode Sesi</strong></td>
            <td width="150">: {{ $sesi->nama_sesi }}</td>
        </tr>
        <tr>
            <td><strong>NIDN</strong></td>
            <td>: {{ $dosen->nidn }}</td>
            <td><strong>Jml Responden</strong></td>
            <td>: {{ $jumlahResponden }} Mahasiswa</td>
        </tr>
    </table>

    <h4 style="margin-bottom: 10px;">A. Rekapitulasi Penilaian (Skala 1-4)</h4>
    <table class="data-table">
        <thead>
            <tr>
                <th width="30">No</th>
                <th>Aspek Penilaian</th>
                <th width="80">Skor Rata-rata</th>
                <th width="100">Kategori</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($detailPerPertanyaan as $item)
            <tr>
                <td style="text-align: center;">{{ $loop->iteration }}</td>
                <td>{{ $item->pertanyaan }}</td>
                <td style="text-align: center; font-weight: bold;">{{ number_format($item->skor_rata_rata, 2) }}</td>
                <td style="text-align: center;">
                    @if($item->skor_rata_rata >= 3.5) Sangat Baik
                    @elseif($item->skor_rata_rata >= 2.5) Baik
                    @elseif($item->skor_rata_rata >= 1.5) Cukup
                    @else Kurang
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2" style="text-align: right; font-weight: bold; padding-right: 10px;">TOTAL SKOR AKHIR</td>
                <td style="text-align: center; background-color: #eee;" class="score-big">{{ number_format($totalRataRata, 2) }}</td>
                <td style="text-align: center; background-color: #eee;">
                    @if($totalRataRata >= 3.5) Sangat Baik
                    @elseif($totalRataRata >= 2.5) Baik
                    @elseif($totalRataRata >= 1.5) Cukup
                    @else Kurang
                    @endif
                </td>
            </tr>
        </tfoot>
    </table>

    {{-- Masukan Teks jika ada --}}
    @if($masukanTeks->isNotEmpty())
        <div class="page-break"></div> {{-- Pindah halaman untuk komentar jika panjang --}}
        
        <h4 style="margin-bottom: 10px;">B. Masukan dan Saran Mahasiswa</h4>
        <div class="comment-section">
            @foreach ($masukanTeks as $masukan)
                <div class="comment-box">
                    <i>"{{ $masukan->jawaban_teks }}"</i>
                </div>
            @endforeach
        </div>
    @endif

    <div style="margin-top: 50px; text-align: right;">
        <p>Fakfak, {{ now()->isoFormat('D MMMM Y') }}</p>
        <p>Mengetahui,</p>
        <br><br><br>
        <p><strong>Bagian Penjaminan Mutu</strong></p>
    </div>

</body>
</html>