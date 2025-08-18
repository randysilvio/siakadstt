<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div>
            <x-input-label for="name" value="Nama Lengkap" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="role" value="Daftar Sebagai" />
            <select id="role" name="role" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required onchange="toggleMahasiswaFields()">
                <option value="mahasiswa" {{ old('role') == 'mahasiswa' ? 'selected' : '' }}>Mahasiswa</option>
                <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
            </select>
            <x-input-error :messages="$errors->get('role')" class="mt-2" />
        </div>

        <div id="mahasiswa-fields" class="mt-4 space-y-4">
            <div>
                <x-input-label for="nim" value="NIM (Nomor Induk Mahasiswa)" />
                <x-text-input id="nim" class="block mt-1 w-full" type="text" name="nim" :value="old('nim')" />
                <x-input-error :messages="$errors->get('nim')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="program_studi_id" value="Program Studi" />
                <select id="program_studi_id" name="program_studi_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    <option value="">Pilih Program Studi</option>
                    @foreach($programStudis as $prodi)
                        <option value="{{ $prodi->id }}" {{ old('program_studi_id') == $prodi->id ? 'selected' : '' }}>{{ $prodi->nama_prodi }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('program_studi_id')" class="mt-2" />
            </div>
        </div>

        <div class="mt-4">
            <x-input-label for="password" value="Password" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password_confirmation" value="Konfirmasi Password" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                Sudah terdaftar?
            </a>

            <x-primary-button class="ms-4">
                Daftar
            </x-primary-button>
        </div>
    </form>
    
    <script>
        function toggleMahasiswaFields() {
            const role = document.getElementById('role').value;
            const mahasiswaFields = document.getElementById('mahasiswa-fields');
            const nimInput = document.getElementById('nim');
            const prodiSelect = document.getElementById('program_studi_id');

            if (role === 'mahasiswa') {
                mahasiswaFields.style.display = 'block';
                nimInput.required = true;
                prodiSelect.required = true;
            } else {
                mahasiswaFields.style.display = 'none';
                nimInput.required = false;
                prodiSelect.required = false;
            }
        }
        // Panggil fungsi saat halaman dimuat untuk mengatur tampilan awal
        document.addEventListener('DOMContentLoaded', toggleMahasiswaFields);
    </script>
</x-guest-layout>