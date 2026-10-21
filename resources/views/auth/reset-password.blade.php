<x-guest-layout>
    <div class="px-6 py-4">
        <!-- Logo & Header Seragam -->
        <div class="flex flex-col items-center mb-6">
            <img src="{{ asset('images/logo.png') }}" 
                 alt="Logo STT GPI PAPUA" 
                 class="w-20 h-20 object-contain mb-3 filter drop-shadow-sm">
            
            <h1 class="text-xl font-bold text-center text-gray-900 tracking-tight uppercase">
                ATUR ULANG KATA SANDI
            </h1>
            
            <p class="text-xs text-teal-700 mt-1 font-bold uppercase tracking-wider text-center">
                SIAKAD STT GPI PAPUA
            </p>
        </div>

        <form id="resetPasswordForm" method="POST" action="{{ route('password.store') }}" class="space-y-5">
            @csrf

            <!-- Password Reset Token -->
            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <!-- Email Address -->
            <div>
                <label for="email" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Alamat Email</label>
                <x-text-input id="email" class="block w-full border-gray-300 bg-gray-50 focus:border-teal-600 focus:ring-teal-600 rounded-md shadow-sm" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <!-- Password Baru -->
            <div>
                <label for="password" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Kata Sandi Baru</label>
                <div class="relative">
                    <x-text-input id="password" style="padding-right: 40px;" class="block w-full pr-10 border-gray-300 focus:border-teal-600 focus:ring-teal-600 rounded-md shadow-sm" type="password" name="password" required autocomplete="new-password" placeholder="Minimal 8 karakter" />
                    <button type="button" id="toggleNewPassword" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-teal-600 focus:outline-none">
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </button>
                </div>
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <!-- Konfirmasi Password Baru -->
            <div>
                <label for="password_confirmation" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Konfirmasi Kata Sandi Baru</label>
                <x-text-input id="password_confirmation" class="block w-full border-gray-300 focus:border-teal-600 focus:ring-teal-600 rounded-md shadow-sm"
                              type="password"
                              name="password_confirmation" required autocomplete="new-password" placeholder="Ulangi kata sandi baru" />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>

            <div class="pt-2">
                <button type="submit" id="submitResetBtn"
                        class="w-full flex justify-center items-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-bold text-white bg-teal-800 hover:bg-black focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-600 transition-all duration-200 ease-in-out uppercase tracking-widest">
                    <span id="resetBtnText">{{ __('Simpan Kata Sandi Baru') }}</span>
                    <svg id="resetBtnSpinner" class="animate-spin ml-3 h-5 w-5 text-white hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </button>
            </div>
        </form>
    </div>
</x-guest-layout>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toggleBtn = document.getElementById('toggleNewPassword');
        const passInput = document.getElementById('password');
        const confirmInput = document.getElementById('password_confirmation');

        if (toggleBtn && passInput) {
            toggleBtn.addEventListener('click', function () {
                const isPass = passInput.getAttribute('type') === 'password';
                passInput.setAttribute('type', isPass ? 'text' : 'password');
                if (confirmInput) {
                    confirmInput.setAttribute('type', isPass ? 'text' : 'password');
                }
            });
        }

        const form = document.getElementById('resetPasswordForm');
        const btn = document.getElementById('submitResetBtn');
        const text = document.getElementById('resetBtnText');
        const spinner = document.getElementById('resetBtnSpinner');

        if (form && btn) {
            form.addEventListener('submit', function () {
                btn.disabled = true;
                btn.classList.add('opacity-75', 'cursor-not-allowed');
                text.textContent = 'Memproses...';
                spinner.classList.remove('hidden');
            });
        }
    });
</script>