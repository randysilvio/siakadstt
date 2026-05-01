<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Eksekutif - Institusi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #fff; font-family: 'Arial', sans-serif; }
        .print-header { border-bottom: 3px solid #1e3c72; padding-bottom: 15px; margin-bottom: 30px; }
        .kpi-box { border: 1px solid #dee2e6; padding: 15px; border-radius: 8px; text-align: center; }
        .kpi-title { font-size: 12px; text-transform: uppercase; color: #6c757d; font-weight: bold; }
        .kpi-value { font-size: 24px; font-weight: bold; color: #1e3c72; margin: 10px 0; }
        
        @media print {
            .no-print { display: none !important; }
            @page { margin: 1.5cm; }
        }
    </style>
</head>
<body onload="window.print()">

<div class="container my-4">
    
    <!-- Tombol navigasi (Disembunyikan saat print) -->
    <div class="text-end mb-4 no-print">
        <button onclick="window.print()" class="btn btn-primary"><i class="bi bi-printer"></i> Print Ulang</button>
        <button onclick="window.close()" class="btn btn-secondary">Tutup</button>
    </div>

    <!-- Header Laporan -->
    <div class="print-header text-center">
        <h2 class="fw-bold" style="color: #1e3c72;">LAPORAN KINERJA EKSEKUTIF</h2>
        <h5 class="text-muted">Periode Semester: {{ $tahunAkademikAktif ? $tahunAkademikAktif->tahun . ' ' . $tahunAkademikAktif->semester : 'Belum Ditentukan' }}</h5>
        <p class="mb-0">Dicetak pada: {{ \Carbon\Carbon::now()->format('d F Y H:i') }}</p>
    </div>

    <!-- 4 Kotak KPI -->
    <div class="row g-3 mb-5">
        <div class="col-3">
            <div class="kpi-box">
                <div class="kpi-title">Mahasiswa Aktif</div>
                <div class="kpi-value">{{ $totalMahasiswaAktif }}</div>
            </div>
        </div>
        <div class="col-3">
            <div class="kpi-box">
                <div class="kpi-title">Pendaftar Baru</div>
                <div class="kpi-value">{{ $pendaftarTahunIni }}</div>
            </div>
        </div>
        <div class="col-3">
            <div class="kpi-box">
                <div class="kpi-title">Pendapatan (Rp)</div>
                <div class="kpi-value">{{ number_format($pendapatanSemesterIni, 0, ',', '.') }}</div>
            </div>
        </div>
        <div class="col-3">
            <div class="kpi-box">
                <div class="kpi-title">Lulusan</div>
                <div class="kpi-value">{{ $mahasiswaLulusTahunIni }}</div>
            </div>
        </div>
    </div>

    <!-- Tabel Kinerja Prodi -->
    <h5 class="fw-bold mb-3 border-bottom pb-2">Performa Program Studi</h5>
    <table class="table table-bordered table-striped">
        <thead class="table-light">
            <tr>
                <th>No</th>
                <th>Program Studi</th>
                <th>Kepala Prodi</th>
                <th class="text-center">Jml Mahasiswa Aktif</th>
                <th class="text-center">Jml Dosen</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($kinerjaProdi as $index => $prodi)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="fw-bold">{{ $prodi->nama_prodi }}</td>
                    <td>{{ $prodi->kaprodi->nama_lengkap ?? 'Belum Ditentukan' }}</td>
                    <td class="text-center">{{ $prodi->jumlah_mahasiswa_aktif }}</td>
                    <td class="text-center">{{ $prodi->jumlah_dosen }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center">Tidak ada data program studi.</td></tr>
            @endforelse
        </tbody>
    </table>

    <!-- Tanda Tangan -->
    <div class="row mt-5 pt-5">
        <div class="col-8"></div>
        <div class="col-4 text-center">
            <p>Jayapura, {{ \Carbon\Carbon::now()->format('d F Y') }}</p>
            <p>Mengetahui,</p>
            <br><br><br>
            <p class="fw-bold text-decoration-underline mb-0">Pimpinan Institusi</p>
        </div>
    </div>
</div>

</body>
</html>