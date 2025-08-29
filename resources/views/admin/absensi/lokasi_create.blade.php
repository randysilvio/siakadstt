@extends('layouts.app')

@push('styles')
    {{-- Menambahkan library Leaflet CSS --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <style>
        #map { height: 400px; } /* Menentukan tinggi peta */
    </style>
@endpush

@section('content')
<div class="container">
    <h1>Tambah Lokasi Kerja Baru</h1>

    <div class="card mt-4">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.absensi.lokasi.store') }}">
                @csrf
                <div class="mb-3">
                    <label for="nama_lokasi" class="form-label">Nama Lokasi</label>
                    <input type="text" class="form-control @error('nama_lokasi') is-invalid @enderror" id="nama_lokasi" name="nama_lokasi" value="{{ old('nama_lokasi') }}" required autofocus>
                    @error('nama_lokasi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Peta akan ditampilkan di sini --}}
                <div id="map" class="mb-2 rounded border"></div>
                
                {{-- PENAMBAHAN TOMBOL LOKASI SAAT INI --}}
                <div class="mb-3">
                    <button type="button" id="locate-btn" class="btn btn-secondary">Gunakan Lokasi Saat Ini</button>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="latitude" class="form-label">Latitude</label>
                        <input type="text" class="form-control @error('latitude') is-invalid @enderror" id="latitude" name="latitude" value="{{ old('latitude') }}" required readonly>
                        @error('latitude') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="longitude" class="form-label">Longitude</label>
                        <input type="text" class="form-control @error('longitude') is-invalid @enderror" id="longitude" name="longitude" value="{{ old('longitude') }}" required readonly>
                        @error('longitude') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="radius_toleransi_meter" class="form-label">Radius Toleransi (dalam meter)</label>
                    <input type="number" class="form-control @error('radius_toleransi_meter') is-invalid @enderror" id="radius_toleransi_meter" name="radius_toleransi_meter" value="{{ old('radius_toleransi_meter', 50) }}" required>
                    @error('radius_toleransi_meter') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <a href="{{ route('admin.absensi.lokasi.index') }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    {{-- Menambahkan library Leaflet JS --}}
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        const initialLatLng = [-2.9126, 132.3023]; // Koordinat awal (Fakfak)
        const map = L.map('map').setView(initialLatLng, 13);
        const locateBtn = document.getElementById('locate-btn');

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap'
        }).addTo(map);

        let marker = L.marker(initialLatLng, { draggable: true }).addTo(map);

        function updateInputs(latlng) {
            document.getElementById('latitude').value = latlng.lat.toFixed(6);
            document.getElementById('longitude').value = latlng.lng.toFixed(6);
        }

        marker.on('dragend', function(e) { updateInputs(e.target.getLatLng()); });
        map.on('click', function(e) {
            marker.setLatLng(e.latlng);
            updateInputs(e.latlng);
        });
        
        // PENAMBAHAN FUNGSI UNTUK TOMBOL LOKASI SAAT INI
        locateBtn.addEventListener('click', function() {
            if (!navigator.geolocation) {
                alert('Browser Anda tidak mendukung geolokasi.');
            } else {
                navigator.geolocation.getCurrentPosition(function(position) {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    const latlng = L.latLng(lat, lng);

                    map.setView(latlng, 16); // Zoom lebih dekat ke lokasi
                    marker.setLatLng(latlng);
                    updateInputs(latlng);
                }, function() {
                    alert('Tidak dapat mengambil lokasi Anda. Pastikan Anda mengizinkan akses lokasi.');
                });
            }
        });
        
        updateInputs(marker.getLatLng());
    </script>
@endpush