<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
        $cfg             = \App\Models\Pengaturan::allCached();
        $logoUrl         = \App\Models\Pengaturan::logoUrl();
        $kopBaris1       = $cfg['kop_baris_1'] ?? 'GEREJA PROTESTAN INDONESIA DI PAPUA';
        $kopBaris2       = $cfg['kop_baris_2'] ?? 'SEKOLAH TINGGI TEOLOGI (STT) GPI PAPUA';
        $kopBaris3       = $cfg['kop_baris_3'] ?? 'TERAKREDITASI BAN-PT';
        $kopAlamat       = $cfg['kop_alamat'] ?? 'Jl. Jend. Sudirman, Kotak Pos 116, Fakfak 98611 - Provinsi Papua Barat';
        $kotaTtd         = strtoupper($cfg['kota_domisili'] ?? 'FAKFAK');
        $sebutanPimpinan = strtoupper($cfg['sebutan_pimpinan'] ?? 'KETUA');
        $namaKampusUpper = strtoupper($cfg['nama_kampus'] ?? 'SEKOLAH TINGGI TEOLOGI GPI PAPUA');
    @endphp
    <title>Pratinjau Cetak - {{ $suratKeputusan->judul }}</title>
    <link rel="icon" href="{{ $logoUrl }}" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background-color: #525659; padding-top: 30px; padding-bottom: 30px; display: flex; flex-direction: column; align-items: center; font-family: 'Times New Roman', Times, serif; }
        .a4-page { background: white; width: 210mm; min-height: 297mm; padding: 20mm 20mm 20mm 20mm; margin-bottom: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.25); position: relative; font-size: 11pt; line-height: 1.35; color: black; }
        .kop-surat { border-bottom: 3px solid black; margin-bottom: 2px; padding-bottom: 6px; }
        .kop-surat-2 { border-bottom: 1px solid black; margin-bottom: 18px; }
        .teks-tengah { text-align: center; }
        .huruf-tebal { font-weight: bold; }
        .huruf-kapital { text-transform: uppercase; }
        table.tabel-diktum { width: 100%; border-collapse: collapse; margin-bottom: 5px; }
        table.tabel-diktum td { vertical-align: top; padding: 2px 0; }
        
        @media print {
            body { background-color: white; padding: 0; margin: 0; }
            .no-print { display: none !important; }
            .a4-page { margin: 0; box-shadow: none; width: 100%; padding: 15mm 20mm 20mm 20mm; page-break-after: always; }
            .a4-page:last-child { page-break-after: auto; }
            .kotak-pengesahan { page-break-inside: avoid; }
        }
    </style>
