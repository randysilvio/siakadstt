@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Manajemen Fakultas</h3>
            <span class="text-muted small uppercase">Data Fakultas & Pimpinan Dekanat</span>
        </div>
        <button type="button" class="btn btn-sm btn-primary rounded-0 px-4 py-2 uppercase fw-bold small shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahFakultas">
            Tambah Fakultas
        </button>
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

    <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 mb-5">
        <div class="card-header bg-dark text-white rounded-0 py-3 uppercase fw-bold small">
            Daftar Fakultas
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0 small">
                    <thead class="bg-light text-dark uppercase text-center fw-bold">
                        <tr>
                            <th style="width: 6%;">NO</th>
                            <th style="width: 12%;">KODE</th>
                            <th class="text-start" style="width: 34%;">NAMA FAKULTAS</th>
                            <th class="text-start" style="width: 26%;">DEKAN</th>
                            <th style="width: 10%;">PRODI</th>
                            <th style="width: 12%;">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($fakultas as $idx => $fak)
                            <tr>
                                <td class="text-center font-monospace fw-bold text-muted">{{ $idx + 1 }}</td>
                                <td class="text-center font-monospace fw-bold">{{ $fak->kode_fakultas }}</td>
                                <td class="ps-3 fw-bold text-dark uppercase">{{ $fak->nama_fakultas }}</td>
                                <td class="ps-3 uppercase">{{ optional($fak->dekan)->nama_lengkap ?? '-' }}</td>
                                <td class="text-center font-monospace fw-bold">{{ $fak->programStudis->count() }}</td>
                                <td class="text-center">
                                    <div class="btn-group rounded-0">
                                        <button type="button" class="btn btn-sm btn-outline-dark rounded-0 py-1 px-2" data-bs-toggle="modal" data-bs-target="#modalEditFakultas-{{ $fak->id }}">
                                            Edit
                                        </button>
                                        <form action="{{ route('admin.fakultas.destroy', $fak->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus fakultas ini?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-0 py-1 px-2">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>

                            <div class="modal fade" id="modalEditFakultas-{{ $fak->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content rounded-0 border-0 border-top border-dark border-4">
                                        <form action="{{ route('admin.fakultas.update', $fak->id) }}" method="POST">
                                            @csrf @method('PUT')
                                            <div class="modal-header bg-dark text-white rounded-0 py-3">
                                                <h6 class="modal-title fw-bold uppercase small">Edit Fakultas</h6>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body p-4">
                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold uppercase text-dark">Kode Fakultas</label>
                                                    <input type="text" name="kode_fakultas" class="form-control rounded-0 font-monospace uppercase" value="{{ $fak->kode_fakultas }}" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold uppercase text-dark">Nama Fakultas</label>
                                                    <input type="text" name="nama_fakultas" class="form-control rounded-0 uppercase" value="{{ $fak->nama_fakultas }}" required>
                                                </div>
                                                <div class="mb-0">
                                                    <label class="form-label small fw-bold uppercase text-dark">Dekan</label>
                                                    <select name="dekan_dosen_id" class="form-select rounded-0 uppercase small">
                                                        <option value="">-- PILIH DOSEN --</option>
                                                        @foreach($dosens as $d)
                                                            <option value="{{ $d->id }}" {{ $fak->dekan_dosen_id == $d->id ? 'selected' : '' }}>{{ $d->nama_lengkap }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="modal-footer bg-light border-top rounded-0">
                                                <button type="button" class="btn btn-sm btn-outline-dark rounded-0 uppercase fw-bold small" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-sm btn-primary rounded-0 px-4 uppercase fw-bold small">Simpan</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr><td colspan="6" class="text-center py-5 text-muted uppercase fw-bold">Belum ada data fakultas.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalTambahFakultas" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content rounded-0 border-0 border-top border-dark border-4">
            <form action="{{ route('admin.fakultas.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-dark text-white rounded-0 py-3">
                    <h6 class="modal-title fw-bold uppercase small">Tambah Fakultas</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold uppercase text-dark">Kode Fakultas <span class="text-danger">*</span></label>
                        <input type="text" name="kode_fakultas" class="form-control rounded-0 font-monospace uppercase" placeholder="CONTOH: FT / FKIP" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold uppercase text-dark">Nama Fakultas <span class="text-danger">*</span></label>
                        <input type="text" name="nama_fakultas" class="form-control rounded-0 uppercase" placeholder="CONTOH: FAKULTAS TEOLOGI" required>
                    </div>
                    <div class="mb-0">
                        <label class="form-label small fw-bold uppercase text-dark">Dekan</label>
                        <select name="dekan_dosen_id" class="form-select rounded-0 uppercase small">
                            <option value="">-- PILIH DOSEN --</option>
                            @foreach($dosens as $d)
                                <option value="{{ $d->id }}">{{ $d->nama_lengkap }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top rounded-0">
                    <button type="button" class="btn btn-sm btn-outline-dark rounded-0 uppercase fw-bold small" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-primary rounded-0 px-4 uppercase fw-bold small">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection