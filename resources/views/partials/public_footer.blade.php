@php
    $fCfg  = \App\Models\Pengaturan::allCached();
    $fLogo = \App\Models\Pengaturan::logoUrl();
    $fSingkatan = $fCfg['singkatan_kampus'] ?? 'STT GPI PAPUA';
    $fNama      = $fCfg['nama_kampus'] ?? 'Sekolah Tinggi Teologi GPI Papua';
@endphp

<footer id="kontak" class="bg-slate-950 text-white pt-16 pb-10 border-t-4 border-teal-600 mt-auto">
    <div class="container mx-auto px-4 lg:px-8 max-w-7xl">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10 mb-12">
            {{-- Kolom 1: Identitas & Sosial Media --}}
            <div>
                <div class="flex items-center space-x-3 mb-5">
                    <img src="{{ $fLogo }}" alt="Logo {{ $fSingkatan }}" class="h-12 w-12 object-contain bg-white p-1 rounded-lg">
                    <div>
                        <span class="font-heading font-black text-lg tracking-wider block leading-none uppercase">{{ $fSingkatan }}</span>
                        <span class="text-[10px] text-teal-400 font-bold uppercase tracking-widest block mt-1">{{ $fCfg['kota_domisili'] ?? 'Fakfak' }} - Papua Barat</span>
                    </div>
                </div>
                <p class="text-slate-400 text-xs leading-relaxed mb-5">
                    {{ $fCfg['motto_kampus'] ?? 'Pendidikan tinggi teologi dan pendidikan agama Kristen berintegritas tinggi di Tanah Papua.' }}
                </p>
                <div class="flex space-x-2.5">
                    @if(!empty($fCfg['facebook_url']))
                        <a href="{{ $fCfg['facebook_url'] }}" target="_blank" class="h-9 w-9 rounded-full bg-slate-900 flex items-center justify-center text-slate-400 hover:bg-teal-600 hover:text-white transition"><i class="fa-brands fa-facebook-f text-xs"></i></a>
                    @endif
                    @if(!empty($fCfg['instagram_url']))
                        <a href="{{ $fCfg['instagram_url'] }}" target="_blank" class="h-9 w-9 rounded-full bg-slate-900 flex items-center justify-center text-slate-400 hover:bg-teal-600 hover:text-white transition"><i class="fa-brands fa-instagram text-xs"></i></a>
                    @endif
                    @if(!empty($fCfg['youtube_url']))
                        <a href="{{ $fCfg['youtube_url'] }}" target="_blank" class="h-9 w-9 rounded-full bg-slate-900 flex items-center justify-center text-slate-400 hover:bg-red-600 hover:text-white transition"><i class="fa-brands fa-youtube text-xs"></i></a>
                    @endif
                    @if(!empty($fCfg['tiktok_url']))
                        <a href="{{ $fCfg['tiktok_url'] }}" target="_blank" class="h-9 w-9 rounded-full bg-slate-900 flex items-center justify-center text-slate-400 hover:bg-teal-600 hover:text-white transition"><i class="fa-brands fa-tiktok text-xs"></i></a>
                    @endif
                </div>
            </div>

            {{-- Kolom 2: Tentang & Struktur Institusi --}}
            <div>
                <h3 class="font-bold text-xs uppercase tracking-widest text-teal-400 mb-5">Profil & Tata Kelola</h3>
                <ul class="space-y-2.5 text-xs text-slate-300">
                    <li><a href="{{ route('publik.profil') }}#sejarah" class="hover:text-teal-400 transition">Sejarah & Visi-Misi</a></li>
                    <li><a href="{{ route('publik.profil') }}#struktur-yayasan" class="hover:text-teal-400 transition">Struktur {{ $fCfg['jenis_badan_penyelenggara'] ?? 'Sinode / Yayasan' }}</a></li>
                    <li><a href="{{ route('publik.profil') }}#struktur-kampus" class="hover:text-teal-400 transition">Senat & Pimpinan Institusi</a></li>
                    <li><a href="{{ route('dosen.public.index') }}" class="hover:text-teal-400 transition">Direktori Dosen & Peneliti</a></li>
                    <li><a href="{{ route('publik.riset') }}" class="hover:text-teal-400 transition">Riset, Publikasi & Pengabdian</a></li>
                </ul>
            </div>

            {{-- Kolom 3: Ekosistem Digital Kampus --}}
            <div>
                <h3 class="font-bold text-xs uppercase tracking-widest text-teal-400 mb-5">Layanan Akademik Digital</h3>
                <ul class="space-y-2.5 text-xs text-slate-300">
                    <li><a href="{{ route('pmb.register') }}" class="hover:text-teal-400 transition">Admisi Mahasiswa Baru (PMB)</a></li>
                    <li><a href="{{ route('login') }}" class="hover:text-teal-400 transition">Portal SIAKAD & Verum LMS</a></li>
                    <li><a href="{{ route('publik.perpustakaan') }}" class="hover:text-teal-400 transition">Katalog Perpustakaan (OPAC)</a></li>
                    <li><a href="{{ route('dokumen.publik') }}" class="hover:text-teal-400 transition">Dokumen Publik & Kalender</a></li>
                    <li><a href="{{ route('berita.index') }}" class="hover:text-teal-400 transition">Warta & Pengumuman Kampus</a></li>
                </ul>
            </div>

            {{-- Kolom 4: Kontak & Lokasi Kampus --}}
            <div>
                <h3 class="font-bold text-xs uppercase tracking-widest text-teal-400 mb-5">Kantor Admisi & Humas</h3>
                <ul class="space-y-3 text-xs text-slate-300">
                    <li class="flex items-start">
                        <i class="fa-solid fa-location-dot mt-1 mr-2.5 text-teal-500"></i>
                        <span>{{ $fCfg['alamat_kampus'] ?? 'Jl. Jenderal Sudirman, Fakfak, Papua Barat' }}</span>
                    </li>
                    <li class="flex items-center">
                        <i class="fa-solid fa-phone mr-2.5 text-teal-500"></i>
                        <span>{{ $fCfg['telepon_kampus'] ?? '(0956) 22114' }}</span>
                    </li>
                    <li class="flex items-center">
                        <i class="fa-regular fa-envelope mr-2.5 text-teal-500"></i>
                        <span>{{ $fCfg['email_kampus'] ?? 'info@sttgpipapua.ac.id' }}</span>
                    </li>
                    @if(!empty($fCfg['whatsapp_admisi']))
                        <li class="pt-2">
                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $fCfg['whatsapp_admisi']) }}" target="_blank" class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold px-4 py-2 rounded-lg text-xs transition shadow">
                                <i class="fa-brands fa-whatsapp text-sm"></i> Chat WhatsApp Admisi
                            </a>
                        </li>
                    @endif
                </ul>
            </div>
        </div>

        <div class="border-t border-slate-800/80 pt-6 flex flex-col sm:flex-row justify-between items-center text-[11px] text-slate-500 gap-3">
            <p>&copy; {{ date('Y') }} <strong>{{ $fNama }}</strong> ({{ $fSingkatan }}). Hak Cipta Dilindungi.</p>
            <p class="font-mono text-[10px]">Kode PT PDDIKTI: {{ $fCfg['kode_pt_pddikti'] ?? '-' }} | Status Akreditasi: {{ $fCfg['akreditasi_institusi'] ?? 'Terakreditasi' }}</p>
        </div>
    </div>
</footer>