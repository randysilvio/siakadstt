@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Dashboard Keuangan</h1>
    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card text-white bg-primary mb-3">
                <div class="card-body">
                    <h5 class="card-title">Total Tagihan</h5>
                    <p class="card-text fs-1">{{ $totalTagihan }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-success mb-3">
                <div class="card-body">
                    <h5 class="card-title">Tagihan Lunas</h5>
                    <p class="card-text fs-1">{{ $totalLunas }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-danger mb-3">
                <div class="card-body">
                    <h5 class="card-title">Tagihan Belum Lunas</h5>
                    <p class="card-text fs-1">{{ $totalBelumLunas }}</p>
                </div>
            </div>
        </div>
    </div>
    <a href="{{ route('pembayaran.index') }}" class="btn btn-primary mt-3">Lihat Detail Pembayaran</a>
</div>
@endsection