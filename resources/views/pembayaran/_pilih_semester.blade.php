{{-- Periode tagihan memakai label baku Tahun Akademik ("2026/2027 Ganjil") agar laporan & gerbang KRS konsisten --}}
@php
    $periodeAktif = $periodes->firstWhere('is_active', true);
    $nilaiTerpilih = old('semester', $terpilih ?? optional($periodeAktif)->label_tagihan);
    $adaDiDaftar = $periodes->contains(fn ($p) => $p->label_tagihan === $nilaiTerpilih);
@endphp
<select name="semester" id="semester" class="form-select rounded-0 font-monospace uppercase @error('semester') is-invalid @enderror" required>
    <option value="">-- PILIH PERIODE --</option>
    @if($nilaiTerpilih && !$adaDiDaftar)
        <option value="{{ $nilaiTerpilih }}" selected>{{ $nilaiTerpilih }} (DATA LAMA)</option>
    @endif
    @foreach($periodes as $p)
        <option value="{{ $p->label_tagihan }}" {{ $nilaiTerpilih === $p->label_tagihan ? 'selected' : '' }}>
            {{ $p->label_tagihan }}{{ $p->is_active ? ' (AKTIF)' : '' }}
        </option>
    @endforeach
</select>
