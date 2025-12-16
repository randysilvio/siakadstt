@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <style>
        #map { height: 450px; width: 100%; border-radius: 8px; z-index: 1; }
    </style>
@endpush

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-0 fw-bold">Edit Lokasi Kerja</h2>
                    <p class="text-muted mb-0">Perbarui informasi titik koordinat lokasi.</p>
                </div>
                <a href="{{ route('admin.absensi.lokasi.index') }}" class="btn btn-outline-secondary btn-sm shadow-sm">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('admin.absensi.lokasi.update', $lokasi) }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-4">
                            <label for="nama_lokasi" class="form-label fw-bold">Nama Lokasi</label>
                            <input type="text" class="form-control @error('nama_lokasi') is-invalid @enderror" id="nama_lokasi" name="nama_lokasi" value="{{ old('nama_lokasi', $lokasi->nama_lokasi) }}" required autofocus>
                            @error('nama_lokasi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3 position-relative">
                            <label class="form-label fw-bold">Pilih Titik di Peta</label>
                            <div id="map" class="shadow-sm border"></div>
                            
                            <button type="button" id="locate-btn" class="btn btn-light shadow-sm position-absolute" style="top: 40px; right: 10px; z-index: 1000;" title="Gunakan Lokasi Saya">
                                <i class="bi bi-crosshair text-primary fs-5"></i>
                            </button>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="latitude" class="form-label fw-bold">Latitude</label>
                                <input type="text" class="form-control bg-light" id="latitude" name="latitude" value="{{ old('latitude', $lokasi->latitude) }}" readonly required>
                            </div>
                            <div class="col-md-6">
                                <label for="longitude" class="form-label fw-bold">Longitude</label>
                                <input type="text" class="form-control bg-light" id="longitude" name="longitude" value="{{ old('longitude', $lokasi->longitude) }}" readonly required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="radius_toleransi_meter" class="form-label fw-bold">Radius Toleransi (Meter)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-broadcast"></i></span>
                                <input type="number" class="form-control @error('radius_toleransi_meter') is-invalid @enderror" id="radius_toleransi_meter" name="radius_toleransi_meter" value="{{ old('radius_toleransi_meter', $lokasi->radius_toleransi_meter) }}" required>
                            </div>
                            @error('radius_toleransi_meter') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>

                        <div class="d-flex gap-2 justify-content-end pt-3 border-top">
                            <a href="{{ route('admin.absensi.lokasi.index') }}" class="btn btn-light border">Batal</a>
                            <button type="submit" class="btn btn-primary px-4 shadow-sm">
                                <i class="bi bi-save me-1"></i> Simpan Perubahan
                            </button>
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
                alert('Browser Anda tidak mendukung geolokasi.');
            } else {
                locateBtn.innerHTML = '<span class="spinner-border spinner-border-sm text-primary" role="status" aria-hidden="true"></span>';
                navigator.geolocation.getCurrentPosition(function(position) {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    const latlng = L.latLng(lat, lng);

                    map.setView(latlng, 17);
                    marker.setLatLng(latlng);
                    updateInputs(latlng);
                    locateBtn.innerHTML = '<i class="bi bi-crosshair text-primary fs-5"></i>';
                }, function() {
                    alert('Gagal mengambil lokasi.');
                    locateBtn.innerHTML = '<i class="bi bi-crosshair text-primary fs-5"></i>';
                });
            }
        });
    </script>
@endpush