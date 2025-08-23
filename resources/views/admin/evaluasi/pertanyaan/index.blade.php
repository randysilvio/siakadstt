@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Manajemen Pertanyaan Evaluasi</h1>
        <a href="{{ route('evaluasi-pertanyaan.create') }}" class="btn btn-primary">Tambah Pertanyaan Baru</a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th style="width: 5%;">Urutan</th>
                            <th>Pertanyaan</th>
                            <th>Tipe Jawaban</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pertanyaan as $item)
                            <tr>
                                <td class="text-center">{{ $item->urutan }}</td>
                                <td>{{ $item->pertanyaan }}</td>
                                <td>
                                    @if ($item->tipe_jawaban == 'skala_1_5')
                                        Skala 1-5
                                    @else
                                        Teks
                                    @endif
                                </td>
                                <td>
                                    @if ($item->is_active)
                                        <span class="badge bg-success">Aktif</span>
                                    @else
                                        <span class="badge bg-secondary">Tidak Aktif</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('evaluasi-pertanyaan.edit', $item->id) }}" class="btn btn-warning btn-sm">Edit</a>
                                    <form action="{{ route('evaluasi-pertanyaan.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pertanyaan ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">Belum ada pertanyaan evaluasi yang dibuat.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($pertanyaan->hasPages())
        <div class="card-footer">
            {{ $pertanyaan->links() }}
        </div>
        @endif
    </div>
</div>
@endsection