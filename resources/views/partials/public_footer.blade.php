@php
    $fCfg       = \App\Models\Pengaturan::allCached();
    $fLogo      = \App\Models\Pengaturan::logoUrl();
    $fSingkatan = $fCfg['singkatan_kampus'] ?? 'STT GPI PAPUA';
    $fNama      = $fCfg['nama_kampus'] ?? 'Sekolah Tinggi Teologi GPI Papua';
@endphp

<footer id="kontak" class="wm-banner text-white pt-14 pb-8 border-t border-white/10 mt-auto">
    <div class="container mx-auto px-4 lg:px-8 max-w-7xl relative z-10">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10 mb-10">
            <div>
                <div class="flex items-center space-x-3 mb-4">
                    <img src="{{ $fLogo }}" alt="Logo {{ $fSingkatan }}" class="h-11 w-11 object-contain bg-white p-1 rounded-lg">
                    <div>
                        <span class="font-bold text-base tracking-wide block leading-none uppercase">{{ $fSingkatan }}</span>
                        <span class="text-[10px] text-white/70 font-mono uppercase block mt-1">{{ $fCfg['kota_domisili'] ?? 'Fakfak' }} - Papua Barat</span>
                    </div>
                </div>
                <p class="text-white/75 text-xs leading-relaxed mb-4">
                    {{ $fCfg['motto_kampus'] ?? '' }}
                </p>
                <div class="flex space-x-2">
                    @if(!empty($fCfg['facebook_url']))
                        <a href="{{ $fCfg['facebook_url'] }}" target="_blank" class="h-8 w-8 rounded-lg bg-white/10 flex items-center justify-center text-white/80 hover:bg-white hover:text-slate-900 transition"><i class="fa-brands fa-facebook-f text-xs"></i></a>
                    @endif
                    @if(!empty($fCfg['instagram_url']))
                        <a href="{{ $fCfg['instagram_url'] }}" target="_blank" class="h-8 w-8 rounded-lg bg-white/10 flex items-center justify-center text-white/80 hover:bg-white hover:text-slate-900 transition"><i class="fa-brands fa-instagram text-xs"></i></a>
                    @endif
                    @if(!empty($fCfg['youtube_url']))
                        <a href="{{ $fCfg['youtube_url'] }}" target="_blank" class="h-8 w-8 rounded-lg bg-white/10 flex items-center justify-center text-white/80 hover:bg-white hover:text-slate-900 transition"><i class="fa-brands fa-youtube text-xs"></i></a>
                    @endif
                    @if(!empty($fCfg['tiktok_url']))
                        <a href="{{ $fCfg['tiktok_url'] }}" target="_blank" class="h-8 w-8 rounded-lg bg-white/10 flex items-center justify-center text-white/80 hover:bg-white hover:text-slate-900 transition"><i class="fa-brands fa-tiktok text-xs"></i></a>
                    @endif
                </div>
            </div>

            <div>
                <h3 class="font-bold text-xs uppercase tracking-wider text-white mb-4 border-b border-white/15 pb-2">Profil & Tata Kelola</h3>
                <ul class="space-y-2 text-xs text-white/75">
                    <li><a href="{{ route('publik.profil') }}#sejarah" class="hover:text-white transition">Sejarah & Visi-Misi</a></li>
                    <li><a href="{{ route('publik.profil') }}#struktur-yayasan" class="hover:text-white transition">Struktur {{ $fCfg['jenis_badan_penyelenggara'] ?? 'Sinode / Yayasan' }}</a></li>
                    <li><a href="{{ route('publik.profil') }}#struktur-kampus" class="hover:text-white transition">Senat & Pimpinan Kampus</a></li>
                    <li><a href="{{ route('dosen.public.index') }}" class="hover:text-white transition">Direktori Dosen</a></li>
                </ul>
            </div>

            <div>
                <h3 class="font-bold text-xs uppercase tracking-wider text-white mb-4 border-b border-white/15 pb-2">Layanan Akademik</h3>
                <ul class="space-y-2 text-xs text-white/75">
                    <li><a href="{{ route('pmb.register') }}" class="hover:text-white transition">Pendaftaran Mahasiswa Baru</a></li>
                    <li><a href="{{ route('login') }}" class="hover:text-white transition">Portal SIAKAD & E-Learning</a></li>
                    <li><a href="{{ route('publik.perpustakaan') }}" class="hover:text-white transition">Katalog Perpustakaan (OPAC)</a></li>
                    <li><a href="{{ route('publik.riset') }}" class="hover:text-white transition">Penelitian & Pengabdian</a></li>
                    <li><a href="{{ route('dokumen.publik') }}" class="hover:text-white transition">Dokumen Resmi</a></li>
                </ul>
            </div>

            <div>
                <h3 class="font-bold text-xs uppercase tracking-wider text-white mb-4 border-b border-white/15 pb-2">Kontak & Alamat</h3>
                <div class="space-y-2 text-xs text-white/75">
                    <p>{{ $fCfg['alamat_kampus'] ?? 'Jl. Jenderal Sudirman, Fakfak, Papua Barat' }}</p>
                    <p class="font-mono">Telp: {{ $fCfg['telepon_kampus'] ?? '(0956) 22114' }}</p>
                    <p class="font-mono">Email: {{ $fCfg['email_kampus'] ?? 'info@sttgpipapua.ac.id' }}</p>
                </div>
            </div>
        </div>

        <div class="border-t border-white/15 pt-6 flex flex-col sm:flex-row justify-between items-center text-[11px] text-white/60 gap-2">
            <p>&copy; {{ date('Y') }} {{ $fNama }} ({{ $fSingkatan }}).</p>
            <p class="font-mono text-[10px]">Kode PT: {{ $fCfg['kode_pt_pddikti'] ?? '-' }} | Akreditasi: {{ $fCfg['akreditasi_institusi'] ?? '-' }}</p>
        </div>
    </div>
</footer>