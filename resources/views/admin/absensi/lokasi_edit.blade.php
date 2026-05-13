@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <style>
        #map { height: 400px; width: 100%; border: 1px solid #dee2e6; z-index: 1; }
    </style>
@endpush

@section('content')
<div class="container-fluid px-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 class="mb-0 text-dark fw-bold">Pembaruan Titik Lokasi</h3>
                    <span class="text-muted small">Modifikasi koordinat geografis absensi</span>
                </div>
                <a href="{{ route('admin.absensi.lokasi.index') }}" class="btn btn-outline-dark btn-sm">Batal</a>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('admin.absensi.lokasi.update', $lokasi) }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-4">
                            <label for="nama_lokasi" class="form-label text-dark fw-semibold">Nama Gedung / Lokasi</label>
                            <input type="text" class="form-control rounded-1 @error('nama_lokasi') is-invalid @enderror" id="nama_lokasi" name="nama_lokasi" value="{{ old('nama_lokasi', $lokasi->nama_lokasi) }}" required autofocus>
                            @error('nama_lokasi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3 position-relative">
                            <div class="d-flex justify-content-between align-items-end mb-2">
                                <label class="form-label text-dark fw-semibold mb-0">Pemetaan Titik Koordinat</label>
                                <button type="button" id="locate-btn" class="btn btn-sm btn-primary">
                                    <i class="bi bi-geo-alt"></i> Gunakan Lokasi Saat Ini
                                </button>
                            </div>
                            <div id="map"></div>
                        </div>

                        <div class="row mb-4 g-3">
                            <div class="col-md-6">
                                <label for="latitude" class="form-label text-muted small fw-bold">LATITUDE</label>
                                <input type="text" class="form-control rounded-1 bg-light text-muted font-monospace" id="latitude" name="latitude" value="{{ old('latitude', $lokasi->latitude) }}" readonly required>
                            </div>
                            <div class="col-md-6">
                                <label for="longitude" class="form-label text-muted small fw-bold">LONGITUDE</label>
                                <input type="text" class="form-control rounded-1 bg-light text-muted font-monospace" id="longitude" name="longitude" value="{{ old('longitude', $lokasi->longitude) }}" readonly required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="radius_toleransi_meter" class="form-label text-dark fw-semibold">Radius Batas Toleransi Absensi</label>
                            <div class="input-group">
                                <input type="number" class="form-control rounded-start-1 @error('radius_toleransi_meter') is-invalid @enderror" id="radius_toleransi_meter" name="radius_toleransi_meter" value="{{ old('radius_toleransi_meter', $lokasi->radius_toleransi_meter) }}" required>
                                <span class="input-group-text rounded-end-1 bg-light">Meter</span>
                            </div>
                            @error('radius_toleransi_meter') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>

                        <hr class="text-muted my-4">
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary px-5">Simpan Pembaruan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        const initialLatLng = [{{ old('latitude', $lokasi->latitude) }}, {{ old('longitude', $lokasi->longitude) }}];
        const map = L.map('map').setView(initialLatLng, 16);
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

        locateBtn.addEventListener('click', function() {
            if (!navigator.geolocation) {
                alert('Sistem peramban Anda tidak mendukung layanan lokasi.');
            } else {
                locateBtn.innerHTML = 'Memproses...';
                navigator.geolocation.getCurrentPosition(function(position) {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    const latlng = L.latLng(lat, lng);

                    map.setView(latlng, 17);
                    marker.setLatLng(latlng);
                    updateInputs(latlng);
                    locateBtn.innerHTML = '<i class="bi bi-geo-alt"></i> Gunakan Lokasi Saat Ini';
                }, function() {
                    alert('Gagal mendeteksi lokasi saat ini.');
                    locateBtn.innerHTML = '<i class="bi bi-geo-alt"></i> Gunakan Lokasi Saat Ini';
                });
            }
        });
    </script>
@endpush