<x-guest-layout>
    <div class="px-6 py-4">
        <div class="flex flex-col items-center mb-8">
            {{-- Logo dimunculkan sesuai bentuk aslinya (tanpa background box/lingkaran) --}}
            <img src="{{ asset('images/logo.png') }}" 
                 alt="Logo STT GPI PAPUA" 
                 class="w-24 h-24 object-contain mb-2 filter drop-shadow-sm" 
                 onerror="this.style.display='none';">
            
            {{-- Title --}}
            <h1 class="text-2xl font-bold text-center text-gray-900 tracking-tight">
                SIAKAD STT GPI PAPUA
            </h1>
            
            {{-- Motto / Subtitle Update --}}
            <p class="text-sm text-teal-700 mt-2 font-medium italic text-center">
                "Aku mengangkat engkau untuk membangun dan menanam"
            </p>
        </div>

        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf

            <div>
                <x-input-label for="email" :value="__('Email')" class="text-gray-700 font-medium" />
                <x-text-input id="email" 
                              class="block mt-1 w-full border-gray-300 focus:border-teal-600 focus:ring-teal-600 rounded-lg shadow-sm" 
                              type="email" 
                              name="email" 
                              :value="old('email')" 
                              required autofocus autocomplete="username" 
                              placeholder="nama@sttgpipapua.ac.id" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div>
                <div class="flex justify-between items-center">
                    <x-input-label for="password" :value="__('Password')" class="text-gray-700 font-medium" />
                </div>
                
                <x-text-input id="password" 
                                class="block mt-1 w-full border-gray-300 focus:border-teal-600 focus:ring-teal-600 rounded-lg shadow-sm"
                                type="password"
                                name="password"
                                required autocomplete="current-password" 
                                placeholder="••••••••" />

                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div class="flex items-center justify-between">
                <label for="remember_me" class="inline-flex items-center cursor-pointer">
                    <input id="remember_me" type="checkbox" 
                           class="rounded border-gray-300 text-teal-600 shadow-sm focus:ring-teal-600" 
                           name="remember">
                    <span class="ms-2 text-sm text-gray-600">{{ __('Ingat Saya') }}</span>
                </label>

                @if (Route::has('password.request'))
                    <a class="underline text-sm text-teal-600 hover:text-teal-800 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 font-medium" 
                       href="{{ route('password.request') }}">
                        {{ __('Lupa Password?') }}
                    </a>
                @endif
            </div>

            <div class="mt-6">
                <button type="submit" 
                        class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-bold text-white bg-teal-600 hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 transition duration-150 ease-in-out uppercase tracking-wider">
                    {{ __('Masuk ke Portal') }}
                </button>
            </div>
        </form>
        
        <div class="mt-8 text-center">
            <p class="text-xs text-gray-400">
                &copy; {{ date('Y') }} STT GPI Papua. All rights reserved.
            </p>
        </div>
    </div>
</x-guest-layout>