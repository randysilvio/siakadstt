<x-guest-layout>
    <div class="px-6 py-4">
        <!-- Logo & Header -->
        <div class="flex flex-col items-center mb-8">
            <img src="{{ asset('images/logo.png') }}" 
                 alt="Logo STT GPI PAPUA" 
                 class="w-24 h-24 object-contain mb-4 filter drop-shadow-sm transition-transform duration-300 hover:scale-105">
            
            <h1 class="text-2xl font-bold text-center text-gray-900 tracking-tight uppercase">
                SIAKAD STT GPI PAPUA
            </h1>
            
            <p class="text-sm text-teal-700 mt-2 font-medium italic text-center">
                "Aku mengangkat engkau untuk membangun dan menanam"
            </p>
        </div>

        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form id="loginForm" method="POST" action="{{ route('login') }}" class="space-y-6">
            @csrf

            <!-- Kolom Email -->
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Alamat Email Resmi</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <!-- Ikon Email -->
                        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                        </svg>
                    </div>
                    <x-text-input id="email" 
                                  class="block w-full pl-10 border-gray-300 focus:border-teal-600 focus:ring-teal-600 rounded-md shadow-sm transition duration-200 ease-in-out" 
                                  type="email" name="email" :value="old('email')" required autofocus placeholder="nama@sttgpipapua.ac.id" />
                </div>
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <!-- Kolom Kata Sandi -->
            <div>
                <div class="flex justify-between items-center mb-1">
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">Kata Sandi</label>
                </div>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <!-- Ikon Gembok -->
                        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    
                    <x-text-input id="password" 
                                  class="block w-full pl-10 pr-10 border-gray-300 focus:border-teal-600 focus:ring-teal-600 rounded-md shadow-sm transition duration-200 ease-in-out"
                                  type="password" name="password" required autocomplete="current-password" placeholder="••••••••" />
                    
                    <!-- Tombol Toggle Show Password -->
                    <button type="button" id="togglePassword" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-teal-600 focus:outline-none transition duration-150">
                        <!-- Ikon Mata (Terbuka) -->
                        <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <!-- Ikon Mata Dicoret (Tertutup) -->
                        <svg id="eyeSlashIcon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                        </svg>
                    </button>
                </div>
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div class="flex items-center justify-between">
                <label for="remember_me" class="inline-flex items-center cursor-pointer group">
                    <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-teal-600 shadow-sm focus:ring-teal-600 transition duration-150" name="remember">
                    <span class="ms-2 text-sm text-gray-600 group-hover:text-gray-900 transition duration-150">Ingat Sesi Saya</span>
                </label>

                @if (Route::has('password.request'))
                    <a class="text-sm text-teal-700 hover:text-teal-900 font-bold decoration-2 transition duration-150" href="{{ route('password.request') }}">
                        Lupa Password?
                    </a>
                @endif
            </div>

            <!-- Tombol Login dengan Animasi Loading -->
            <div class="pt-2">
                <button type="submit" id="submitBtn"
                        class="w-full flex justify-center items-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-bold text-white bg-teal-800 hover:bg-black focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-600 transition-all duration-200 ease-in-out uppercase tracking-widest group">
                    <span id="btnText">Masuk ke Sistem</span>
                    <!-- Spinner Loading SVG (Awalnya Sembunyi) -->
                    <svg id="btnSpinner" class="animate-spin ml-3 h-5 w-5 text-white hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </button>
            </div>
        </form>
        
        <div class="mt-8 text-center border-t border-gray-200 pt-4">
            <p class="text-xs text-gray-400 uppercase tracking-tighter hover:text-gray-600 transition duration-150">
                &copy; {{ date('Y') }} STT GPI Papua | Biro Administrasi Akademik & Kemahasiswaan
            </p>
        </div>
    </div>
</x-guest-layout>

<!-- JavaScript Interaksi UI -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // 1. Logika Lihat / Sembunyikan Password
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');
        const eyeSlashIcon = document.getElementById('eyeSlashIcon');

        if(togglePassword && passwordInput) {
            togglePassword.addEventListener('click', function () {
                // Mengubah tipe input antara 'password' dan 'text'
                const isPassword = passwordInput.getAttribute('type') === 'password';
                passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
                
                // Mengubah ikon mata
                eyeIcon.classList.toggle('hidden');
                eyeSlashIcon.classList.toggle('hidden');
                
                // Menjaga agar form tetap fokus saat tombol diklik
                passwordInput.focus();
            });
        }

        // 2. Logika Efek Loading Saat Formulir Dikirim
        const loginForm = document.getElementById('loginForm');
        const submitBtn = document.getElementById('submitBtn');
        const btnText = document.getElementById('btnText');
        const btnSpinner = document.getElementById('btnSpinner');

        if(loginForm && submitBtn) {
            loginForm.addEventListener('submit', function () {
                // Mencegah double click / submit berulang
                submitBtn.disabled = true;
                submitBtn.classList.add('opacity-75', 'cursor-not-allowed');
                
                // Merubah teks dan memunculkan spinner
                btnText.textContent = 'Memproses...';
                btnSpinner.classList.remove('hidden');
            });
        }
    });
</script>