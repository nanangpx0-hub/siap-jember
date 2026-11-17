@extends('layouts.app')

@section('content')
<div class="row justify-content-center"><div class="col-lg-8">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">Pengumuman Hasil Seleksi</h3>
        <a href="{{ route('peserta.dashboard') }}" class="btn btn-default btn-sm">Dashboard</a>
    </div>

    @if(!$kegiatan)
        <div class="card"><div class="card-body text-muted">Belum ada kegiatan aktif.</div></div>
    @elseif(!$pesertaSeleksi)
        <div class="card"><div class="card-body text-muted">Anda tidak terdaftar sebagai peserta terpilih pada kegiatan aktif ini.</div></div>
    @elseif(!$dipublikasikan)
        <div class="alert alert-warning"><i class="fas fa-hourglass-half mr-1"></i>Pengumuman hasil seleksi <strong>{{ $kegiatan->nama_kegiatan }}</strong> belum dipublikasikan. Silakan cek kembali secara berkala.</div>
    @else
        <div class="card">
            <div class="card-header"><h3 class="card-title">Kegiatan: <strong>{{ $kegiatan->nama_kegiatan }}</strong> ({{ $kegiatan->tahun }})</h3></div>
            <div class="card-body">
                @php
                    $label = [
                        'belum_ditentukan' => 'Belum Ditentukan',
                        'lulus' => 'LULUS',
                        'tidak_lulus' => 'TIDAK LULUS',
                        'cadangan' => 'CADANGAN',
                    ][$pesertaSeleksi->status_hasil] ?? $pesertaSeleksi->status_hasil;
                    $badge = match($pesertaSeleksi->status_hasil) {
                        'lulus' => 'success',
                        'tidak_lulus' => 'danger',
                        'cadangan' => 'warning',
                        default => 'secondary',
                    };
                @endphp
                <div class="callout callout-{{ $badge === 'danger' ? 'danger' : ($badge === 'success' ? 'success' : ($badge === 'warning' ? 'warning' : 'info')) }}">
                    <div class="text-muted">Status Kelulusan Anda:</div>
                    <h2 class="mb-0"><span class="badge badge-{{ $badge }}" style="font-size:1.1em">{{ $label }}</span></h2>
                </div>
                @if($pesertaSeleksi->catatan_hasil)
                    <div class="mt-3">
                        <h6 class="mb-1">Catatan</h6>
                        <p class="mb-0" style="white-space:pre-line">{{ $pesertaSeleksi->catatan_hasil }}</p>
                    </div>
                @endif
                <p class="text-muted small mt-3 mb-0">Informasi ini bersifat pribadi dan hanya ditampilkan untuk akun Anda.</p>
            </div>
        </div>
    @endif
</div></div>
@endsection
