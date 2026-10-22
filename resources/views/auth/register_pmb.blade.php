<x-guest-layout>
    <div class="px-2 py-2">
        <div class="flex flex-col items-center mb-6">
            <img src="{{ asset('images/logo.png') }}" alt="Logo STT GPI Papua" class="w-20 h-20 object-contain mb-3 filter drop-shadow-sm">
            <h1 class="text-xl font-bold text-gray-900 text-center tracking-tight uppercase">PENERIMAAN MAHASISWA BARU</h1>
            <p class="text-xs text-teal-700 font-bold uppercase tracking-wider mt-1">PORTAL ADMISI STT GPI PAPUA</p>
        </div>

        @if(session('error'))
            <div class="bg-red-50 border-l-4 border-red-600 text-red-700 px-4 py-3 text-xs font-bold uppercase mb-4" role="alert">
                {{ session('error') }}
            </div>
        @endif

        @if(!$periodeAktif)
            <div class="text-center py-8 bg-gray-50 border border-gray-200 p-6">
                <h3 class="text-base font-bold text-gray-800 uppercase mb-1">Pendaftaran Sedang Ditutup</h3>
                <p class="text-xs text-gray-500 leading-relaxed">Saat ini belum ada gelombang pendaftaran mahasiswa baru yang dibuka.</p>
                <div class="mt-6">
                    <a href="{{ url('/') }}" class="inline-block bg-teal-800 hover:bg-black text-white text-xs font-bold uppercase tracking-widest py-2.5 px-6 rounded-md transition">
                        Kembali ke Beranda
                    </a>
                </div>
            </div>
        @else
            <form id="pmbRegisterForm" method="POST" action="{{ route('pmb.register.store') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="pmb_period_id" value="{{ $periodeAktif->id }}">

                <div class="bg-teal-50 border-l-4 border-teal-700 p-3 mb-4">
                    <p class="text-[11px] text-teal-800 font-bold uppercase tracking-wider">GELOMBANG AKTIF: {{ $periodeAktif->nama_gelombang }}</p>
                    <p class="text-xs text-gray-700 font-semibold mt-0.5">Biaya Formulir: Rp {{ number_format($periodeAktif->biaya_pendaftaran, 0, ',', '.') }}</p>
                </div>

                <div>
                    <label for="name" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Nama Lengkap Sesuai Ijazah <span class="text-red-600">*</span></label>
                    <x-text-input id="name" class="block w-full border-gray-300 focus:border-teal-600 focus:ring-teal-600 rounded-md shadow-sm" type="text" name="name" :value="old('name')" required autofocus placeholder="Nama lengkap..." />
                    <x-input-error :messages="$errors->get('name')" class="mt-1" />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label for="email" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Email Aktif <span class="text-red-600">*</span></label>
                        <x-text-input id="email" class="block w-full border-gray-300 focus:border-teal-600 focus:ring-teal-600 rounded-md shadow-sm" type="email" name="email" :value="old('email')" required placeholder="email@contoh.com" />
                        <x-input-error :messages="$errors->get('email')" class="mt-1" />
                    </div>
                    <div>
                        <label for="no_hp" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">No. WhatsApp <span class="text-red-600">*</span></label>
                        <x-text-input id="no_hp" class="block w-full border-gray-300 focus:border-teal-600 focus:ring-teal-600 rounded-md shadow-sm" type="text" name="no_hp" :value="old('no_hp')" required placeholder="0812..." />
                        <x-input-error :messages="$errors->get('no_hp')" class="mt-1" />
                    </div>
                </div>

                <div>
                    <label for="pilihan_prodi_1_id" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Pilihan Program Studi Utama <span class="text-red-600">*</span></label>
                    <select id="pilihan_prodi_1_id" name="pilihan_prodi_1_id" class="block w-full border-gray-300 focus:border-teal-600 focus:ring-teal-600 rounded-md shadow-sm text-sm" required>
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
                        <x-text-input id="password" class="block w-full border-gray-300 focus:border-teal-600 focus:ring-teal-600 rounded-md shadow-sm" type="password" name="password" required autocomplete="new-password" placeholder="Min. 8 karakter" />
                        <x-input-error :messages="$errors->get('password')" class="mt-1" />
                    </div>
                    <div>
                        <label for="password_confirmation" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Ulangi Sandi <span class="text-red-600">*</span></label>
                        <x-text-input id="password_confirmation" class="block w-full border-gray-300 focus:border-teal-600 focus:ring-teal-600 rounded-md shadow-sm" type="password" name="password_confirmation" required placeholder="Ulangi kata sandi" />
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" id="pmbSubmitBtn" class="w-full flex justify-center items-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-bold text-white bg-teal-800 hover:bg-black focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-600 transition-all duration-200 uppercase tracking-widest">
                        <span id="pmbBtnText">{{ __('Daftar Sekarang') }}</span>
                    </button>
                </div>

                <div class="mt-4 pt-3 border-t border-gray-200 text-center">
                    <a href="{{ route('login') }}" class="text-xs font-bold text-teal-700 hover:text-teal-900 uppercase tracking-wider">
                        Sudah punya akun pendaftaran? Masuk di sini &rarr;
                    </a>
                </div>
            </form>
        @endif
    </div>
</x-guest-layout>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('pmbRegisterForm');
        const btn = document.getElementById('pmbSubmitBtn');
        const text = document.getElementById('pmbBtnText');
        if (form && btn) {
            form.addEventListener('submit', function () {
                btn.disabled = true;
                btn.classList.add('opacity-75', 'cursor-not-allowed');
                text.textContent = 'Memproses Pendaftaran...';
            });
        }
    });
</script>