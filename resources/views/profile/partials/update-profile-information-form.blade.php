<section class="bg-white p-6 border-t-4 border-slate-900">
    <header>
        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-900 border-b pb-2">
            {{ __('Atribut Informasi Autentikasi') }}
        </h2>

        <p class="mt-2 text-xs text-slate-600 font-mono uppercase leading-relaxed text-justify">
            {{ __('Manajemen kontrol parameter nama tampilan dan perujukan tautan notifikasi surel terpusat.') }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-4 space-y-4">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" :value="__('Nama Tampilan Resmi')" class="text-xs font-bold uppercase text-slate-900" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full rounded-none uppercase font-bold text-xs py-2 px-3 border-black" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-1 font-mono text-xs text-red-600 uppercase" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Surel Terverifikasi')" class="text-xs font-bold uppercase text-slate-900" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full rounded-none font-mono text-sm py-2 px-3 border-black" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-1 font-mono text-xs text-red-600 uppercase" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-3 bg-slate-50 p-3 border border-slate-200">
                    <p class="text-xs font-mono uppercase text-slate-800 leading-snug">
                        {{ __('Status surel Anda belum melalui tahap pengesahan akhir.') }}

                        <button form="send-verification" class="underline text-xs text-slate-900 hover:text-black font-bold block mt-1 focus:outline-none">
                            {{ __('Klik tautan ini untuk meminta pengiriman ulang token verifikasi surel.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-mono font-bold text-xs text-slate-900 uppercase bg-white p-2 border-l-2 border-slate-900">
                            {{ __('Tautan pengesahan baru telah didistribusikan ke alamat surel di atas.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4 pt-3 border-t">
            <x-primary-button class="rounded-none uppercase font-bold text-xs px-5 py-2 shadow-none tracking-widest">{{ __('Simpan Informasi') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-xs font-mono font-bold text-slate-900 uppercase bg-slate-100 py-1 px-3 border-l-2 border-black"
                >{{ __('Atribut Termutakhirkan.') }}</p>
            @endif
        </div>
    </form>
</section>