<section class="bg-white p-6 border-t-4 border-slate-900">
    <header>
        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-900 border-b pb-2">
            {{ __('Pembaruan Kunci Keamanan Akun') }}
        </h2>

        <p class="mt-2 text-xs text-slate-600 font-mono uppercase leading-relaxed text-justify">
            {{ __('Pastikan kombinasi sandi baru Anda memuat string acak, kombinasi angka, dan karakter khusus demi menjaga ketahanan enkripsi pangkalan data.') }}
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-4 space-y-4">
        @csrf
        @method('put')

        <div>
            <x-input-label for="update_password_current_password" :value="__('Kata Sandi Berjalan')" class="text-xs font-bold uppercase text-slate-900" />
            <x-text-input id="update_password_current_password" name="current_password" type="password" class="mt-1 block w-full rounded-none font-mono text-sm py-2 px-3 border-black" autocomplete="current-password" />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-1 font-mono text-xs text-red-600 uppercase" />
        </div>

        <div>
            <x-input-label for="update_password_password" :value="__('Kata Sandi Baru Sasaran')" class="text-xs font-bold uppercase text-slate-900" />
            <x-text-input id="update_password_password" name="password" type="password" class="mt-1 block w-full rounded-none font-mono text-sm py-2 px-3 border-black" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-1 font-mono text-xs text-red-600 uppercase" />
        </div>

        <div>
            <x-input-label for="update_password_password_confirmation" :value="__('Validasi Ulang Sandi Baru')" class="text-xs font-bold uppercase text-slate-900" />
            <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full rounded-none font-mono text-sm py-2 px-3 border-black" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-1 font-mono text-xs text-red-600 uppercase" />
        </div>

        <div class="flex items-center gap-4 pt-3 border-t">
            <x-primary-button class="rounded-none uppercase font-bold text-xs px-5 py-2 shadow-none tracking-widest">{{ __('Simpan Kunci') }}</x-primary-button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-xs font-mono font-bold text-slate-900 uppercase bg-slate-100 py-1 px-3 border-l-2 border-black"
                >{{ __('Sandi Tersimpan Permanen.') }}</p>
            @endif
        </div>
    </form>
</section>