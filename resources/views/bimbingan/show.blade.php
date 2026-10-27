@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 mt-3 gap-3">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Detail Bimbingan</h3>
            <span class="text-muted small uppercase">{{ $bimbingan->jenis_bimbingan }} — {{ $bimbingan->mahasiswa->nama_lengkap }} ({{ $bimbingan->mahasiswa->nim }})</span>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('bimbingan.cetak', $bimbingan->id) }}" target="_blank" class="btn btn-sm btn-dark rounded-0 px-4 py-2 uppercase fw-bold small">
                Cetak Kartu Bimbingan
            </a>
            <a href="{{ route('bimbingan.index') }}" class="btn btn-sm btn-outline-dark rounded-0 px-3 py-2 uppercase fw-bold small">
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

    <div class="row g-4 mb-4">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 h-100 bg-white">
                <div class="card-header bg-white py-3 border-bottom rounded-0 d-flex justify-content-between align-items-center">
                    <span class="uppercase fw-bold small text-dark">Data Bimbingan</span>
                    <span class="badge bg-dark text-white rounded-0 font-monospace">{{ strtoupper($bimbingan->status) }}</span>
                </div>
                <div class="card-body p-4 small">
                    <h5 class="fw-bold text-dark uppercase mb-3">{{ $bimbingan->judul }}</h5>
                    <table class="table table-borderless uppercase mb-0">
                        <tr>
                            <td class="fw-bold p-1" style="width: 170px;">MAHASISWA</td>
                            <td class="p-1">: <strong>{{ $bimbingan->mahasiswa->nama_lengkap }}</strong> ({{ $bimbingan->mahasiswa->nim }})</td>
                        </tr>
                        <tr>
                            <td class="fw-bold p-1">PROGRAM STUDI</td>
                            <td class="p-1">: {{ optional($bimbingan->mahasiswa->programStudi)->nama_prodi ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold p-1">PEMBIMBING 1</td>
                            <td class="p-1">: {{ optional($bimbingan->pembimbing1)->nama_lengkap ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold p-1">PEMBIMBING 2</td>
                            <td class="p-1">: {{ optional($bimbingan->pembimbing2)->nama_lengkap ?? '-' }}</td>
                        </tr>
                        @if($bimbingan->tanggal_sidang)
                        <tr>
                            <td class="fw-bold p-1">JADWAL SIDANG</td>
                            <td class="p-1 font-monospace fw-bold">: {{ $bimbingan->tanggal_sidang->translatedFormat('d F Y') }} ({{ $bimbingan->ruang_sidang ?? '-' }})</td>
                        </tr>
                        @endif
                        @if($bimbingan->nilai_huruf)
                        <tr>
                            <td class="fw-bold p-1">NILAI SIDANG</td>
                            <td class="p-1 font-monospace fw-bold">: {{ number_format($bimbingan->nilai_angka, 2) }} ({{ $bimbingan->nilai_huruf }})</td>
                        </tr>
                        @endif
                    </table>
                    @if($bimbingan->catatan_kaprodi)
                        <div class="border p-2 bg-light mt-3 small font-monospace">
                            <strong>CATATAN:</strong> {{ $bimbingan->catatan_kaprodi }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            @if(!Auth::user()->hasRole('mahasiswa'))
                <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 h-100 bg-light">
                    <div class="card-header bg-dark text-white py-3 rounded-0 uppercase fw-bold small">
                        Pengaturan Pembimbing & Sidang
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('bimbingan.updateStatus', $bimbingan->id) }}" method="POST">
                            @csrf @method('PATCH')
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold uppercase text-dark mb-1">Pembimbing 1</label>
                                    <select name="pembimbing_1_id" class="form-select form-select-sm rounded-0 uppercase">
                                        <option value="">-- PILIH --</option>
                                        @foreach($dosens as $d)
                                            <option value="{{ $d->id }}" {{ $bimbingan->pembimbing_1_id == $d->id ? 'selected' : '' }}>{{ $d->nama_lengkap }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold uppercase text-dark mb-1">Pembimbing 2</label>
                                    <select name="pembimbing_2_id" class="form-select form-select-sm rounded-0 uppercase">
                                        <option value="">-- PILIH --</option>
                                        @foreach($dosens as $d)
                                            <option value="{{ $d->id }}" {{ $bimbingan->pembimbing_2_id == $d->id ? 'selected' : '' }}>{{ $d->nama_lengkap }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label small fw-bold uppercase text-dark mb-1">Status</label>
                                    <select name="status" class="form-select form-select-sm rounded-0 uppercase fw-bold">
                                        @foreach(['Diajukan', 'Disetujui / Aktif Bimbingan', 'Siap Sidang', 'Selesai / Lulus', 'Revisi Judul'] as $st)
                                            <option value="{{ $st }}" {{ $bimbingan->status == $st ? 'selected' : '' }}>{{ $st }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold uppercase text-dark mb-1">Tanggal Sidang</label>
                                    <input type="date" name="tanggal_sidang" class="form-control form-control-sm rounded-0 font-monospace" value="{{ $bimbingan->tanggal_sidang ? $bimbingan->tanggal_sidang->format('Y-m-d') : '' }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold uppercase text-dark mb-1">Ruang Sidang</label>
                                    <input type="text" name="ruang_sidang" class="form-control form-control-sm rounded-0 uppercase" value="{{ $bimbingan->ruang_sidang }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold uppercase text-dark mb-1">Nilai Angka</label>
                                    <input type="number" step="0.01" name="nilai_angka" class="form-control form-control-sm rounded-0 font-monospace" value="{{ $bimbingan->nilai_angka }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold uppercase text-dark mb-1">Nilai Huruf</label>
                                    <select name="nilai_huruf" class="form-select form-select-sm rounded-0 font-monospace fw-bold">
                                        <option value="">-</option>
                                        @foreach(['A', 'B', 'C', 'D', 'E'] as $h)
                                            <option value="{{ $h }}" {{ $bimbingan->nilai_huruf == $h ? 'selected' : '' }}>{{ $h }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-bold uppercase text-dark mb-1">Catatan</label>
                                    <input type="text" name="catatan_kaprodi" class="form-control form-control-sm rounded-0" value="{{ $bimbingan->catatan_kaprodi }}">
                                </div>
                                <div class="col-12 pt-2">
                                    <button type="submit" class="btn btn-sm btn-primary rounded-0 px-4 uppercase fw-bold small w-100">
                                        Simpan
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            @else
                <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 h-100 bg-light">
                    <div class="card-header bg-dark text-white py-3 rounded-0 uppercase fw-bold small">
                        Tambah Catatan Bimbingan
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('bimbingan.log.store', $bimbingan->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-2">
                                <label class="form-label small fw-bold uppercase text-dark mb-1">Tanggal <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal_bimbingan" class="form-control form-control-sm rounded-0 font-monospace" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small fw-bold uppercase text-dark mb-1">Dosen Pembimbing</label>
                                <select name="dosen_id" class="form-select form-select-sm rounded-0 uppercase">
                                    @if($bimbingan->pembimbing1)
                                        <option value="{{ $bimbingan->pembimbing1->id }}">{{ $bimbingan->pembimbing1->nama_lengkap }}</option>
                                    @endif
                                    @if($bimbingan->pembimbing2)
                                        <option value="{{ $bimbingan->pembimbing2->id }}">{{ $bimbingan->pembimbing2->nama_lengkap }}</option>
                                    @endif
                                </select>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small fw-bold uppercase text-dark mb-1">Bab / Materi Bahasan <span class="text-danger">*</span></label>
                                <input type="text" name="topik_bab" class="form-control form-control-sm rounded-0 uppercase" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small fw-bold uppercase text-dark mb-1">Catatan Mahasiswa</label>
                                <textarea name="catatan_mahasiswa" rows="2" class="form-control form-control-sm rounded-0"></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold uppercase text-dark mb-1">Berkas Draf (PDF/DOCX)</label>
                                <input type="file" name="file_draf" class="form-control form-control-sm rounded-0" accept=".pdf,.doc,.docx">
                            </div>
                            <button type="submit" class="btn btn-sm btn-primary rounded-0 w-100 py-2 uppercase fw-bold small">
                                Simpan Catatan
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 mb-5">
        <div class="card-header bg-dark text-white rounded-0 py-3 d-flex justify-content-between align-items-center">
            <span class="uppercase fw-bold small">Riwayat Pertemuan Bimbingan</span>
            <span class="badge bg-light text-dark rounded-0 font-monospace">{{ $bimbingan->logs->count() }} PERTEMUAN</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0 small">
                    <thead class="bg-light text-dark uppercase text-center fw-bold">
                        <tr>
                            <th style="width: 5%;">NO</th>
                            <th style="width: 12%;">TANGGAL</th>
                            <th class="text-start" style="width: 25%;">MATERI BAHASAN</th>
                            <th class="text-start" style="width: 34%;">CATATAN PEMBIMBING</th>
                            <th style="width: 12%;">STATUS</th>
                            @if(!Auth::user()->hasRole('mahasiswa'))
                                <th style="width: 12%;">AKSI</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bimbingan->logs as $idx => $log)
                            <tr>
                                <td class="text-center font-monospace fw-bold">{{ $idx + 1 }}</td>
                                <td class="text-center font-monospace">{{ $log->tanggal_bimbingan->format('d/m/Y') }}</td>
                                <td class="ps-3">
                                    <div class="fw-bold text-dark uppercase">{{ $log->topik_bab }}</div>
                                    @if($log->catatan_mahasiswa)
                                        <div class="text-muted small">{{ $log->catatan_mahasiswa }}</div>
                                    @endif
                                    @if($log->file_draf)
                                        <a href="{{ route('berkas.unduh', ['jenis' => 'draf-bimbingan', 'id' => $log->id]) }}" target="_blank" class="btn btn-sm btn-outline-dark rounded-0 py-0 px-2 mt-1 font-monospace" style="font-size: 10px;">
                                            Unduh Draf
                                        </a>
                                    @endif
                                </td>
                                <td class="ps-3">
                                    @if(!Auth::user()->hasRole('mahasiswa'))
                                        <form action="{{ route('bimbingan.log.validasi', $log->id) }}" method="POST" id="form-log-{{ $log->id }}">
                                            @csrf @method('PATCH')
                                            <textarea name="arahan_dosen" rows="2" class="form-control form-control-sm rounded-0 mb-1">{{ $log->arahan_dosen }}</textarea>
                                    @else
                                        @if($log->arahan_dosen)
                                            <div class="fw-bold text-dark">{{ $log->arahan_dosen }}</div>
                                            <small class="text-muted font-monospace">{{ optional($log->dosen)->nama_lengkap ?? '' }}</small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if(!Auth::user()->hasRole('mahasiswa'))
                                        <select name="status_paraf" form="form-log-{{ $log->id }}" class="form-select form-select-sm rounded-0 font-monospace fw-bold">
                                            <option value="Menunggu Paraf" {{ $log->status_paraf == 'Menunggu Paraf' ? 'selected' : '' }}>MENUNGGU</option>
                                            <option value="Disetujui / Lanjut" {{ $log->status_paraf == 'Disetujui / Lanjut' ? 'selected' : '' }}>DISETUJUI</option>
                                            <option value="Revisi" {{ $log->status_paraf == 'Revisi' ? 'selected' : '' }}>REVISI</option>
                                        </select>
                                    @else
                                        <span class="font-monospace fw-bold uppercase">{{ $log->status_paraf }}</span>
                                    @endif
                                </td>
                                @if(!Auth::user()->hasRole('mahasiswa'))
                                    <td class="text-center">
                                        <button type="submit" form="form-log-{{ $log->id }}" class="btn btn-sm btn-dark rounded-0 py-1 px-3 uppercase fw-bold small">
                                            Simpan
                                        </button>
                                        </form>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted uppercase fw-bold">Belum ada catatan bimbingan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection