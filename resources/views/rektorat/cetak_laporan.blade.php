<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Eksekutif - Institusi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #fff; font-family: 'Arial', sans-serif; }
        .print-header { border-bottom: 4px solid #000; padding-bottom: 15px; margin-bottom: 30px; }
        .kpi-box { border: 2px solid #000; padding: 15px; border-radius: 0px; text-align: center; }
        .uppercase { text-transform: uppercase; }
        
        @media print {
            .no-print { display: none !important; }
            @page { margin: 1.5cm; }
        }
    </style>
</head>
<body onload="window.print()">

<div class="container my-4">
    
    <div class="text-end mb-4 no-print">
        <button onclick="window.print()" class="btn btn-dark rounded-0 px-3 uppercase fw-bold small me-1">
            <i class="bi bi-printer"></i> Print Ulang
        </button>
        <button onclick="window.close()" class="btn btn-outline-dark rounded-0 px-3 uppercase fw-bold small">
            Tutup
        </button>
    </div>

    <div class="print-header text-center">
        <h2 class="fw-bold uppercase text-dark mb-1">LAPORAN KINERJA EKSEKUTIF</h2>
        <h6 class="text-muted uppercase fw-bold small mb-2">
            Periode Semester: <span class="font-monospace">{{ $tahunAkademikAktif ? $tahunAkademikAktif->tahun . ' ' . $tahunAkademikAktif->semester : 'BELUM DITENTUKAN' }}</span>
        </h6>
        <p class="mb-0 small text-muted uppercase fw-bold">
            Dicetak pada: <span class="font-monospace">{{ \Carbon\Carbon::now()->format('d F Y H:i') }}</span>
        </p>
    </div>

    <div class="row g-3 mb-5">
        <div class="col-3">
            <div class="kpi-box">
                <div class="uppercase fw-bold small text-dark mb-2">Mahasiswa Aktif</div>
                <div class="fs-4 fw-bold text-dark font-monospace">{{ $totalMahasiswaAktif }}</div>
            </div>
        </div>
        <div class="col-3">
            <div class="kpi-box">
                <div class="uppercase fw-bold small text-dark mb-2">Pendaftar Baru</div>
                <div class="fs-4 fw-bold text-dark font-monospace">{{ $pendaftarTahunIni }}</div>
            </div>
        </div>
        <div class="col-3">
            <div class="kpi-box">
                <div class="uppercase fw-bold small text-dark mb-2">Pendapatan (Rp)</div>
                <div class="fs-4 fw-bold text-dark font-monospace">{{ number_format($pendapatanSemesterIni, 0, ',', '.') }}</div>
            </div>
        </div>
        <div class="col-3">
            <div class="kpi-box">
                <div class="uppercase fw-bold small text-dark mb-2">Lulusan</div>
                <div class="fs-4 fw-bold text-dark font-monospace">{{ $mahasiswaLulusTahunIni }}</div>
            </div>
        </div>
    </div>

    <h6 class="fw-bold uppercase small text-dark mb-3">Performa Program Studi</h6>
    <table class="table table-bordered align-middle border-dark mb-5">
        <thead class="table-dark text-white small uppercase text-center fw-bold">
            <tr>
                <th style="width: 8%;">NO</th>
                <th class="text-start">PROGRAM STUDI</th>
                <th class="text-start">KEPALA PRODI</th>
                <th style="width: 20%;">MAHASISWA AKTIF</th>
                <th style="width: 15%;">JML DOSEN</th>
            </tr>
        </thead>
        <tbody class="small text-dark">
            @forelse ($kinerjaProdi as $index => $prodi)
                <tr>
                    <td class="text-center font-monospace fw-bold">{{ $index + 1 }}</td>
                    <td class="fw-bold uppercase">{{ $prodi->nama_prodi }}</td>
                    <td class="uppercase">{{ $prodi->kaprodi->nama_lengkap ?? 'BELUM DITENTUKAN' }}</td>
                    <td class="text-center font-monospace">{{ $prodi->jumlah_mahasiswa_aktif }}</td>
                    <td class="text-center font-monospace">{{ $prodi->jumlah_dosen }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center uppercase fw-bold py-3">Tidak ada data program studi.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="row mt-5 pt-5">
        <div class="col-8"></div>
        <div class="col-4 text-center small uppercase fw-bold text-dark">
            <p class="mb-1">Jayapura, <span class="font-monospace">{{ \Carbon\Carbon::now()->format('d F Y') }}</span></p>
            <p class="mb-5">Mengetahui,</p>
            <br><br>
            <p class="text-decoration-underline mb-0">PIMPINAN INSTITUSI</p>
        </div>
    </div>
</div>

</body>
</html>