</head>
<body>

    <div class="no-print position-fixed top-0 start-0 m-3 z-3">
        <button onclick="window.print()" class="btn btn-dark rounded-0 px-4 py-2 fw-bold text-uppercase shadow">
            <i class="bi bi-printer me-2"></i> Cetak Dokumen
        </button>
        <button onclick="window.close()" class="btn btn-outline-light bg-dark rounded-0 px-3 py-2 fw-bold ms-2 shadow">
            Tutup
        </button>
    </div>

    <div class="a4-page">
        <div class="kop-surat d-flex align-items-center">
            <img src="{{ $logoUrl }}" alt="Logo" style="width: 72px; height: 72px; object-fit: contain; margin-right: 15px;">
            <div class="teks-tengah flex-grow-1" style="line-height: 1.25;">
                <div style="font-size: 11pt;" class="huruf-tebal huruf-kapital">{{ $kopBaris1 }}</div>
                <div style="font-size: 14.5pt;" class="huruf-tebal huruf-kapital">{{ $kopBaris2 }}</div>
                @if(!empty($kopBaris3))
                    <div style="font-size: 9pt;" class="huruf-tebal huruf-kapital">{{ $kopBaris3 }}</div>
                @endif
                <div style="font-size: 8.5pt;">{{ $kopAlamat }}</div>
            </div>
        </div>
        <div class="kop-surat-2"></div>

        <div class="teks-tengah mb-4 mt-3">
            <div class="huruf-tebal huruf-kapital" style="text-decoration: underline; font-size: 12pt;">
                {{ $suratKeputusan->jenis_surat == 'Surat Keputusan (SK)' ? 'KEPUTUSAN ' . $sebutanPimpinan : $suratKeputusan->jenis_surat }}
            </div>
            @if($suratKeputusan->nomor_surat)
                <div class="fw-bold">Nomor: {{ $suratKeputusan->nomor_surat }}</div>
            @endif
            
            <div class="mt-3">Tentang</div>
            <div class="huruf-tebal huruf-kapital mt-1">{{ $suratKeputusan->judul }}</div>
            <div class="huruf-tebal huruf-kapital mt-3">{{ $sebutanPimpinan }} {{ $namaKampusUpper }}</div>
        </div>

        @if($suratKeputusan->jenis_surat == 'Surat Keputusan (SK)')
            <table class="tabel-diktum">
                @if(!empty($suratKeputusan->menimbang))
                <tr>
                    <td width="18%" class="huruf-tebal">Menimbang</td>
                    <td width="2%">:</td>
                    <td width="80%">
                        <ol type="a" style="margin-top:0; padding-left: 20px; text-align: justify;">
                            @foreach($suratKeputusan->menimbang as $item)
                                <li style="margin-bottom: 5px;">{{ $item }}</li>
                            @endforeach
                        </ol>
                    </td>
                </tr>
                @endif

                @if(!empty($suratKeputusan->mengingat))
                <tr>
                    <td class="huruf-tebal">Mengingat</td>
                    <td>:</td>
                    <td>
                        <ol style="margin-top:0; padding-left: 20px; text-align: justify;">
                            @foreach($suratKeputusan->mengingat as $item)
                                <li style="margin-bottom: 5px;">{{ $item }}</li>
                            @endforeach
                        </ol>
                    </td>
                </tr>
                @endif

                @if(!empty($suratKeputusan->memperhatikan))
                <tr>
                    <td class="huruf-tebal">Memperhatikan</td>
                    <td>:</td>
                    <td>
                        <ol style="margin-top:0; padding-left: 20px; text-align: justify;">
                            @foreach($suratKeputusan->memperhatikan as $item)
                                <li style="list-style-type: none; margin-bottom: 5px;">{{ $loop->iteration }}. {{ $item }}</li>
                            @endforeach
                        </ol>
                    </td>
                </tr>
                @endif
            </table>

            <div class="teks-tengah huruf-tebal mt-4 mb-3" style="letter-spacing: 5px;">MEMUTUSKAN</div>

            <table class="tabel-diktum">
                @if(!empty($suratKeputusan->menetapkan))
                    @php $diktumLabels = ['Pertama', 'Kedua', 'Ketiga', 'Keempat', 'Kelima', 'Keenam', 'Ketujuh', 'Kedelapan']; @endphp
                    <tr>
                        <td width="18%" class="huruf-tebal">Menetapkan</td>
                        <td width="2%">:</td>
                        <td width="80%"></td>
                    </tr>
                    @foreach($suratKeputusan->menetapkan as $index => $item)
                    <tr>
                        <td class="huruf-tebal" style="padding-left: 10px;">{{ $diktumLabels[$index] ?? 'Ke-' . ($index + 1) }}</td>
                        <td>:</td>
                        <td style="text-align: justify; padding-bottom: 5px;">{{ $item }}</td>
                    </tr>
                    @endforeach
                @endif
            </table>
        @else
            <div style="text-align: justify; white-space: pre-line; margin-top: 30px;">
                {{ $suratKeputusan->isi_surat }}
            </div>
        @endif

        <div class="kotak-pengesahan" style="margin-top: 40px; width: 100%;">
            <div style="float: right; width: 260px;">
                <p style="margin: 0;">DITETAPKAN DI : {{ $kotaTtd }}</p>
                <p style="margin: 0; border-bottom: 1px solid black; display: inline-block;">PADA TANGGAL : <span class="huruf-tebal">{{ $suratKeputusan->tanggal_terbit ? \Carbon\Carbon::parse($suratKeputusan->tanggal_terbit)->locale('id')->isoFormat('D MMMM Y') : '......................' }}</span></p>
                
                <p class="huruf-tebal mt-3 mb-5" style="letter-spacing: 1px; text-transform: uppercase;">{{ $suratKeputusan->penandatangan_jabatan ?: ($sebutanPimpinan . ' ' . ($cfg['singkatan_kampus'] ?? '')) }},</p>
                
                <p class="huruf-tebal mt-5 mb-0" style="text-decoration: underline; text-transform: uppercase;">{{ $suratKeputusan->penandatangan_nama ?: ($cfg['nama_rektor'] ?? '') }}</p>
            </div>
            <div style="clear: both;"></div>

            @if(!empty($suratKeputusan->tembusan))
                <div style="margin-top: 30px; font-size: 10pt;">
                    <div class="huruf-tebal">Tembusan disampaikan Kepada Yth.:</div>
                    <ol style="margin-top: 5px; padding-left: 15px;">
                        @foreach($suratKeputusan->tembusan as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ol>
                </div>
            @endif
        </div>
    </div>

    @if($suratKeputusan->dosens->count() > 0 || (!empty($suratKeputusan->panitia_lainnya) && count($suratKeputusan->panitia_lainnya) > 0))
    <div class="a4-page">
        <div style="font-size: 10pt; margin-bottom: 30px;">
            <table width="100%">
                <tr>
                    <td width="30%" valign="top">Lampiran SK Nomor</td>
                    <td width="2%" valign="top">:</td>
                    <td width="68%" valign="top">{{ $suratKeputusan->nomor_surat ?? '- Belum Ada Nomor -' }}</td>
                </tr>
            </table>
        </div>

        <div class="teks-tengah huruf-tebal huruf-kapital mb-5">
            {{ $suratKeputusan->judul }}<br>
            TAHUN AKADEMIK {{ date('Y') }}/{{ date('Y', strtotime('+1 year')) }}
        </div>

        <table width="100%" style="font-size: 11pt; border-collapse: separate; border-spacing: 0 10px;">
            @foreach($suratKeputusan->dosens as $dosen)
                <tr>
                    <td width="30%" class="huruf-tebal huruf-kapital" valign="top">{{ $dosen->pivot->jabatan_dalam_surat }}</td>
                    <td width="2%" valign="top">:</td>
                    <td width="68%" class="huruf-kapital" valign="top">{{ $dosen->nama_lengkap }}</td>
                </tr>
            @endforeach

            @if(!empty($suratKeputusan->panitia_lainnya))
                @foreach($suratKeputusan->panitia_lainnya as $eksternal)
                    <tr>
                        <td width="30%" class="huruf-tebal huruf-kapital" valign="top">{{ $eksternal['jabatan'] }}</td>
                        <td width="2%" valign="top">:</td>
                        <td width="68%" class="huruf-kapital" valign="top">{{ $eksternal['nama'] }}</td>
                    </tr>
                @endforeach
            @endif
        </table>

        <div class="kotak-pengesahan" style="margin-top: 50px; width: 100%;">
            <div style="float: right; width: 260px;">
                <p style="margin: 0;">DITETAPKAN DI : {{ $kotaTtd }}</p>
                <p style="margin: 0; border-bottom: 1px solid black; display: inline-block;">PADA TANGGAL : <span class="huruf-tebal">{{ $suratKeputusan->tanggal_terbit ? \Carbon\Carbon::parse($suratKeputusan->tanggal_terbit)->locale('id')->isoFormat('D MMMM Y') : '......................' }}</span></p>
                
                <p class="huruf-tebal mt-3 mb-5" style="letter-spacing: 1px; text-transform: uppercase;">{{ $suratKeputusan->penandatangan_jabatan ?: ($sebutanPimpinan . ' ' . ($cfg['singkatan_kampus'] ?? '')) }},</p>
                
                <p class="huruf-tebal mt-5 mb-0" style="text-decoration: underline; text-transform: uppercase;">{{ $suratKeputusan->penandatangan_nama ?: ($cfg['nama_rektor'] ?? '') }}</p>
            </div>
            <div style="clear: both;"></div>
        </div>
    </div>
    @endif

</body>
</html>