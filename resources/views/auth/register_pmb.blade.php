<x-guest-layout>
    @php
        $cfg     = \App\Models\Pengaturan::allCached();
        $theme   = \App\Models\Pengaturan::themeColors();
        $logoUrl = \App\Models\Pengaturan::logoUrl();
    @endphp
    <style>
        .btn-auth-primary { background-color: {{ $theme['primary'] }}; color: #ffffff; }
        .btn-auth-primary:hover { background-color: {{ $theme['accent'] }}; color: #ffffff; }
    </style>

    <div class="px-2 py-2">
        <div class="flex flex-col items-center mb-6">
            <img src="{{ $logoUrl }}" alt="Logo" class="w-20 h-20 object-contain mb-3">
            <h1 class="text-xl font-bold text-gray-900 text-center tracking-tight uppercase">Penerimaan Mahasiswa Baru</h1>
            <p class="text-xs text-gray-500 font-bold uppercase tracking-wider mt-1">{{ $cfg['singkatan_kampus'] ?? 'STT GPI PAPUA' }}</p>
        </div>

        @if(session('error'))
            <div class="bg-red-50 border-l-4 border-red-600 text-red-700 px-4 py-3 text-xs font-bold uppercase mb-4">
                {{ session('error') }}
            </div>
        @endif

        @if(!$periodeAktif)
            <div class="text-center py-8 bg-gray-50 border border-gray-200 p-6 rounded-md">
                <h3 class="text-base font-bold text-gray-800 uppercase mb-1">Pendaftaran Sedang Ditutup</h3>
                <p class="text-xs text-gray-500 leading-relaxed">Saat ini belum ada gelombang pendaftaran mahasiswa baru yang dibuka.</p>
                <div class="mt-6">
                    <a href="{{ url('/') }}" class="inline-block btn-auth-primary text-xs font-bold uppercase tracking-wider py-2.5 px-6 rounded-md transition">
                        Kembali ke Beranda
                    </a>
                </div>
            </div>
        @else
            <form id="pmbRegisterForm" method="POST" action="{{ route('pmb.register.store') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="pmb_period_id" value="{{ $periodeAktif->id }}">

                <div class="bg-gray-100 border-l-4 border-gray-800 p-3 mb-4">
                    <p class="text-[11px] text-gray-800 font-bold uppercase tracking-wider">GELOMBANG AKTIF: {{ $periodeAktif->nama_gelombang }}</p>
                    <p class="text-xs text-gray-600 font-semibold mt-0.5">Biaya Pendaftaran: Rp {{ number_format($periodeAktif->biaya_pendaftaran, 0, ',', '.') }}</p>
                </div>

                <div>
                    <label for="name" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Nama Lengkap Sesuai Ijazah <span class="text-red-600">*</span></label>
                    <x-text-input id="name" class="block w-full border-gray-300 rounded-md shadow-sm text-sm" type="text" name="name" :value="old('name')" required autofocus />
                    <x-input-error :messages="$errors->get('name')" class="mt-1" />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label for="email" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Email Aktif <span class="text-red-600">*</span></label>
                        <x-text-input id="email" class="block w-full border-gray-300 rounded-md shadow-sm text-sm" type="email" name="email" :value="old('email')" required />
                        <x-input-error :messages="$errors->get('email')" class="mt-1" />
                    </div>
                    <div>
                        <label for="no_hp" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">No. WhatsApp <span class="text-red-600">*</span></label>
                        <x-text-input id="no_hp" class="block w-full border-gray-300 rounded-md shadow-sm text-sm" type="text" name="no_hp" :value="old('no_hp')" required />
                        <x-input-error :messages="$errors->get('no_hp')" class="mt-1" />
                    </div>
                </div>

                <div>
                    <label for="pilihan_prodi_1_id" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Pilihan Program Studi <span class="text-red-600">*</span></label>
                    <select id="pilihan_prodi_1_id" name="pilihan_prodi_1_id" class="block w-full border-gray-300 rounded-md shadow-sm text-sm" required>
                        <option value="" disabled {{ old('pilihan_prodi_1_id') ? '' : 'selected' }}>-- Pilih Program Studi --</option>
                        @foreach($prodis as $prodi)
                            <option value="{{ $prodi->id }}" {{ old('pilihan_prodi_1_id') == $prodi->id ? 'selected' : '' }}>
                                {{ $prodi->nama_prodi }}
                            </option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('pilihan_prodi_1_id')" class="mt-1" />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label for="password" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Kata Sandi <span class="text-red-600">*</span></label>
                        <x-text-input id="password" class="block w-full border-gray-300 rounded-md shadow-sm text-sm" type="password" name="password" required autocomplete="new-password" />
                        <x-input-error :messages="$errors->get('password')" class="mt-1" />
                    </div>
                    <div>
                        <label for="password_confirmation" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Ulangi Sandi <span class="text-red-600">*</span></label>
                        <x-text-input id="password_confirmation" class="block w-full border-gray-300 rounded-md shadow-sm text-sm" type="password" name="password_confirmation" required />
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" id="pmbSubmitBtn" class="w-full flex justify-center items-center py-2.5 px-4 rounded-md shadow-sm text-xs font-bold uppercase tracking-wider transition btn-auth-primary">
                        <span id="pmbBtnText">Daftar Sekarang</span>
                    </button>
                </div>

                <div class="mt-4 pt-3 border-t border-gray-200 text-center">
                    <a href="{{ route('login') }}" class="text-xs font-bold text-gray-700 hover:underline uppercase tracking-wider">
                        Sudah punya akun? Masuk di sini &rarr;
                    </a>
                </div>
            </form>
        @endif
    </div>
</x-guest-layout>