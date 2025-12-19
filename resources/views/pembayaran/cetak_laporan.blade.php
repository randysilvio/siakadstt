<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Keuangan</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        
        /* Styling Judul Laporan */
        .report-title {
            text-align: center;
            margin-top: 5px;
            margin-bottom: 20px;
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
            text-decoration: underline;
        }

        /* Styling Informasi Filter (Header) */
        .meta-info { margin-bottom: 15px; font-size: 11px; }
        .meta-table { width: auto; border: none; }
        .meta-table td { border: none; padding: 2px 10px 2px 0; }

        /* Styling Tabel Data Utama */
        table.data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.data-table th, 
        table.data-table td { border: 1px solid #000; padding: 6px 8px; text-align: left; vertical-align: middle; }
        table.data-table th { background-color: #f0f0f0; font-weight: bold; text-align: center; }
        
        /* Utility Classes */
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .badge-lunas { color: green; font-weight: bold; text-transform: uppercase; }
        .badge-belum { color: red; font-weight: bold; text-transform: uppercase; }
        .badge-cek { color: orange; font-weight: bold; text-transform: uppercase; }
        .badge-camaba { color: #0056b3; font-style: italic; font-size: 10px; }
        
        /* Footer Cetakan */
        .footer { position: fixed; bottom: 0; width: 100%; text-align: right; font-size: 10px; color: #666; font-style: italic; }
    </style>
</head>
<body>

    {{-- 1. INCLUDE KOP SURAT --}}
    @include('partials._kop')

    {{-- 2. JUDUL LAPORAN --}}
    <div class="report-title">Laporan Status Pembayaran</div>

    {{-- 3. INFORMASI FILTER --}}
    <div class="meta-info">
        <table class="meta-table">
            <tr>
                <td><strong>Periode Semester</strong></td>
                <td>: {{ $filterInfo['semester'] ?? '-' }}</td>
            </tr>
            <tr>
                <td><strong>Status Filter</strong></td>
                <td>: {{ $filterInfo['status'] ?? '-' }}</td>
            </tr>
            <tr>
                <td><strong>Tipe User</strong></td>
                <td>: {{ $filterInfo['tipe_user'] ?? 'Semua' }}</td>
            </tr>
            <tr>
                <td><strong>Dicetak Oleh</strong></td>
                <td>: {{ $filterInfo['pencetak'] ?? '-' }}</td>
            </tr>
            <tr>
                <td><strong>Tanggal Cetak</strong></td>
                <td>: {{ $filterInfo['tanggal_cetak'] ?? date('d M Y') }}</td>
            </tr>
        </table>
    </div>

    {{-- 4. TABEL DATA --}}
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 15%;">NIM / ID</th>
                <th style="width: 25%;">Nama Pembayar</th>
                <th style="width: 20%;">Keterangan</th>
                <th style="width: 15%;">Jumlah</th>
                <th style="width: 10%;">Status</th>
                <th style="width: 10%;">Tgl Bayar</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pembayarans as $index => $p)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                
                {{-- KOLOM NIM --}}
                <td class="text-center">
                    @if($p->mahasiswa)
                        {{ $p->mahasiswa->nim }}
                    @else
                        <span class="badge-camaba">(CAMABA)</span>
                    @endif
                </td>

                {{-- KOLOM NAMA --}}
                <td>
                    @if($p->mahasiswa)
                        {{ $p->mahasiswa->nama_lengkap }}
                    @elseif($p->user)
                        {{ $p->user->name }}
                    @else
                        <span style="color: red; font-style: italic;">User Terhapus</span>
                    @endif
                </td>

                {{-- KOLOM KETERANGAN --}}
                <td>
                    {{ $p->semester }}
                    @if($p->jenis_pembayaran != 'spp')
                        <br><small style="color: #666;">({{ ucwords(str_replace('_', ' ', $p->jenis_pembayaran)) }})</small>
                    @endif
                </td>

                <td class="text-right">Rp {{ number_format($p->jumlah, 0, ',', '.') }}</td>
                
                <td class="text-center">
                    @if($p->status == 'lunas')
                        <span class="badge-lunas">LUNAS</span>
                    @elseif($p->status == 'menunggu_konfirmasi')
                        <span class="badge-cek">CEK</span>
                    @else
                        <span class="badge-belum">BELUM</span>
                    @endif
                </td>
                
                <td class="text-center">
                    {{ $p->tanggal_bayar ? \Carbon\Carbon::parse($p->tanggal_bayar)->format('d/m/Y') : '-' }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center" style="padding: 15px;">
                    <em>Data tidak ditemukan untuk kriteria filter ini.</em>
                </td>
            </tr>
            @endforelse
        </tbody>
        
        {{-- FOOTER TABEL (REKAPITULASI) --}}
        <tfoot>
            {{-- 1. Total LUNAS --}}
            <tr>
                <td colspan="4" class="text-right"><strong>TOTAL PENERIMAAN (SUDAH LUNAS)</strong></td>
                <td class="text-right" style="background-color: #e8f5e9;">
                    <strong style="color: green;">Rp {{ number_format($pembayarans->where('status', 'lunas')->sum('jumlah'), 0, ',', '.') }}</strong>
                </td>
                <td colspan="2" style="background-color: #f9f9f9;"></td>
            </tr>

            {{-- 2. Total TUNGGAKAN --}}
            <tr>
                <td colspan="4" class="text-right"><strong>TOTAL TUNGGAKAN (BELUM LUNAS)</strong></td>
                <td class="text-right" style="background-color: #ffebee;">
                    <strong style="color: red;">Rp {{ number_format($pembayarans->where('status', '!=', 'lunas')->sum('jumlah'), 0, ',', '.') }}</strong>
                </td>
                <td colspan="2" style="background-color: #f9f9f9;">
                    <small><em>*Termasuk Menunggu Konfirmasi</em></small>
                </td>
            </tr>

            {{-- 3. GRAND TOTAL (SEMUA) --}}
            <tr>
                <td colspan="4" class="text-right"><strong>TOTAL ESTIMASI PENDAPATAN (SEMUA)</strong></td>
                <td class="text-right" style="background-color: #e3f2fd;">
                    <strong style="color: #0d47a1;">Rp {{ number_format($pembayarans->sum('jumlah'), 0, ',', '.') }}</strong>
                </td>
                <td colspan="2" style="background-color: #f9f9f9;">
                    <small><em>*Potensi Total</em></small>
                </td>
            </tr>
        </tfoot>
    </table>

    {{-- 5. FOOTER HALAMAN --}}
    <div class="footer">
        Dicetak melalui Sistem Informasi Akademik (SIAKAD) STT GPI Papua
    </div>

</body>
</html>