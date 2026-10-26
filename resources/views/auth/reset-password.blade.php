<x-guest-layout>
    @php
        $cfg     = \App\Models\Pengaturan::allCached();
        $logoUrl = \App\Models\Pengaturan::logoUrl();
    @endphp

    <div class="px-2 py-2">
        <div class="flex flex-col items-center mb-6">
            <img src="{{ $logoUrl }}" alt="Logo" class="w-20 h-20 object-contain mb-3">
            <h1 class="text-xl font-bold text-center text-slate-900 tracking-tight uppercase">
                Atur Ulang Kata Sandi
            </h1>
            <p class="text-xs text-slate-500 mt-1 font-bold uppercase tracking-wider text-center">
                SIAKAD {{ $cfg['singkatan_kampus'] ?? 'STT GPI PAPUA' }}
            </p>
        </div>

        <form id="resetPasswordForm" method="POST" action="{{ route('password.store') }}" class="space-y-4">
            @csrf

            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <div>
                <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Alamat Email</label>
                <x-text-input id="email" class="block w-full border-slate-300 bg-slate-50 rounded-md shadow-sm text-sm px-3 py-2 border" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div>
                <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Kata Sandi Baru</label>
                <x-text-input id="password" class="block w-full border-slate-300 rounded-md shadow-sm text-sm px-3 py-2 border" type="password" name="password" required autocomplete="new-password" placeholder="Minimal 8 karakter" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div>
                <label for="password_confirmation" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Konfirmasi Kata Sandi Baru</label>
                <x-text-input id="password_confirmation" class="block w-full border-slate-300 rounded-md shadow-sm text-sm px-3 py-2 border" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Ulangi kata sandi baru" />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>

            <div class="pt-2">
                <button type="submit" class="w-full flex justify-center items-center py-2.5 px-4 shadow-sm text-xs font-bold uppercase tracking-wider transition btn-auth-primary">
                    Simpan Kata Sandi Baru
                </button>
            </div>
        </form>
    </div>
</x-guest-layout>