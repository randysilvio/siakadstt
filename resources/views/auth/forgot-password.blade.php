<x-guest-layout>
    <div class="px-6 py-4">
        <!-- Logo & Header Seragam dengan Login -->
        <div class="flex flex-col items-center mb-6">
            <img src="{{ asset('images/logo.png') }}" 
                 alt="Logo STT GPI PAPUA" 
                 class="w-20 h-20 object-contain mb-3 filter drop-shadow-sm">
            
            <h1 class="text-xl font-bold text-center text-gray-900 tracking-tight uppercase">
                PEMULIHAN KATA SANDI
            </h1>
            
            <p class="text-xs text-teal-700 mt-1 font-bold uppercase tracking-wider text-center">
                SIAKAD STT GPI PAPUA
            </p>
        </div>

        <div class="mb-5 text-xs text-gray-600 leading-relaxed text-center bg-gray-50 p-3 border border-gray-200 rounded-md">
            {{ __('Masukkan alamat email resmi Anda yang terdaftar di sistem. Kami akan mengirimkan tautan khusus untuk mengatur ulang kata sandi Anda.') }}
        </div>

        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form id="forgotForm" method="POST" action="{{ route('password.email') }}" class="space-y-5">
            @csrf

            <div>
                <label for="email" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Alamat Email Terdaftar</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                        </svg>
                    </div>
                    <x-text-input id="email" 
                                  style="padding-left: 40px;"
                                  class="block w-full pl-10 border-gray-300 focus:border-teal-600 focus:ring-teal-600 rounded-md shadow-sm transition duration-200 ease-in-out" 
                                  type="email" name="email" :value="old('email')" required autofocus placeholder="nama@sttgpipapua.ac.id" />
                </div>
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div class="pt-2">
                <button type="submit" id="submitForgotBtn"
                        class="w-full flex justify-center items-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-bold text-white bg-teal-800 hover:bg-black focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-600 transition-all duration-200 ease-in-out uppercase tracking-widest">
                    <span id="forgotBtnText">{{ __('Kirim Tautan Pemulihan') }}</span>
                    <svg id="forgotBtnSpinner" class="animate-spin ml-3 h-5 w-5 text-white hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </button>
            </div>
            
            <div class="text-center mt-4 pt-3 border-t border-gray-200">
                <a href="{{ route('login') }}" class="text-sm text-teal-700 hover:text-teal-900 font-bold transition duration-150">
                    &larr; Kembali ke Halaman Login
                </a>
            </div>
        </form>
    </div>
</x-guest-layout>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('forgotForm');
        const btn = document.getElementById('submitForgotBtn');
        const text = document.getElementById('forgotBtnText');
        const spinner = document.getElementById('forgotBtnSpinner');

        if (form && btn) {
            form.addEventListener('submit', function () {
                btn.disabled = true;
                btn.classList.add('opacity-75', 'cursor-not-allowed');
                text.textContent = 'Mengirim...';
                spinner.classList.remove('hidden');
            });
        }
    });
</script>