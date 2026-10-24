<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan BKD & CV Akademik - {{ $dosen->nama_lengkap }}</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 10pt; color: #000; line-height: 1.35; }
        @page { size: A4 portrait; margin: 1.5cm; }
        .doc-title { text-align: center; font-weight: bold; font-size: 13pt; text-decoration: underline; text-transform: uppercase; margin: 8px 0 3px 0; }
        .doc-sub { text-align: center; font-size: 9.5pt; font-weight: bold; margin-bottom: 14px; font-family: 'Courier New', Courier, monospace; }
        .section-bar { font-size: 9.5pt; font-weight: bold; text-transform: uppercase; background-color: #212529; color: #ffffff; padding: 4px 8px; margin-top: 14px; margin-bottom: 6px; }
        table.meta-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        table.meta-table td { padding: 3px 4px; border: none; font-size: 9.5pt; text-transform: uppercase; }
        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        table.data-table th, table.data-table td { border: 1px solid #000; padding: 5px 6px; font-size: 9pt; vertical-align: middle; }
        table.data-table th { background-color: #f0f0f0; text-align: center; font-weight: bold; text-transform: uppercase; }
        .text-center { text-align: center; }
        .font-monospace { font-family: 'Courier New', Courier, monospace; font-weight: bold; }
        .ttd { margin-top: 25px; width: 100%; border-collapse: collapse; font-size: 9.5pt; text-transform: uppercase; page-break-inside: avoid; }
        .ttd td { border: none; text-align: center; vertical-align: top; }
    </style>
</head>
<body>

    @include('partials._kop')

    <div class="doc-title">LAPORAN BEBAN KERJA DOSEN (BKD) & PORTOFOLIO TRIDHARMA</div>
    <div class="doc-sub">STANDAR PELAPORAN SISTER & AKREDITASI INSTITUSI | TANGGAL CETAK: {{ date('d/m/Y') }}</div>

    <div class="section-bar">I. IDENTITAS TENAGA PENDIDIK (DOSEN)</div>
    <table class="meta-table">
        <tr>
            <td style="width: 22%;"><strong>NAMA LENGKAP</strong></td>
            <td style="width: 33%;">: <strong>{{ $dosen->nama_lengkap }}</strong></td>
            <td style="width: 22%;"><strong>JABATAN AKADEMIK</strong></td>
            <td style="width: 23%;">: {{ $dosen->jabatan_akademik ?? 'TENAGA PENGAJAR' }}</td>
        </tr>
        <tr>
            <td><strong>NIDN / NUPTK</strong></td>
            <td class="font-monospace">: {{ $dosen->nidn ?? '-' }} / {{ $dosen->nuptk ?? '-' }}</td>
            <td><strong>PANGKAT / GOLONGAN</strong></td>
            <td>: {{ $dosen->pangkat_golongan ?? '-' }}</td>
        </tr>
        <tr>
            <td><strong>PROGRAM STUDI</strong></td>
            <td>: {{ optional($dosen->programStudi)->nama_prodi ?? 'STT GPI PAPUA' }}</td>
            <td><strong>TOTAL SKS BKD / KUM</strong></td>
            <td class="font-monospace">: {{ $ringkasanBkd['total_sks_bkd'] }} SKS / {{ $ringkasanBkd['total_kum'] }} KUM</td>
        </tr>
    </table>

    <div class="section-bar">II. UNSUR PENDIDIKAN & PENGAJARAN ({{ $ringkasanBkd['sks_pendidikan'] }} SKS)</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 6%;">NO</th>
                <th style="width: 15%;">KODE MK</th>
                <th>NAMA MATA KULIAH DIAMPU</th>
                <th style="width: 10%;">SMT</th>
                <th style="width: 12%;">SKS</th>
                <th style="width: 16%;">STATUS RPS</th>
            </tr>
        </thead>
        <tbody>
            @forelse($mataKuliahs as $i => $mk)
                <tr>
                    <td class="text-center font-monospace">{{ $i + 1 }}</td>
                    <td class="text-center font-monospace">{{ $mk->kode_mk }}</td>
                    <td style="font-weight: bold; text-transform: uppercase;">{{ $mk->nama_mk }}</td>
                    <td class="text-center font-monospace">{{ $mk->semester }}</td>
                    <td class="text-center font-monospace">{{ $mk->sks }}</td>
                    <td class="text-center font-monospace">{{ $mk->file_rps ? 'TERSEDIA' : 'BELUM ADA' }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center">Belum ada mata kuliah aktif.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-bar">III. UNSUR PENELITIAN & PUBLIKASI ILMIAH ({{ $ringkasanBkd['sks_penelitian'] }} SKS)</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 6%;">NO</th>
                <th>JUDUL KARYA ILMIAH / PENELITIAN</th>
                <th style="width: 22%;">KATEGORI / JURNAL</th>
                <th style="width: 10%;">TAHUN</th>
                <th style="width: 10%;">SKS BKD</th>
                <th style="width: 10%;">KUM</th>
            </tr>
        </thead>
        <tbody>
            @forelse($penelitians as $i => $p)
                <tr>
                    <td class="text-center font-monospace">{{ $i + 1 }}</td>
                    <td><strong>{{ strtoupper($p->judul) }}</strong><br><small>{{ $p->nama_jurnal_penerbit_lokasi }} ({{ $p->nomor_identitas }})</small></td>
                    <td class="text-center">{{ strtoupper($p->kategori) }}</td>
                    <td class="text-center font-monospace">{{ $p->tahun }}</td>
                    <td class="text-center font-monospace">{{ $p->sks_bkd }}</td>
                    <td class="text-center font-monospace">{{ $p->angka_kredit }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center">Belum ada data penelitian tercatat.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-bar">IV. UNSUR PENGABDIAN KEPADA MASYARAKAT / PKM ({{ $ringkasanBkd['sks_pengabdian'] }} SKS)</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 6%;">NO</th>
                <th>JUDUL KEGIATAN PKM / PELAYANAN GEREJAWI</th>
                <th style="width: 22%;">LOKASI / MITRA</th>
                <th style="width: 10%;">TAHUN</th>
                <th style="width: 10%;">SKS BKD</th>
                <th style="width: 10%;">KUM</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pengabdians as $i => $pkm)
                <tr>
                    <td class="text-center font-monospace">{{ $i + 1 }}</td>
                    <td><strong>{{ strtoupper($pkm->judul) }}</strong> ({{ strtoupper($pkm->peran) }})</td>
                    <td class="text-center">{{ strtoupper($pkm->nama_jurnal_penerbit_lokasi ?? '-') }}</td>
                    <td class="text-center font-monospace">{{ $pkm->tahun }}</td>
                    <td class="text-center font-monospace">{{ $pkm->sks_bkd }}</td>
                    <td class="text-center font-monospace">{{ $pkm->angka_kredit }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center">Belum ada data pengabdian masyarakat tercatat.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-bar">V. UNSUR PENUNJANG AKADEMIK & SK KEPANITIAAN ({{ $ringkasanBkd['sks_penunjang'] }} SKS)</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 6%;">NO</th>
                <th>KEGIATAN PENUNJANG / SURAT KEPUTUSAN (SK)</th>
                <th style="width: 22%;">PERAN / JABATAN</th>
                <th style="width: 22%;">NOMOR SK / SERTIFIKAT</th>
            </tr>
        </thead>
        <tbody>
            @php $noPen = 1; @endphp
            @foreach($suratKeputusans as $sk)
                <tr>
                    <td class="text-center font-monospace">{{ $noPen++ }}</td>
                    <td><strong>{{ strtoupper($sk->judul) }}</strong></td>
                    <td class="text-center">{{ strtoupper($sk->pivot->jabatan_dalam_surat ?? 'ANGGOTA') }}</td>
                    <td class="text-center font-monospace">{{ $sk->nomor_surat }}</td>
                </tr>
            @endforeach
            @foreach($penunjangs as $pen)
                <tr>
                    <td class="text-center font-monospace">{{ $noPen++ }}</td>
                    <td><strong>{{ strtoupper($pen->judul) }}</strong> ({{ $pen->tahun }})</td>
                    <td class="text-center">{{ strtoupper($pen->peran) }}</td>
                    <td class="text-center font-monospace">{{ $pen->nomor_identitas ?? '-' }}</td>
                </tr>
            @endforeach
            @if($noPen === 1)
                <tr><td colspan="4" class="text-center">Belum ada data penunjang tercatat.</td></tr>
            @endif
        </tbody>
    </table>

    <table class="ttd">
        <tr>
            <td style="width: 50%;">
                Mengetahui / Mengesahkan,<br>
                Ketua Unit Penjaminan Mutu (UPM)<br><br><br><br>
                <strong><u>( ................................................. )</u></strong>
            </td>
            <td style="width: 50%;">
                Fakfak, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
                Dosen Yang Membuat Pernyataan,<br><br><br><br>
                <strong><u>{{ $dosen->nama_lengkap }}</u></strong><br>
                <span class="font-monospace">NIDN. {{ $dosen->nidn ?? '-' }}</span>
            </td>
        </tr>
    </table>

</body>
</html>