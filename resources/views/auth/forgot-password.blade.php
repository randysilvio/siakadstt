<x-guest-layout>
    <div class="px-6 py-4">
        <div class="mb-6 text-sm text-gray-600 leading-relaxed text-center">
            {{ __('Lupa password? Masukkan alamat email Anda yang terdaftar di sistem. Kami akan mengirimkan tautan pemulihan untuk mengatur ulang kata sandi Anda.') }}
        </div>

        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Email Terdaftar</label>
                <x-text-input id="email" class="block w-full border-gray-300 focus:border-teal-600 focus:ring-teal-600 rounded-1" type="email" name="email" :value="old('email')" required autofocus />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div class="flex items-center justify-center mt-6">
                <x-primary-button class="w-full justify-center py-3 bg-teal-800 rounded-1 uppercase tracking-widest">
                    {{ __('Kirim Tautan Pemulihan') }}
                </x-primary-button>
            </div>
            
            <div class="text-center mt-4">
                <a href="{{ route('login') }}" class="text-sm text-teal-700 font-bold">Kembali ke Halaman Login</a>
            </div>
        </form>
    </div>
</x-guest-layout>