<x-guest-layout>
    @php
        $cfg     = \App\Models\Pengaturan::allCached();
        $logoUrl = \App\Models\Pengaturan::logoUrl();
    @endphp

    <div class="px-2 py-2">
        <div class="flex flex-col items-center mb-6">
            <img src="{{ $logoUrl }}" alt="Logo" class="w-20 h-20 object-contain mb-3">
            <h1 class="text-xl font-bold text-center text-slate-900 tracking-tight uppercase">
                Pemulihan Kata Sandi
            </h1>
            <p class="text-xs text-slate-500 mt-1 font-bold uppercase tracking-wider text-center">
                SIAKAD {{ $cfg['singkatan_kampus'] ?? 'STT GPI PAPUA' }}
            </p>
        </div>

        <div class="mb-5 text-xs text-slate-600 leading-relaxed text-center bg-slate-50 p-3 border border-slate-200 rounded-md">
            Masukkan alamat email resmi Anda yang terdaftar di sistem. Kami akan mengirimkan tautan khusus untuk mengatur ulang kata sandi Anda.
        </div>

        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form id="forgotForm" method="POST" action="{{ route('password.email') }}" class="space-y-5">
            @csrf

            <div>
                <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Alamat Email Terdaftar</label>
                <x-text-input id="email" class="block w-full border-slate-300 rounded-md shadow-sm text-sm px-3 py-2 border" type="email" name="email" :value="old('email')" required autofocus placeholder="nama@email.ac.id" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div class="pt-1">
                <button type="submit" id="submitForgotBtn" class="w-full flex justify-center items-center py-2.5 px-4 shadow-sm text-xs font-bold uppercase tracking-wider transition btn-auth-primary">
                    <span id="forgotBtnText">Kirim Tautan Pemulihan</span>
                </button>
            </div>
            
            <div class="text-center mt-4 pt-3 border-t border-slate-200">
                <a href="{{ route('login') }}" class="text-xs text-slate-700 hover:underline font-bold uppercase tracking-wider">
                    &larr; Kembali ke Halaman Login
                </a>
            </div>
        </form>
    </div>
</x-guest-layout>