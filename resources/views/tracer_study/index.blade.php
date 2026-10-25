@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Tracer Study Alumni</h3>
            <span class="text-muted small uppercase">Data Pelacakan Lulusan</span>
        </div>
        <div class="d-flex gap-2">
            @if($tracer)
                <a href="{{ route('transkrip.index') }}" class="btn btn-sm btn-dark rounded-0 px-3 uppercase fw-bold small">
                    Transkrip Nilai
                </a>
            @endif
            <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-dark rounded-0 px-3 uppercase fw-bold small">
                Kembali
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border rounded-0 shadow-sm mb-4 p-3 uppercase small fw-bold">
            {{ session('success') }}
            <button type="button" class="btn-close rounded-0" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border rounded-0 shadow-sm mb-4 p-3 uppercase small fw-bold">
            {{ session('error') }}
            <button type="button" class="btn-close rounded-0" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 col-lg-10 mx-auto mb-5">
        <div class="card-header bg-dark text-white py-3 rounded-0 d-flex justify-content-between align-items-center">
            <span class="uppercase fw-bold small">{{ $mahasiswa->nama_lengkap }} ({{ $mahasiswa->nim }})</span>
            <span class="badge bg-light text-dark rounded-0 font-monospace">
                {{ $tracer ? 'TERISI (' . $tracer->updated_at->format('d/m/Y') . ')' : 'BELUM DIISI' }}
            </span>
        </div>
        <div class="card-body p-4">
            <form action="{{ route('tracer.store') }}" method="POST">
                @csrf

                <h6 class="fw-bold text-dark uppercase small border-bottom pb-2 mb-3">A. Data Pekerjaan / Pelayanan</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold uppercase text-dark">Tahun Lulus <span class="text-danger">*</span></label>
                        <input type="number" name="tahun_lulus" class="form-control rounded-0 font-monospace fw-bold" value="{{ old('tahun_lulus', $tracer->tahun_lulus ?? ($mahasiswa->tanggal_lulus ? $mahasiswa->tanggal_lulus->format('Y') : date('Y'))) }}" min="1990" max="{{ date('Y') + 1 }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold uppercase text-dark">Status Saat Ini <span class="text-danger">*</span></label>
                        @php $stKerja = old('status_pekerjaan', $tracer->status_pekerjaan ?? 'Bekerja / Pelayan Gereja'); @endphp
                        <select name="status_pekerjaan" class="form-select rounded-0 uppercase small fw-bold" required>
                            <option value="Bekerja / Pelayan Gereja" {{ $stKerja == 'Bekerja / Pelayan Gereja' ? 'selected' : '' }}>Bekerja / Pelayan Gereja</option>
                            <option value="Melanjutkan Studi" {{ $stKerja == 'Melanjutkan Studi' ? 'selected' : '' }}>Melanjutkan Studi</option>
                            <option value="Wiraswasta" {{ $stKerja == 'Wiraswasta' ? 'selected' : '' }}>Wiraswasta</option>
                            <option value="Belum Bekerja" {{ $stKerja == 'Belum Bekerja' ? 'selected' : '' }}>Belum Bekerja</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold uppercase text-dark">Waktu Tunggu (Bulan) <span class="text-danger">*</span></label>
                        <input type="number" name="waktu_tunggu_bulan" class="form-control rounded-0 font-monospace fw-bold" value="{{ old('waktu_tunggu_bulan', $tracer->waktu_tunggu_bulan ?? 0) }}" min="0" max="120" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold uppercase text-dark">Nama Gereja / Instansi</label>
                        <input type="text" name="nama_instansi_gereja" class="form-control rounded-0 uppercase" value="{{ old('nama_instansi_gereja', $tracer->nama_instansi_gereja ?? '') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold uppercase text-dark">Jabatan / Posisi</label>
                        <input type="text" name="jabatan_pekerjaan" class="form-control rounded-0 uppercase" value="{{ old('jabatan_pekerjaan', $tracer->jabatan_pekerjaan ?? '') }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold uppercase text-dark">Kesesuaian Bidang Ilmu <span class="text-danger">*</span></label>
                        @php $kesesuaian = old('kesesuaian_bidang', $tracer->kesesuaian_bidang ?? 'Sangat Sesuai'); @endphp
                        <select name="kesesuaian_bidang" class="form-select rounded-0 uppercase small fw-bold" required>
                            <option value="Sangat Sesuai" {{ $kesesuaian == 'Sangat Sesuai' ? 'selected' : '' }}>Sangat Sesuai</option>
                            <option value="Sesuai" {{ $kesesuaian == 'Sesuai' ? 'selected' : '' }}>Sesuai</option>
                            <option value="Kurang Sesuai" {{ $kesesuaian == 'Kurang Sesuai' ? 'selected' : '' }}>Kurang Sesuai</option>
                            <option value="Tidak Sesuai" {{ $kesesuaian == 'Tidak Sesuai' ? 'selected' : '' }}>Tidak Sesuai</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold uppercase text-dark">Kisaran Penghasilan</label>
                        @php $pendapatan = old('kisaran_pendapatan', $tracer->kisaran_pendapatan ?? ''); @endphp
                        <select name="kisaran_pendapatan" class="form-select rounded-0 uppercase small fw-bold">
                            <option value="">-- PILIH --</option>
                            <option value="< Rp 2.000.000" {{ $pendapatan == '< Rp 2.000.000' ? 'selected' : '' }}>&lt; Rp 2.000.000</option>
                            <option value="Rp 2.000.000 - Rp 4.000.000" {{ $pendapatan == 'Rp 2.000.000 - Rp 4.000.000' ? 'selected' : '' }}>Rp 2.000.000 - Rp 4.000.000</option>
                            <option value="Rp 4.000.000 - Rp 6.000.000" {{ $pendapatan == 'Rp 4.000.000 - Rp 6.000.000' ? 'selected' : '' }}>Rp 4.000.000 - Rp 6.000.000</option>
                            <option value="> Rp 6.000.000" {{ $pendapatan == '> Rp 6.000.000' ? 'selected' : '' }}>&gt; Rp 6.000.000</option>
                        </select>
                    </div>
                </div>

                <h6 class="fw-bold text-dark uppercase small border-bottom pb-2 mb-3">B. Kontak Pimpinan Instansi / Jemaat</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold uppercase text-dark">Nama Atasan / Pimpinan</label>
                        <input type="text" name="kontak_atasan_nama" class="form-control rounded-0 uppercase" value="{{ old('kontak_atasan_nama', $tracer->kontak_atasan_nama ?? '') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold uppercase text-dark">Jabatan</label>
                        <input type="text" name="kontak_atasan_jabatan" class="form-control rounded-0 uppercase" value="{{ old('kontak_atasan_jabatan', $tracer->kontak_atasan_jabatan ?? '') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold uppercase text-dark">Nomor Telepon / HP</label>
                        <input type="text" name="kontak_atasan_hp" class="form-control rounded-0 font-monospace" value="{{ old('kontak_atasan_hp', $tracer->kontak_atasan_hp ?? '') }}">
                    </div>
                </div>

                <h6 class="fw-bold text-dark uppercase small border-bottom pb-2 mb-3">C. Masukan Kurikulum</h6>
                <div class="mb-4">
                    <label class="form-label small fw-bold uppercase text-dark">Saran Pengembangan Kurikulum</label>
                    <textarea name="saran_kurikulum" rows="3" class="form-control rounded-0">{{ old('saran_kurikulum', $tracer->saran_kurikulum ?? '') }}</textarea>
                </div>

                <div class="d-flex justify-content-end pt-3 border-top">
                    <button type="submit" class="btn btn-sm btn-primary rounded-0 px-5 py-2 uppercase fw-bold small">
                        Simpan Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection