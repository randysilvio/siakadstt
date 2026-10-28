{{-- Indikator kuantitatif akreditasi (matriks BAN-PT IAPS 4.0 / LAMDIK, rumpun Sosial Humaniora). Skor 0–4. --}}
@php
    $S = \App\Services\IndikatorAkreditasiService::class;
    $skor = function ($nilai) use ($S) {
        if ($nilai === null) return '<span class="badge rounded-0 bg-secondary font-monospace">N/A</span>';
        $w = $S::warnaSkor($nilai);
        return '<span class="badge rounded-0 bg-' . $w . ($w === 'warning' ? ' text-dark' : '') . ' font-monospace">' . number_format($nilai, 2) . '</span>';
    };
@endphp
<div class="card border-0 shadow-sm border-top border-dark border-4 mb-4 bg-white">
    <div class="card-header bg-dark text-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span class="uppercase fw-bold small">Indikator Kuantitatif Akreditasi Program Studi <x-info-aturan kunci="akreditasi_dosen" terang /></span>
        <span class="badge bg-light text-dark rounded-0 font-monospace uppercase" style="font-size: 10px;">Matriks BAN-PT / LAMDIK · Skor 0–4</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0 small">
                <thead class="bg-light text-dark uppercase text-center fw-bold">
                    <tr>
                        <th class="text-start ps-3">PROGRAM STUDI</th>
                        <th>MHS AKTIF</th>
                        <th>DOSEN TETAP (DTPS)</th>
                        <th>RASIO DOSEN : MHS</th>
                        <th>% DOSEN TIDAK TETAP</th>
                        <th>BIMBINGAN TA / PEMBIMBING</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($indikatorProdi as $i)
                        <tr class="text-center">
                            <td class="text-start ps-3 fw-bold text-dark uppercase">{{ $i->prodi->nama_prodi }}</td>
                            <td class="font-monospace">{{ $i->mahasiswa }}</td>
                            <td class="font-monospace">{{ $i->dtps }} {!! $skor($i->skor_dtps) !!}</td>
                            <td class="font-monospace fw-bold">{{ $i->rasio_teks }} {!! $skor($i->skor_rasio) !!}</td>
                            <td class="font-monospace">{{ $i->persen_dtt !== null ? number_format($i->persen_dtt, 1) . '%' : '-' }} {!! $skor($i->skor_dtt) !!}</td>
                            <td class="font-monospace">{{ $i->rdpu !== null ? number_format($i->rdpu, 1) : '-' }} {!! $skor($i->skor_bimbingan) !!}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-4 text-muted uppercase fw-bold">Belum ada program studi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="row g-0 border-top">
            <div class="col-md-6 border-end p-3">
                <div class="uppercase fw-bold small text-dark mb-2">Lulusan 3 Tahun Terakhir <x-info-aturan kunci="akreditasi_lulusan" /></div>
                <div class="d-flex flex-wrap gap-3 small font-monospace">
                    <span>LULUSAN: <strong>{{ $indikatorLulusan['jumlah'] }}</strong></span>
                    <span>RATA-RATA IPK: <strong>{{ $indikatorLulusan['rata_ipk'] !== null ? number_format($indikatorLulusan['rata_ipk'], 2) : '-' }}</strong> {!! $skor($indikatorLulusan['skor_ipk']) !!}</span>
                    <span>MASA STUDI: <strong>{{ $indikatorLulusan['masa_studi'] ?? '-' }}</strong> THN</span>
                    <span>TEPAT WAKTU: <strong>{{ $indikatorLulusan['tepat_waktu'] !== null ? number_format($indikatorLulusan['tepat_waktu'], 1) . '%' : '-' }}</strong> {!! $skor($indikatorLulusan['skor_tepat']) !!}</span>
                </div>
            </div>
            <div class="col-md-6 p-3">
                <div class="uppercase fw-bold small text-dark mb-2">Tracer Study 3 Tahun Terakhir <x-info-aturan kunci="tracer" /></div>
                <div class="d-flex flex-wrap gap-3 small font-monospace">
                    <span>RESPONDEN: <strong>{{ $indikatorTracer['responden'] }}/{{ $indikatorTracer['lulusan'] }}</strong>
                        ({{ $indikatorTracer['persen_responden'] !== null ? number_format($indikatorTracer['persen_responden'], 1) . '%' : '-' }}, MIN {{ number_format($indikatorTracer['responden_min'], 1) }}%)</span>
                    <span>WAKTU TUNGGU: <strong>{{ $indikatorTracer['waktu_tunggu'] ?? '-' }}</strong> BLN {!! $skor($indikatorTracer['skor_wt']) !!}</span>
                    <span>KESESUAIAN: <strong>{{ $indikatorTracer['kesesuaian'] !== null ? number_format($indikatorTracer['kesesuaian'], 1) . '%' : '-' }}</strong> {!! $skor($indikatorTracer['skor_pbs']) !!}</span>
                </div>
            </div>
        </div>
    </div>
</div>
