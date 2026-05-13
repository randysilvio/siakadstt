<x-guest-layout>
    <div class="px-6 py-4">
        <div class="flex flex-col items-center mb-8">
            <img src="{{ asset('images/logo.png') }}" 
                 alt="Logo STT GPI PAPUA" 
                 class="w-24 h-24 object-contain mb-4 filter drop-shadow-sm">
            
            <h1 class="text-2xl font-bold text-center text-gray-900 tracking-tight uppercase">
                SIAKAD STT GPI PAPUA
            </h1>
            
            <p class="text-sm text-teal-700 mt-2 font-medium italic text-center">
                "Aku mengangkat engkau untuk membangun dan menanam"
            </p>
        </div>

        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}" class="space-y-6">
            @csrf

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Alamat Email Resmi</label>
                <x-text-input id="email" 
                              class="block w-full border-gray-300 focus:border-teal-600 focus:ring-teal-600 rounded-1 shadow-sm" 
                              type="email" name="email" :value="old('email')" required autofocus placeholder="nama@sttgpipapua.ac.id" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div>
                <div class="flex justify-between items-center mb-1">
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">Kata Sandi</label>
                </div>
                <x-text-input id="password" 
                                class="block w-full border-gray-300 focus:border-teal-600 focus:ring-teal-600 rounded-1 shadow-sm"
                                type="password" name="password" required autocomplete="current-password" placeholder="••••••••" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div class="flex items-center justify-between">
                <label for="remember_me" class="inline-flex items-center cursor-pointer">
                    <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-teal-600 shadow-sm focus:ring-teal-600" name="remember">
                    <span class="ms-2 text-sm text-gray-600">Ingat Sesi Saya</span>
                </label>

                @if (Route::has('password.request'))
                    <a class="text-sm text-teal-700 hover:text-teal-900 font-bold decoration-2" href="{{ route('password.request') }}">
                        Lupa Password?
                    </a>
                @endif
            </div>

            <div class="pt-2">
                <button type="submit" 
                        class="w-full flex justify-center py-3 px-4 border border-transparent rounded-1 shadow-sm text-sm font-bold text-white bg-teal-800 hover:bg-black focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 transition duration-150 ease-in-out uppercase tracking-widest">
                    Masuk ke Sistem
                </button>
            </div>
        </form>
        
        <div class="mt-8 text-center border-top pt-4">
            <p class="text-xs text-gray-400 uppercase tracking-tighter">
                &copy; {{ date('Y') }} STT GPI Papua | Biro Administrasi Akademik & Kemahasiswaan
            </p>
        </div>
    </div>
</x-guest-layout>