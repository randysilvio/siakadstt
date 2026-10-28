@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 mt-3 gap-2">
        <div>
            <h3 class="fw-bold text-dark mb-0 uppercase">Aturan & Perhitungan Sistem</h3>
            <span class="text-muted small uppercase">Rangkuman rumus dan aturan yang tampil sebagai ikon <i class="bi bi-question-circle"></i> di setiap halaman</span>
        </div>
    </div>

    <div class="alert alert-light border rounded-0 shadow-sm small mb-4">
        @foreach(\App\Support\InfoAturan::JENIS as $j)
            <span class="badge rounded-0 bg-{{ $j['warna'] }} {{ $j['warna'] === 'warning' ? 'text-dark' : '' }} me-1">{{ strtoupper($j['label']) }}</span>
        @endforeach
        <span class="ms-2 text-muted">Butir berlabel <strong>PERLU KONFIRMASI</strong> memakai angka bawaan sistem yang belum ditetapkan pimpinan kampus.</span>
    </div>

    @foreach(collect($aturan)->groupBy('modul', true) as $modul => $daftar)
        <div class="card border-0 shadow-sm rounded-0 border-top border-dark border-4 mb-4">
            <div class="card-header bg-dark text-white rounded-0 py-2 uppercase fw-bold small">{{ $modul }}</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0 small">
                        <thead class="bg-light text-dark uppercase text-center fw-bold">
                            <tr>
                                <th style="width: 20%;" class="text-start">Aturan</th>
                                <th style="width: 38%;" class="text-start">Cara Sistem Menghitung</th>
                                <th style="width: 30%;" class="text-start">Dasar</th>
                                <th style="width: 12%;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($daftar as $kunci => $a)
                                @php $j = \App\Support\InfoAturan::JENIS[$a['jenis']]; @endphp
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark uppercase">{{ $a['judul'] }}</div>
                                        <div class="text-muted" style="font-size: 11px;">Tampil di: {{ $a['halaman'] }}</div>
                                    </td>
                                    <td><ul class="ps-3 mb-0">@foreach($a['isi'] as $b)<li>{{ $b }}</li>@endforeach</ul></td>
                                    <td class="text-muted">{{ $a['dasar'] }}</td>
                                    <td class="text-center"><span class="badge rounded-0 bg-{{ $j['warna'] }} {{ $j['warna'] === 'warning' ? 'text-dark' : '' }}">{{ strtoupper($j['label']) }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection
