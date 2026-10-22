{{-- ================= FOOTER KAMPUS TERPUSAT (4 KOLOM SERAGAM) ================= --}}
<footer id="kontak" class="bg-slate-950 text-white pt-16 pb-8 border-t-2 border-brand-600 mt-auto">
    <div class="container mx-auto px-6 max-w-6xl">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10 mb-12">
            {{-- Kolom 1: Identitas & Sosial Media --}}
            <div>
                <div class="flex items-center space-x-3 mb-5">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo STT GPI Papua" class="h-10 w-10 object-contain">
                    <div>
                        <span class="font-heading font-extrabold text-lg tracking-wider block leading-none">STT GPI PAPUA</span>
                        <span class="text-[10px] text-brand-400 font-bold uppercase tracking-widest block mt-1">Fakfak - Papua Barat</span>
                    </div>
                </div>
                <p class="text-slate-400 text-xs leading-relaxed mb-5 font-light">
                    Pendidikan tinggi teologi berintegritas tinggi guna mempersiapkan pelayan Tuhan yang setia, berilmu, dan relevan di Tanah Papua.
                </p>
                <div class="flex space-x-3">
                    <a href="#" class="h-8 w-8 rounded-full bg-slate-900 flex items-center justify-center text-slate-400 hover:bg-brand-600 hover:text-white transition" aria-label="Facebook"><i class="fa-brands fa-facebook-f text-xs"></i></a>
                    <a href="#" class="h-8 w-8 rounded-full bg-slate-900 flex items-center justify-center text-slate-400 hover:bg-brand-600 hover:text-white transition" aria-label="Instagram"><i class="fa-brands fa-instagram text-xs"></i></a>
                    <a href="#" class="h-8 w-8 rounded-full bg-slate-900 flex items-center justify-center text-slate-400 hover:bg-brand-600 hover:text-white transition" aria-label="YouTube"><i class="fa-brands fa-youtube text-xs"></i></a>
                </div>
            </div>

            {{-- Kolom 2: Kantor & Narahubung --}}
            <div>
                <h3 class="font-bold text-sm uppercase tracking-wider text-brand-400 mb-5">Kantor Kampus</h3>
                <ul class="space-y-3 text-xs text-slate-300 font-light">
                    <li class="flex items-start">
                        <i class="fa-solid fa-location-dot mt-0.5 mr-2.5 text-brand-500"></i>
                        <span>Jl. Jenderal Sudirman / Jl. Pattimura No. 1, Fakfak, Papua Barat</span>
                    </li>
                    <li class="flex items-center">
                        <i class="fa-solid fa-envelope mr-2.5 text-brand-500"></i>
                        <span>info@sttgpipapua.ac.id</span>
                    </li>
                    <li class="flex items-center">
                        <i class="fa-solid fa-phone mr-2.5 text-brand-500"></i>
                        <span>(0956) 22252</span>
                    </li>
                </ul>
            </div>

            {{-- Kolom 3: Akses Informasi Publik --}}
            <div>
                <h3 class="font-bold text-sm uppercase tracking-wider text-brand-400 mb-5">Akses Informasi</h3>
                <ul class="space-y-2.5 text-xs text-slate-300 font-light">
                    <li><a href="{{ url('/#prodi') }}" class="hover:text-brand-400 transition">Program Studi Unggulan</a></li>
                    <li><a href="{{ route('dosen.public.index') }}" class="hover:text-brand-400 transition">Direktori Dosen Pengajar</a></li>
                    <li><a href="{{ route('berita.index') }}" class="hover:text-brand-400 transition">Berita & Pengumuman</a></li>
                    <li><a href="{{ route('dokumen.publik') }}" class="hover:text-brand-400 transition">Laci Arsip & Dokumen Publik</a></li>
                </ul>
            </div>

            {{-- Kolom 4: Layanan Portal Akademik --}}
            <div>
                <h3 class="font-bold text-sm uppercase tracking-wider text-brand-400 mb-5">Portal Akademik</h3>
                <ul class="space-y-2.5 text-xs text-slate-300 font-light">
                    <li>
                        <a href="{{ route('login') }}" class="inline-flex items-center hover:text-brand-400 transition">
                            <i class="fa-solid fa-chevron-right text-[9px] text-brand-500 mr-2"></i>Masuk Portal SIAKAD
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('pmb.register') }}" class="inline-flex items-center hover:text-brand-400 transition">
                            <i class="fa-solid fa-chevron-right text-[9px] text-brand-500 mr-2"></i>Pendaftaran Mahasiswa Baru (PMB)
                        </a>
                    </li>
                    <li>
                        <a href="{{ url('/#berita') }}" class="inline-flex items-center hover:text-brand-400 transition">
                            <i class="fa-solid fa-chevron-right text-[9px] text-brand-500 mr-2"></i>Kalender & Agenda Akademik
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="border-t border-slate-900 pt-6 flex flex-col sm:flex-row justify-between items-center text-slate-500 text-xs font-light gap-2">
            <p>&copy; {{ date('Y') }} <strong>STT GPI Papua</strong>. Seluruh Hak Cipta Dilindungi Undang-Undang.</p>
            <p class="text-[11px] text-slate-600">Biro Administrasi Akademik & Kemahasiswaan (BAAK)</p>
        </div>
    </div>
</footer>