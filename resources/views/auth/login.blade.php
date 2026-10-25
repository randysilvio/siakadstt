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

    <div class="px-6 py-4">
        <div class="flex flex-col items-center mb-7">
            <img src="{{ $logoUrl }}" alt="Logo" class="w-20 h-20 object-contain mb-3">
            <h1 class="text-xl font-bold text-center text-gray-900 tracking-tight uppercase">
                SIAKAD {{ $cfg['singkatan_kampus'] ?? 'STT GPI PAPUA' }}
            </h1>
            <p class="text-xs text-gray-500 mt-1 text-center">
                {{ $cfg['nama_kampus'] ?? '' }}
            </p>
        </div>

        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form id="loginForm" method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Alamat Email</label>
                <x-text-input id="email" class="block w-full border-gray-300 rounded-md shadow-sm text-sm" type="email" name="email" :value="old('email')" required autofocus placeholder="nama@email.ac.id" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Kata Sandi</label>
                <div class="relative">
                    <x-text-input id="password" class="block w-full pr-10 border-gray-300 rounded-md shadow-sm text-sm" type="password" name="password" required autocomplete="current-password" placeholder="••••••••" />
                    <button type="button" id="togglePassword" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-700">
                        <span class="text-xs font-mono uppercase" id="eyeLabel">Lihat</span>
                    </button>
                </div>
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div class="flex items-center justify-between">
                <label for="remember_me" class="inline-flex items-center cursor-pointer">
                    <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-gray-900 shadow-sm" name="remember">
                    <span class="ms-2 text-xs text-gray-600">Ingat Sesi Saya</span>
                </label>

                @if (Route::has('password.request'))
                    <a class="text-xs text-gray-700 hover:underline font-bold" href="{{ route('password.request') }}">
                        Lupa Kata Sandi?
                    </a>
                @endif
            </div>

            <div class="pt-1">
                <button type="submit" id="submitBtn" class="w-full flex justify-center items-center py-2.5 px-4 rounded-md shadow-sm text-xs font-bold uppercase tracking-wider transition btn-auth-primary">
                    <span id="btnText">Masuk ke Sistem</span>
                </button>
            </div>
        </form>
        
        <div class="mt-7 text-center border-t border-gray-200 pt-4">
            <p class="text-[11px] text-gray-400 uppercase">
                &copy; {{ date('Y') }} {{ $cfg['singkatan_kampus'] ?? 'STT GPI Papua' }}
            </p>
        </div>
    </div>
</x-guest-layout>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const eyeLabel = document.getElementById('eyeLabel');

        if (togglePassword && passwordInput) {
            togglePassword.addEventListener('click', function () {
                const isPassword = passwordInput.getAttribute('type') === 'password';
                passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
                if (eyeLabel) eyeLabel.textContent = isPassword ? 'Tutup' : 'Lihat';
            });
        }
    });
</script>