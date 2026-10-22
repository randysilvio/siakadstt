@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <style>
        #map { height: 400px; width: 100%; border: 2px solid #212529; z-index: 1; }
    </style>
@endpush

@section('content')
<div class="container-fluid px-4">
    {{-- BAGIAN HEADER UTAMA --}}
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="mb-0 text-dark fw-bold uppercase">Pembaruan Titik Lokasi Absensi</h3>
            <span class="text-muted small uppercase">Modifikasi Koordinat Geografis & Radius Toleransi Presensi</span>
        </div>
        <div>
            <a href="{{ route('admin.absensi.lokasi.index') }}" class="btn btn-outline-dark btn-sm rounded-0 px-3 uppercase fw-bold small">
                Kembali
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 col-lg-10 mx-auto mb-5">
        <div class="card-header bg-white py-3 border-bottom rounded-0 uppercase fw-bold small text-dark">
            Formulir Pembaruan Parameter Lokasi Kerja
        </div>
        <div class="card-body p-4">
            <form method="POST" action="{{ route('admin.absensi.lokasi.update', $lokasi) }}">
                @csrf
                @method('PUT')
                
                <div class="mb-4">
                    <label for="nama_lokasi" class="form-label text-dark small fw-bold uppercase">Nama Gedung / Lokasi <span class="text-danger">*</span></label>
                    <input type="text" class="form-control rounded-0 uppercase @error('nama_lokasi') is-invalid @enderror" id="nama_lokasi" name="nama_lokasi" value="{{ old('nama_lokasi', $lokasi->nama_lokasi) }}" required autofocus>
                    @error('nama_lokasi') <div class="invalid-feedback font-monospace small">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3 position-relative">
                    <div class="d-flex justify-content-between align-items-end mb-2">
                        <label class="form-label text-dark small fw-bold uppercase mb-0">Pemetaan Titik Koordinat</label>
                        <button type="button" id="locate-btn" class="btn btn-sm btn-dark rounded-0 uppercase fw-bold small">
                            <i class="bi bi-geo-alt me-1"></i> Gunakan Lokasi Saat Ini
                        </button>
                    </div>
                    <div id="map"></div>
                </div>

                <div class="row mb-4 g-3">
                    <div class="col-md-6">
                        <label for="latitude" class="form-label text-dark small fw-bold uppercase">Latitude <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-0 bg-light text-dark font-monospace fw-bold" id="latitude" name="latitude" value="{{ old('latitude', $lokasi->latitude) }}" readonly required>
                    </div>
                    <div class="col-md-6">
                        <label for="longitude" class="form-label text-dark small fw-bold uppercase">Longitude <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-0 bg-light text-dark font-monospace fw-bold" id="longitude" name="longitude" value="{{ old('longitude', $lokasi->longitude) }}" readonly required>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="radius_toleransi_meter" class="form-label text-dark small fw-bold uppercase">Radius Batas Toleransi Absensi <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="number" class="form-control rounded-0 font-monospace @error('radius_toleransi_meter') is-invalid @enderror" id="radius_toleransi_meter" name="radius_toleransi_meter" value="{{ old('radius_toleransi_meter', $lokasi->radius_toleransi_meter) }}" min="1" required>
                        <span class="input-group-text rounded-0 bg-light font-monospace uppercase fw-bold small">Meter</span>
                    </div>
                    @error('radius_toleransi_meter') <div class="invalid-feedback d-block font-monospace small">{{ $message }}</div> @enderror
                </div>

                <div class="d-flex justify-content-end pt-4 mt-4 border-top">
                    <a href="{{ route('admin.absensi.lokasi.index') }}" class="btn btn-sm btn-outline-dark rounded-0 px-4 uppercase fw-bold small me-2">Batal</a>
                    <button type="submit" class="btn btn-sm btn-primary rounded-0 px-5 uppercase fw-bold small shadow-sm">
                        Simpan Pembaruan
                    </button>
                </div>
            </form>
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
                locateBtn.textContent = 'Memproses...';
                navigator.geolocation.getCurrentPosition(function(position) {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    const latlng = L.latLng(lat, lng);

                    map.setView(latlng, 17);
                    marker.setLatLng(latlng);
                    updateInputs(latlng);
                    locateBtn.innerHTML = '<i class="bi bi-geo-alt me-1"></i> Gunakan Lokasi Saat Ini';
                }, function() {
                    alert('Gagal mendeteksi lokasi saat ini.');
                    locateBtn.innerHTML = '<i class="bi bi-geo-alt me-1"></i> Gunakan Lokasi Saat Ini';
                });
            }
        });
    </script>
@endpush