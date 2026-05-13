<section class="space-y-6 bg-white p-6 border-t-4 border-red-700">
    <header>
        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-900 border-b pb-2">
            {{ __('Pencabutan Hak Akses & Akun Permanen') }}
        </h2>

        <p class="mt-2 text-xs text-slate-600 font-mono uppercase leading-relaxed text-justify">
            {{ __('Peringatan: Seluruh portofolio akademik, rekam jejak presensi, serta tautan repositori yang merujuk pada identitas ini akan dicabut secara permanen. Pastikan Anda telah mengunduh arsip yang relevan sebelum melanjutkan.') }}
        </p>
    </header>

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
        class="rounded-none uppercase font-bold text-xs px-5 py-2.5 shadow-none tracking-widest"
    >{{ __('Eksekusi Penghapusan Akun') }}</x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6 bg-white rounded-none border-t-4 border-black">
            @csrf
            @method('delete')

            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-900 border-b pb-2">
                {{ __('Verifikasi Tindakan Kritis Sistem') }}
            </h2>

            <p class="mt-2 text-xs text-slate-600 font-mono uppercase leading-relaxed text-justify">
                {{ __('Tindakan ini tidak dapat dipulihkan. Silakan masukkan kata sandi Anda sebagai otorisasi akhir untuk menyetujui penghapusan akun beserta seluruh simpul datanya.') }}
            </p>

            <div class="mt-4">
                <x-input-label for="password" value="{{ __('Kata Sandi Otorisasi') }}" class="sr-only" />

                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1 block w-full rounded-none font-mono text-sm py-2 px-3 border-black"
                    placeholder="{{ __('Ketik Kata Sandi Anda...') }}"
                />

                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2 font-mono text-xs text-red-600 uppercase" />
            </div>

            <div class="mt-6 flex justify-end gap-2 border-t pt-4">
                <x-secondary-button x-on:click="$dispatch('close')" class="rounded-none uppercase text-xs font-bold py-2 px-4 shadow-none">
                    {{ __('Batalkan') }}
                </x-secondary-button>

                <x-danger-button class="rounded-none uppercase text-xs font-bold py-2 px-5 shadow-none tracking-widest">
                    {{ __('Setujui Penghapusan') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>