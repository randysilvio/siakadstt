<x-guest-layout>
    <div class="px-6 py-4">
        <div class="flex flex-col items-center mb-6">
            <img src="{{ asset('images/logo.png') }}" alt="Logo" class="w-20 h-20 object-contain mb-2">
            <h1 class="text-xl font-bold text-gray-900 text-center">PENERIMAAN MAHASISWA BARU</h1>
            <p class="text-sm text-teal-600 font-medium">STT GPI PAPUA</p>
        </div>

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        @if(!$periodeAktif)
            <div class="text-center py-10">
                <div class="text-gray-500 mb-2">🚫</div>
                <h3 class="text-lg font-bold text-gray-700">Pendaftaran Ditutup</h3>
                <p class="text-sm text-gray-500">Belum ada gelombang pendaftaran yang dibuka saat ini.</p>
                <div class="mt-6">
                    <a href="/" class="text-teal-600 hover:underline">Kembali ke Beranda</a>
                </div>
            </div>
        @else
            <form method="POST" action="{{ route('pmb.register.store') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="pmb_period_id" value="{{ $periodeAktif->id }}">

                <div class="bg-teal-50 border-l-4 border-teal-500 p-3 mb-4">
                    <p class="text-xs text-teal-700 font-bold">GELOMBANG AKTIF:</p>
                    <p class="text-sm text-gray-700">{{ $periodeAktif->nama_gelombang }}</p>
                    <p class="text-xs text-gray-500">Biaya Formulir: Rp {{ number_format($periodeAktif->biaya_pendaftaran, 0, ',', '.') }}</p>
                </div>

                <div>
                    <x-input-label for="name" :value="__('Nama Lengkap Sesuai Ijazah')" />
                    <x-text-input id="name" class="block mt-1 w-full border-gray-300 focus:ring-teal-600" type="text" name="name" :value="old('name')" required autofocus />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="email" :value="__('Email Aktif')" />
                        <x-text-input id="email" class="block mt-1 w-full border-gray-300 focus:ring-teal-600" type="email" name="email" :value="old('email')" required />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="no_hp" :value="__('No WhatsApp')" />
                        <x-text-input id="no_hp" class="block mt-1 w-full border-gray-300 focus:ring-teal-600" type="text" name="no_hp" :value="old('no_hp')" required />
                        <x-input-error :messages="$errors->get('no_hp')" class="mt-2" />
                    </div>
                </div>

                <div>
                    <x-input-label for="password" :value="__('Password')" />
                    <x-text-input id="password" class="block mt-1 w-full border-gray-300 focus:ring-teal-600" type="password" name="password" required autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="password_confirmation" :value="__('Konfirmasi Password')" />
                    <x-text-input id="password_confirmation" class="block mt-1 w-full border-gray-300 focus:ring-teal-600" type="password" name="password_confirmation" required />
                </div>

                <div class="mt-6">
                    <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-bold text-white bg-teal-600 hover:bg-teal-700 uppercase tracking-wider">
                        {{ __('Daftar Sekarang') }}
                    </button>
                </div>

                <div class="mt-4 text-center">
                    <a href="{{ route('login') }}" class="text-sm text-gray-600 hover:text-teal-600">Sudah punya akun? Masuk disini</a>
                </div>
            </form>
        @endif
    </div>
</x-guest-layout>