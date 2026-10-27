@extends('layouts.app')

@section('content')
@php
    $c = $cfg ?? \App\Models\Pengaturan::allCached();
@endphp
<div class="container-fluid px-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 mt-3 gap-3">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Pengaturan Sistem & Identitas Institusi</h3>
            <span class="text-muted small uppercase">Konfigurasi Profil Kampus, Struktur Organisasi, Logo Tematik, Watermark, & Tema Warna</span>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('welcome') }}" target="_blank" class="btn btn-sm btn-outline-dark px-3 py-2 uppercase fw-bold small">
                Lihat Halaman Publik
            </a>
            <button type="submit" form="formMasterPengaturan" class="btn btn-sm btn-dark px-4 py-2 uppercase fw-bold small shadow-sm">
                Simpan Pengaturan
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border shadow-sm mb-4 p-3 uppercase small fw-bold" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border shadow-sm mb-4 p-3 uppercase small font-monospace" role="alert">
            <strong class="d-block mb-1">MOHON PERIKSA KEMBALI INPUT ANDA:</strong>
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form id="formMasterPengaturan" action="{{ route('admin.pengaturan.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <ul class="nav nav-tabs mb-4 border-bottom border-dark border-2" id="settingsTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-bold uppercase small py-2 px-3 text-dark" data-bs-toggle="tab" data-bs-target="#tab-identitas" type="button" role="tab">
                    1. Identitas & Pimpinan
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold uppercase small py-2 px-3 text-dark" data-bs-toggle="tab" data-bs-target="#tab-struktur" type="button" role="tab">
                    2. Struktur Yayasan & Kampus
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold uppercase small py-2 px-3 text-dark" data-bs-toggle="tab" data-bs-target="#tab-visual" type="button" role="tab">
                    3. Logo, Event & Watermark
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold uppercase small py-2 px-3 text-dark" data-bs-toggle="tab" data-bs-target="#tab-tema" type="button" role="tab">
                    4. Tema & Warna
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold uppercase small py-2 px-3 text-dark" data-bs-toggle="tab" data-bs-target="#tab-publik" type="button" role="tab">
                    5. Konten Halaman Publik
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold uppercase small py-2 px-3 text-dark" data-bs-toggle="tab" data-bs-target="#tab-akademik" type="button" role="tab">
                    6. Akademik, OBE & Saklar
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold uppercase small py-2 px-3 text-dark" data-bs-toggle="tab" data-bs-target="#tab-kontak" type="button" role="tab">
                    7. Rekening & Kontak
                </button>
            </li>
        </ul>

        <div class="tab-content mb-5">
            {{-- TAB 1: IDENTITAS INSTITUSI & PIMPINAN UTAMA --}}
            <div class="tab-pane fade show active" id="tab-identitas" role="tabpanel">
                <div class="card border-0 shadow-sm border-top border-dark border-4">
                    <div class="card-header bg-dark text-white py-3 uppercase fw-bold small">
                        I. Identitas Resmi Perguruan Tinggi & Pimpinan Tertinggi
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold uppercase text-dark">Nama Lengkap Perguruan Tinggi <span class="text-danger">*</span></label>
                                <input type="text" name="nama_kampus" class="form-control fw-bold" value="{{ old('nama_kampus', $c['nama_kampus']) }}" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold uppercase text-dark">Nama Singkatan / Brand <span class="text-danger">*</span></label>
                                <input type="text" name="singkatan_kampus" class="form-control font-monospace fw-bold uppercase" value="{{ old('singkatan_kampus', $c['singkatan_kampus']) }}" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold uppercase text-dark">Bentuk Institusi</label>
                                <select name="bentuk_institusi" class="form-select uppercase small fw-bold">
                                    @foreach(['Sekolah Tinggi', 'Institut', 'Universitas', 'Politeknik', 'Akademi'] as $bi)
                                        <option value="{{ $bi }}" {{ $c['bentuk_institusi'] == $bi ? 'selected' : '' }}>{{ $bi }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label small fw-bold uppercase text-dark">Sebutan Pimpinan Tertinggi</label>
                                <select name="sebutan_pimpinan" class="form-select uppercase small fw-bold">
                                    @foreach(['Ketua', 'Rektor', 'Direktur'] as $sp)
                                        <option value="{{ $sp }}" {{ $c['sebutan_pimpinan'] == $sp ? 'selected' : '' }}>{{ $sp }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label small fw-bold uppercase text-dark">Nama Lengkap {{ $c['sebutan_pimpinan'] }} (Beserta Gelar) <span class="text-danger">*</span></label>
                                <input type="text" name="nama_rektor" class="form-control fw-bold" value="{{ old('nama_rektor', $c['nama_rektor']) }}" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold uppercase text-dark">Jenis Nomor Induk</label>
                                <select name="label_id_rektor" class="form-select font-monospace fw-bold">
                                    @foreach(['NIDN', 'NUPTK', 'NIP'] as $lbl)
                                        <option value="{{ $lbl }}" {{ $c['label_id_rektor'] == $lbl ? 'selected' : '' }}>{{ $lbl }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold uppercase text-dark">Nomor NIDN / NUPTK</label>
                                <input type="text" name="nidn_rektor" class="form-control font-monospace" value="{{ old('nidn_rektor', $c['nidn_rektor']) }}">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label small fw-bold uppercase text-dark">Kode PT PDDIKTI</label>
                                <input type="text" name="kode_pt_pddikti" class="form-control font-monospace" value="{{ old('kode_pt_pddikti', $c['kode_pt_pddikti']) }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold uppercase text-dark">Peringkat Akreditasi Institusi</label>
                                <select name="akreditasi_institusi" class="form-select uppercase small fw-bold">
                                    @foreach(['Unggul', 'Baik Sekali', 'Baik', 'A', 'B', 'C', 'Terakreditasi'] as $ak)
                                        <option value="{{ $ak }}" {{ $c['akreditasi_institusi'] == $ak ? 'selected' : '' }}>{{ $ak }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold uppercase text-dark">Nomor SK Akreditasi</label>
                                <input type="text" name="no_sk_akreditasi" class="form-control font-monospace" value="{{ old('no_sk_akreditasi', $c['no_sk_akreditasi']) }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold uppercase text-dark">Kota Domisili (Tanda Tangan Surat)</label>
                                <input type="text" name="kota_domisili" class="form-control fw-bold" value="{{ old('kota_domisili', $c['kota_domisili']) }}" placeholder="Contoh: Fakfak">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TAB 2: STRUKTUR ORGANISASI YAYASAN/SINODE, SENAT, & KAMPUS --}}
            <div class="tab-pane fade" id="tab-struktur" role="tabpanel">
                <div class="card border-0 shadow-sm border-top border-dark border-4">
                    <div class="card-header bg-dark text-white py-3 uppercase fw-bold small">
                        II. Struktur Organisasi Badan Penyelenggara (Sinode/Yayasan), Senat Akademik, & Pejabat Struktural
                    </div>
                    <div class="card-body p-4">
                        <h6 class="fw-bold text-dark uppercase small border-bottom pb-2 mb-3">A. Badan Penyelenggara (Sinode / Yayasan Penaung)</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-8">
                                <label class="form-label small fw-bold uppercase text-dark">Nama Resmi Sinode / Yayasan Penyelenggara</label>
                                <input type="text" name="nama_badan_penyelenggara" class="form-control fw-bold" value="{{ $c['nama_badan_penyelenggara'] }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold uppercase text-dark">Label Kategori Badan</label>
                                <input type="text" name="jenis_badan_penyelenggara" class="form-control" value="{{ $c['jenis_badan_penyelenggara'] }}" placeholder="Sinode / Yayasan">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold uppercase text-dark">Ketua Umum Sinode / Ketua Yayasan</label>
                                <input type="text" name="ketua_yayasan" class="form-control" value="{{ $c['ketua_yayasan'] }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold uppercase text-dark">Sekretaris Umum Sinode / Yayasan</label>
                                <input type="text" name="sekretaris_yayasan" class="form-control" value="{{ $c['sekretaris_yayasan'] }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold uppercase text-dark">Bendahara / Pengawas Yayasan</label>
                                <input type="text" name="bendahara_yayasan" class="form-control" value="{{ $c['bendahara_yayasan'] }}">
                            </div>
                        </div>

                        <h6 class="fw-bold text-dark uppercase small border-bottom pb-2 mb-3">B. Senat Akademik & Unsur Pimpinan Eksekutif Kampus</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold uppercase text-dark">Ketua Senat Akademik</label>
                                <input type="text" name="ketua_senat" class="form-control" value="{{ $c['ketua_senat'] }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold uppercase text-dark">Sekretaris Senat Akademik</label>
                                <input type="text" name="sekretaris_senat" class="form-control" value="{{ $c['sekretaris_senat'] }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-bold uppercase text-dark">Jabatan Wakil Pimpinan I</label>
                                <input type="text" name="wakil_pimpinan_1" class="form-control form-control-sm bg-light mb-1 fw-bold" value="{{ $c['wakil_pimpinan_1'] }}">
                                <input type="text" name="nama_wakil_1" class="form-control" value="{{ $c['nama_wakil_1'] }}" placeholder="Nama Pejabat Wakil I...">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold uppercase text-dark">Jabatan Wakil Pimpinan II</label>
                                <input type="text" name="wakil_pimpinan_2" class="form-control form-control-sm bg-light mb-1 fw-bold" value="{{ $c['wakil_pimpinan_2'] }}">
                                <input type="text" name="nama_wakil_2" class="form-control" value="{{ $c['nama_wakil_2'] }}" placeholder="Nama Pejabat Wakil II...">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold uppercase text-dark">Jabatan Wakil Pimpinan III</label>
                                <input type="text" name="wakil_pimpinan_3" class="form-control form-control-sm bg-light mb-1 fw-bold" value="{{ $c['wakil_pimpinan_3'] }}">
                                <input type="text" name="nama_wakil_3" class="form-control" value="{{ $c['nama_wakil_3'] }}" placeholder="Nama Pejabat Wakil III...">
                            </div>
                        </div>

                        <h6 class="fw-bold text-dark uppercase small border-bottom pb-2 mb-3">C. Pimpinan Lembaga, Unit Penunjang, & Bagan Struktur Resmi</h6>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label small fw-bold uppercase text-dark">Kepala Penjaminan Mutu (UPM)</label>
                                <input type="text" name="nama_kepala_upm" class="form-control" value="{{ $c['nama_kepala_upm'] }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold uppercase text-dark">Kepala LPPM (Riset & PkM)</label>
                                <input type="text" name="nama_kepala_lppm" class="form-control" value="{{ $c['nama_kepala_lppm'] }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold uppercase text-dark">Kepala BAAK / Administrasi</label>
                                <input type="text" name="nama_kepala_baak" class="form-control" value="{{ $c['nama_kepala_baak'] }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold uppercase text-dark">Kepala UPT Perpustakaan</label>
                                <input type="text" name="nama_kepala_perpus" class="form-control" value="{{ $c['nama_kepala_perpus'] }}">
                            </div>

                            <div class="col-12 mt-3">
                                <label class="form-label small fw-bold uppercase text-dark">Unggah Gambar Bagan Struktur Organisasi Resmi (Opsional - PNG/JPG Maks 5MB)</label>
                                <input type="file" name="bagan_struktur_file" class="form-control" accept="image/*">
                                @if(!empty($c['bagan_struktur_path']))
                                    <div class="mt-2 small font-monospace text-dark">
                                        Bagan Struktur Aktif: <a href="{{ asset('storage/' . $c['bagan_struktur_path']) }}" target="_blank" class="fw-bold text-dark">Lihat Gambar Bagan</a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TAB 3: LOGO INTI, LOGO EVENT/DIES NATALIS, FOTO MAJELIS/POP-OUT, WATERMARK, & KOP PDF --}}
            <div class="tab-pane fade" id="tab-visual" role="tabpanel">
                <div class="card border-0 shadow-sm border-top border-dark border-4">
                    <div class="card-header bg-dark text-white py-3 uppercase fw-bold small">
                        III. Manajemen Logo Inti, Logo Tematik Kegiatan (Dies Natalis), Foto Pimpinan Pop-Out, & Watermark
                    </div>
                    <div class="card-body p-4">
                        <h6 class="fw-bold text-dark uppercase small border-bottom pb-2 mb-3">A. Logo Inti Institusi, Logo Event/Dies Natalis (Khusus Halaman Publik), & Foto Pimpinan</h6>
                        <div class="row g-4 mb-4">
                            {{-- 1. Logo Inti --}}
                            <div class="col-md-3">
                                <div class="border p-3 bg-light h-100 text-center">
                                    <label class="form-label small fw-bold uppercase text-dark d-block">1. Logo Inti Kampus</label>
                                    <img src="{{ \App\Models\Pengaturan::logoUrl() }}" alt="Logo" class="my-2 border bg-white p-2" style="height: 75px; object-fit: contain;">
                                    <input type="file" name="logo_file" class="form-control form-control-sm mt-2" accept="image/png,image/jpeg,image/webp">
                                    <small class="text-muted d-block mt-1" style="font-size: 10px;">Logo permanen untuk SIAKAD Internal, Login, & Kop Surat PDF.</small>
                                </div>
                            </div>

                            {{-- 2. Logo Tematik / Event Kampus (Dies Natalis, dll) --}}
                            <div class="col-md-3">
                                <div class="border p-3 bg-light h-100 text-center">
                                    <label class="form-label small fw-bold uppercase text-dark d-block">2. Logo Event / Dies Natalis</label>
                                    @if(\App\Models\Pengaturan::eventLogoUrl())
                                        <img src="{{ \App\Models\Pengaturan::eventLogoUrl() }}" alt="Logo Event" class="my-2 border bg-white p-2" style="height: 75px; object-fit: contain;">
                                    @else
                                        <div class="my-2 py-4 text-muted small font-monospace">[TIDAK ADA EVENT AKTIF]</div>
                                    @endif
                                    <input type="file" name="logo_event_file" class="form-control form-control-sm mt-2" accept="image/png,image/jpeg,image/webp">
                                    <input type="text" name="label_event_publik" class="form-control form-control-sm mt-2" value="{{ $c['label_event_publik'] ?? '' }}" placeholder="Teks: Dies Natalis ke-25...">
                                    @if(!empty($c['logo_event_path']))
                                        <div class="form-check text-start mt-1">
                                            <input class="form-check-input" type="checkbox" name="hapus_logo_event" value="1" id="chkHapusEvent">
                                            <label class="form-check-label small text-danger fw-bold" for="chkHapusEvent" style="font-size: 10px;">Nonaktifkan Logo Event</label>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- 3. Foto Pimpinan (PNG Transparan untuk Efek Majalah Pop-Out) --}}
                            <div class="col-md-3">
                                <div class="border p-3 bg-light h-100 text-center">
                                    <label class="form-label small fw-bold uppercase text-dark d-block">3. Foto {{ $c['sebutan_pimpinan'] }} (PNG)</label>
                                    @if(!empty($c['foto_pimpinan_path']))
                                        <img src="{{ asset('storage/' . $c['foto_pimpinan_path']) }}" alt="Pimpinan" class="my-2 border bg-white p-1" style="height: 75px; object-fit: contain;">
                                    @else
                                        <div class="my-2 py-4 text-muted small font-monospace">[BELUM ADA FOTO]</div>
                                    @endif
                                    <input type="file" name="foto_pimpinan_file" class="form-control form-control-sm mt-2" accept="image/png,image/jpeg,image/webp">
                                    <small class="text-muted d-block mt-1" style="font-size: 10px;">Gunakan PNG transparan agar tampil keluar bingkai (*Magazine Pop-Out*) di Beranda.</small>
                                </div>
                            </div>

                            {{-- 4. Favicon --}}
                            <div class="col-md-3">
                                <div class="border p-3 bg-light h-100 text-center">
                                    <label class="form-label small fw-bold uppercase text-dark d-block">4. Ikon Browser (Favicon)</label>
                                    <img src="{{ !empty($c['favicon_path']) ? asset('storage/' . $c['favicon_path']) : \App\Models\Pengaturan::logoUrl() }}" alt="Favicon" class="my-2 border bg-white p-2" style="height: 60px; object-fit: contain;">
                                    <input type="file" name="favicon_file" class="form-control form-control-sm mt-2" accept="image/png,image/x-icon,image/jpeg,image/webp">
                                    <small class="text-muted d-block mt-1" style="font-size: 10px;">Ikon pada tab browser pengunjung.</small>
                                </div>
                            </div>
                        </div>

                        <h6 class="fw-bold text-dark uppercase small border-bottom pb-2 mb-3">B. Watermark Eksklusif (Hanya pada Sambutan Pimpinan, 1 Kartu Highlight Utama, Banner, & Footer)</h6>
                        <div class="row g-4 mb-4">
                            <div class="col-md-5">
                                <div class="border p-3 bg-light h-100">
                                    <label class="form-label small fw-bold uppercase text-dark d-block">5. Ornamen / Lambang Watermark Fokus</label>
                                    <div class="d-flex align-items-center gap-3 my-2">
                                        <div class="border bg-white p-2 d-flex align-items-center justify-content-center" style="width: 68px; height: 68px;">
                                            <img src="{{ \App\Models\Pengaturan::cardWatermarkUrl() }}" alt="Watermark" style="max-width: 52px; max-height: 52px; filter: grayscale(100%) contrast(120%); opacity: 0.4;">
                                        </div>
                                        <div class="small text-muted" style="font-size: 11px;">
                                            Ditampilkan secara eksklusif sebagai siluet bayangan monokrom hanya pada Kartu Sambutan Pimpinan dan 1 Kartu Statistik Utama.
                                        </div>
                                    </div>
                                    <input type="file" name="watermark_card_file" class="form-control form-control-sm mt-2" accept="image/png,image/jpeg,image/webp">
                                    @if(!empty($c['watermark_card_path']))
                                        <div class="form-check mt-2">
                                            <input class="form-check-input" type="checkbox" name="hapus_watermark_card" value="1" id="chkHapusWmCard">
                                            <label class="form-check-label small text-danger fw-bold" for="chkHapusWmCard" style="font-size: 11px;">Gunakan Lambang Kampus Default</label>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="border p-3 bg-light h-100">
                                    <label class="form-label small fw-bold uppercase text-dark d-block">6. Motif Batik Papua (Banner & Footer)</label>
                                    <div class="d-flex align-items-center gap-3 my-2">
                                        <div class="border bg-dark p-2 d-flex align-items-center justify-content-center" style="width: 68px; height: 68px;">
                                            <img src="{{ \App\Models\Pengaturan::sectionWatermarkUrl() }}" alt="Watermark Seksi" style="max-width: 52px; max-height: 52px; filter: grayscale(100%); opacity: 0.45;">
                                        </div>
                                        <div class="small text-muted" style="font-size: 11px;">
                                            Ornamen bayangan budaya pada latar belakang header halaman dalam dan footer.
                                        </div>
                                    </div>
                                    <input type="file" name="watermark_section_file" class="form-control form-control-sm mt-2" accept="image/png,image/jpeg,image/webp">
                                    @if(!empty($c['watermark_section_path']))
                                        <div class="form-check mt-2">
                                            <input class="form-check-input" type="checkbox" name="hapus_watermark_section" value="1" id="chkHapusWmSec">
                                            <label class="form-check-label small text-danger fw-bold" for="chkHapusWmSec" style="font-size: 11px;">Reset ke Ukiran Papua Bawaan</label>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="border p-3 bg-light h-100">
                                    <label class="form-label small fw-bold uppercase text-dark d-block">Ketebalan Watermark</label>
                                    <select name="watermark_opacity" class="form-select form-select-sm font-monospace fw-bold mt-2">
                                        @foreach(['0.04' => 'Sangat Halus (4%)', '0.06' => 'Standar Elegan (6%)', '0.09' => 'Sedang (9%)', '0.12' => 'Tegas (12%)'] as $opVal => $opLabel)
                                            <option value="{{ $opVal }}" {{ ($c['watermark_opacity'] ?? '0.06') == $opVal ? 'selected' : '' }}>{{ $opLabel }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <h6 class="fw-bold text-dark uppercase small border-bottom pb-2 mb-3">C. Teks Header Kop Surat Resmi (Cetakan PDF)</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold uppercase text-dark">Teks Kop Baris 1 (Sinode / Yayasan Penaung)</label>
                                <input type="text" name="kop_baris_1" class="form-control uppercase fw-bold" value="{{ $c['kop_baris_1'] }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold uppercase text-dark">Teks Kop Baris 2 (Nama Utama Perguruan Tinggi)</label>
                                <input type="text" name="kop_baris_2" class="form-control uppercase fw-bold" value="{{ $c['kop_baris_2'] }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold uppercase text-dark">Teks Kop Baris 3 (Sub-Header / Status Akreditasi)</label>
                                <input type="text" name="kop_baris_3" class="form-control uppercase" value="{{ $c['kop_baris_3'] }}">
                            </div>
                            <div class="col-md-8">
                                <label class="form-label small fw-bold uppercase text-dark">Teks Alamat & Kontak pada Kop Surat PDF</label>
                                <input type="text" name="kop_alamat" class="form-control" value="{{ $c['kop_alamat'] }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TAB 4: THEME ENGINE --}}
            <div class="tab-pane fade" id="tab-tema" role="tabpanel">
                <div class="card border-0 shadow-sm border-top border-dark border-4">
                    <div class="card-header bg-dark text-white py-3 uppercase fw-bold small">
                        IV. Kendali Tema Visual, Skema Warna Kustom, & Gaya Sudut Komponen
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-4 align-items-end">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold uppercase text-dark">Pilih Preset Skema Warna Kampus</label>
                                <select name="theme_preset" id="themePresetSelect" class="form-select uppercase small fw-bold">
                                    <option value="monochrome" data-primary="#1e293b" data-accent="#334155" {{ $c['theme_preset'] == 'monochrome' ? 'selected' : '' }}>1. Academic Slate Monochrome (Abu Tua & Hitam Bersih)</option>
                                    <option value="royal_blue" data-primary="#0f2942" data-accent="#1e3a8a" {{ $c['theme_preset'] == 'royal_blue' ? 'selected' : '' }}>2. University Navy Blue (Biru Dongker Akademik)</option>
                                    <option value="emerald_teal" data-primary="#064e3b" data-accent="#115e59" {{ $c['theme_preset'] == 'emerald_teal' ? 'selected' : '' }}>3. Papua Deep Emerald (Hijau Tua Klasik)</option>
                                    <option value="crimson_maroon" data-primary="#7f1d1d" data-accent="#991b1b" {{ $c['theme_preset'] == 'crimson_maroon' ? 'selected' : '' }}>4. Academic Crimson Maroon (Merah Marun Tua)</option>
                                    <option value="imperial_purple" data-primary="#3b0764" data-accent="#581c87" {{ $c['theme_preset'] == 'imperial_purple' ? 'selected' : '' }}>5. Imperial Deep Purple (Ungu Tua Institusi)</option>
                                    <option value="custom" {{ $c['theme_preset'] == 'custom' ? 'selected' : '' }}>6. Warna Kustom Bebas (Sesuai Pilihan Warna di Bawah)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold uppercase text-dark">Gaya Sudut Kartu & Tombol (Border Radius)</label>
                                <select name="theme_border_radius" id="themeRadiusSelect" class="form-select uppercase small fw-bold">
                                    <option value="10px" {{ ($c['theme_border_radius'] ?? '10px') == '10px' ? 'selected' : '' }}>Modern Proporsional (10px Soft Rounded)</option>
                                    <option value="6px" {{ $c['theme_border_radius'] == '6px' ? 'selected' : '' }}>Semi-Tegas (6px Subtle Rounded)</option>
                                    <option value="0px" {{ $c['theme_border_radius'] == '0px' ? 'selected' : '' }}>Kaku Presisi (0px Square)</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold uppercase text-dark">Warna Utama Kustom (Primary Color)</label>
                                <div class="input-group">
                                    <input type="color" name="theme_primary_color" id="inputPrimaryColor" class="form-control form-control-color" style="max-width: 70px;" value="{{ $c['theme_primary_color'] }}">
                                    <input type="text" id="textPrimaryColor" class="form-control font-monospace uppercase fw-bold" value="{{ $c['theme_primary_color'] }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold uppercase text-dark">Warna Sekunder / Aksen Kustom (Accent Color)</label>
                                <div class="input-group">
                                    <input type="color" name="theme_accent_color" id="inputAccentColor" class="form-control form-control-color" style="max-width: 70px;" value="{{ $c['theme_accent_color'] }}">
                                    <input type="text" id="textAccentColor" class="form-control font-monospace uppercase fw-bold" value="{{ $c['theme_accent_color'] }}" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TAB 5: KONTEN PORTAL PUBLIK --}}
            <div class="tab-pane fade" id="tab-publik" role="tabpanel">
                <div class="card border-0 shadow-sm border-top border-dark border-4">
                    <div class="card-header bg-dark text-white py-3 uppercase fw-bold small">
                        V. Konten Halaman Publik (Sambutan Pimpinan, Sejarah, Visi & Misi)
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label small fw-bold uppercase text-dark">Slogan / Motto Utama di Beranda</label>
                                <input type="text" name="motto_kampus" class="form-control" value="{{ $c['motto_kampus'] }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold uppercase text-dark">Tautan Video Profil YouTube (Opsional)</label>
                                <input type="url" name="video_profil_url" class="form-control font-monospace" value="{{ $c['video_profil_url'] }}" placeholder="https://youtube.com/watch?v=...">
                            </div>

                            <div class="col-12">
                                <label class="form-label small fw-bold uppercase text-dark">Isi Sambutan {{ $c['sebutan_pimpinan'] }} di Halaman Depan & Profil</label>
                                <textarea name="isi_sambutan" rows="3" class="form-control">{{ $c['isi_sambutan'] }}</textarea>
                            </div>

                            <div class="col-12">
                                <label class="form-label small fw-bold uppercase text-dark">Sejarah Singkat Perguruan Tinggi</label>
                                <textarea name="sejarah_kampus" rows="3" class="form-control">{{ $c['sejarah_kampus'] }}</textarea>
                            </div>

                            <div class="col-md-5">
                                <label class="form-label small fw-bold uppercase text-dark">Visi Perguruan Tinggi</label>
                                <textarea name="visi_kampus" rows="5" class="form-control">{{ $c['visi_kampus'] }}</textarea>
                            </div>
                            <div class="col-md-7">
                                <label class="form-label small fw-bold uppercase text-dark">Misi Perguruan Tinggi (Pisahkan setiap poin dengan baris baru / Enter)</label>
                                <textarea name="misi_kampus" rows="5" class="form-control">{{ $c['misi_kampus'] }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TAB 6: AKADEMIK, BOBOT OBE & SAKLAR --}}
            <div class="tab-pane fade" id="tab-akademik" role="tabpanel">
                <div class="card border-0 shadow-sm border-top border-dark border-4">
                    <div class="card-header bg-dark text-white py-3 uppercase fw-bold small">
                        VI. Parameter Operasional Akademik, Bobot Penilaian OBE, & Saklar Skala Kampus
                    </div>
                    <div class="card-body p-4">
                        <h6 class="fw-bold text-dark uppercase small border-bottom pb-2 mb-3">A. Saklar Operasional KRS, Struktur Fakultas, & Istilah Pembimbing</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-3">
                                <label class="form-label small fw-bold uppercase text-dark">Status Periode Pengisian KRS</label>
                                <select name="status_krs" class="form-select uppercase small fw-bold">
                                    <option value="buka" {{ $c['status_krs'] == 'buka' ? 'selected' : '' }}>BUKA (MAHASISWA BISA ISI KRS)</option>
                                    <option value="tutup" {{ $c['status_krs'] == 'tutup' ? 'selected' : '' }}>TUTUP (KUNCI PENGISIAN KRS)</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold uppercase text-dark">Mode Struktur Fakultas</label>
                                <select name="fitur_fakultas_aktif" class="form-select uppercase small fw-bold">
                                    <option value="0" {{ $c['fitur_fakultas_aktif'] == '0' ? 'selected' : '' }}>Sekolah Tinggi (Langsung Prodi)</option>
                                    <option value="1" {{ $c['fitur_fakultas_aktif'] == '1' ? 'selected' : '' }}>Universitas (Multi-Fakultas)</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold uppercase text-dark">Sebutan Jabatan Pembimbing</label>
                                <select name="istilah_pembimbing" class="form-select uppercase small fw-bold">
                                    <option value="Dosen Tutor" {{ $c['istilah_pembimbing'] == 'Dosen Tutor' ? 'selected' : '' }}>Dosen Tutor</option>
                                    <option value="Dosen Wali" {{ $c['istilah_pembimbing'] == 'Dosen Wali' ? 'selected' : '' }}>Dosen Wali</option>
                                    <option value="Pembimbing Akademik" {{ $c['istilah_pembimbing'] == 'Pembimbing Akademik' ? 'selected' : '' }}>Pembimbing Akademik (PA)</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold uppercase text-dark">Sebutan Menu Bimbingan KRS</label>
                                <select name="istilah_perwalian" class="form-select uppercase small fw-bold">
                                    <option value="Tutorship" {{ ($c['istilah_perwalian'] ?? 'Tutorship') == 'Tutorship' ? 'selected' : '' }}>Tutorship</option>
                                    <option value="Perwalian" {{ ($c['istilah_perwalian'] ?? '') == 'Perwalian' ? 'selected' : '' }}>Perwalian</option>
                                    <option value="Bimbingan PA" {{ ($c['istilah_perwalian'] ?? '') == 'Bimbingan PA' ? 'selected' : '' }}>Bimbingan PA</option>
                                </select>
                            </div>
                        </div>

                        <h6 class="fw-bold text-dark uppercase small border-bottom pb-2 mb-3">B. Bobot Persentase Default Komponen Buku Nilai OBE (Total Disarankan 100%)</h6>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label small fw-bold uppercase text-dark">Bobot Kehadiran (%)</label>
                                <input type="number" name="bobot_hadir_default" min="0" max="100" class="form-control font-monospace fw-bold" value="{{ $c['bobot_hadir_default'] ?? 10 }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold uppercase text-dark">Bobot Tugas Terstruktur (%)</label>
                                <input type="number" name="bobot_tugas_default" min="0" max="100" class="form-control font-monospace fw-bold" value="{{ $c['bobot_tugas_default'] ?? 20 }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold uppercase text-dark">Bobot Ujian Tengah Semester / UTS (%)</label>
                                <input type="number" name="bobot_uts_default" min="0" max="100" class="form-control font-monospace fw-bold" value="{{ $c['bobot_uts_default'] ?? 30 }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold uppercase text-dark">Bobot Ujian Akhir Semester / UAS (%)</label>
                                <input type="number" name="bobot_uas_default" min="0" max="100" class="form-control font-monospace fw-bold" value="{{ $c['bobot_uas_default'] ?? 40 }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TAB 7: REKENING & KONTAK --}}
            <div class="tab-pane fade" id="tab-kontak" role="tabpanel">
                <div class="card border-0 shadow-sm border-top border-dark border-4">
                    <div class="card-header bg-dark text-white py-3 uppercase fw-bold small">
                        VII. Rekening Bank Pembayaran Resmi, Kontak Admisi, & Media Sosial
                    </div>
                    <div class="card-body p-4">
                        <h6 class="fw-bold text-dark uppercase small border-bottom pb-2 mb-3">A. Informasi Rekening Bank Kampus</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold uppercase text-dark">Nama Bank Resmi</label>
                                <input type="text" name="nama_bank_kampus" class="form-control fw-bold" value="{{ $c['nama_bank_kampus'] }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold uppercase text-dark">Nomor Rekening</label>
                                <input type="text" name="no_rekening_kampus" class="form-control font-monospace fw-bold" value="{{ $c['no_rekening_kampus'] }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold uppercase text-dark">Atas Nama Rekening</label>
                                <input type="text" name="atas_nama_rekening" class="form-control uppercase fw-bold" value="{{ $c['atas_nama_rekening'] }}">
                            </div>
                        </div>

                        <h6 class="fw-bold text-dark uppercase small border-bottom pb-2 mb-3">B. Alamat Kampus, Kontak Humas/PMB, & Tautan Media Sosial</h6>
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label small fw-bold uppercase text-dark">Alamat Lengkap Kampus</label>
                                <input type="text" name="alamat_kampus" class="form-control" value="{{ $c['alamat_kampus'] }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold uppercase text-dark">Nomor Telepon Kantor</label>
                                <input type="text" name="telepon_kampus" class="form-control font-monospace" value="{{ $c['telepon_kampus'] }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold uppercase text-dark">Nomor WhatsApp Layanan PMB (Format 628...)</label>
                                <input type="text" name="whatsapp_admisi" class="form-control font-monospace" value="{{ $c['whatsapp_admisi'] }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold uppercase text-dark">Email Resmi Institusi</label>
                                <input type="email" name="email_kampus" class="form-control font-monospace" value="{{ $c['email_kampus'] }}">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label small fw-bold uppercase text-dark">Link Facebook</label>
                                <input type="url" name="facebook_url" class="form-control font-monospace" value="{{ $c['facebook_url'] }}" placeholder="https://facebook.com/...">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold uppercase text-dark">Link Instagram</label>
                                <input type="url" name="instagram_url" class="form-control font-monospace" value="{{ $c['instagram_url'] }}" placeholder="https://instagram.com/...">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold uppercase text-dark">Link YouTube Channel</label>
                                <input type="url" name="youtube_url" class="form-control font-monospace" value="{{ $c['youtube_url'] }}" placeholder="https://youtube.com/...">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold uppercase text-dark">Link TikTok</label>
                                <input type="url" name="tiktok_url" class="form-control font-monospace" value="{{ $c['tiktok_url'] }}" placeholder="https://tiktok.com/...">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end mb-5">
            <button type="submit" class="btn btn-dark px-5 py-2 uppercase fw-bold small shadow-sm">
                Simpan Seluruh Konfigurasi
            </button>
        </div>
    </form>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const storageKey = 'siakad_active_settings_tab';
        const savedTab = localStorage.getItem(storageKey);
        if (savedTab) {
            const trigger = document.querySelector('#settingsTabs button[data-bs-target="' + savedTab + '"]');
            if (trigger) {
                new bootstrap.Tab(trigger).show();
            }
        }
        document.querySelectorAll('#settingsTabs button[data-bs-toggle="tab"]').forEach(btn => {
            btn.addEventListener('shown.bs.tab', function (e) {
                localStorage.setItem(storageKey, e.target.getAttribute('data-bs-target'));
            });
        });

        const presetSelect = document.getElementById('themePresetSelect');
        const inputPrimary = document.getElementById('inputPrimaryColor');
        const textPrimary  = document.getElementById('textPrimaryColor');
        const inputAccent  = document.getElementById('inputAccentColor');
        const textAccent   = document.getElementById('textAccentColor');

        if (presetSelect) {
            presetSelect.addEventListener('change', function() {
                const opt = this.options[this.selectedIndex];
                if (opt.dataset.primary) {
                    inputPrimary.value = opt.dataset.primary;
                    textPrimary.value = opt.dataset.primary;
                }
                if (opt.dataset.accent) {
                    inputAccent.value = opt.dataset.accent;
                    textAccent.value = opt.dataset.accent;
                }
            });
        }

        if (inputPrimary) {
            inputPrimary.addEventListener('input', function() {
                textPrimary.value = this.value;
                if (presetSelect) presetSelect.value = 'custom';
            });
        }

        if (inputAccent) {
            inputAccent.addEventListener('input', function() {
                textAccent.value = this.value;
                if (presetSelect) presetSelect.value = 'custom';
            });
        }
    });
</script>
@endsection