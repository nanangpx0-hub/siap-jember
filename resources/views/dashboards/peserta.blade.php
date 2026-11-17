@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card card-primary card-outline">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-user mr-1"></i>Dashboard Peserta</h3></div>
            <div class="card-body">Selamat datang, <strong>{{ auth()->user()->name }}</strong>. Berikut ringkasan seleksi Anda.</div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Kegiatan Aktif</h3></div>
            <div class="card-body">
                @if(!empty($kegiatan))
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Nama</dt><dd class="col-sm-8">{{ $kegiatan->nama_kegiatan }}</dd>
                        <dt class="col-sm-4">Tahun</dt><dd class="col-sm-8">{{ $kegiatan->tahun }}</dd>
                        <dt class="col-sm-4">Periode</dt><dd class="col-sm-8">{{ optional($kegiatan->tanggal_mulai)->format('d M Y') ?? '-' }} s/d {{ optional($kegiatan->tanggal_selesai)->format('d M Y') ?? '-' }}</dd>
                    </dl>
                @else
                    <p class="text-muted mb-0">Belum ada kegiatan aktif.</p>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Status Seleksi Anda</h3></div>
            <div class="card-body">
                @if(!empty($pesertaSeleksi))
                    <dl class="row mb-0">
                        <dt class="col-sm-5">Status Undangan</dt><dd class="col-sm-7"><span class="badge badge-secondary">{{ $pesertaSeleksi->status_undangan }}</span></dd>
                        <dt class="col-sm-5">Konfirmasi</dt><dd class="col-sm-7">{{ optional($pesertaSeleksi->waktu_konfirmasi)->format('d M Y H:i') ?? '-' }}</dd>
                        <dt class="col-sm-5">CV</dt><dd class="col-sm-7">{!! $pesertaSeleksi->cv_path ? '<span class="badge badge-success">Sudah diunggah</span>' : '<span class="badge badge-warning">Belum diunggah</span>' !!}</dd>
                        <dt class="col-sm-5">Hasil</dt><dd class="col-sm-7">{{ $pesertaSeleksi->status_hasil }}</dd>
                    </dl>
                @else
                    <p class="text-muted mb-0">Anda tidak terdaftar sebagai peserta terpilih pada kegiatan aktif ini.</p>
                @endif
            </div>
        </div>
    </div>
</div>
<div class="card">
    <div class="card-header"><h3 class="card-title">Menu Cepat</h3></div>
    <div class="card-body">
        <a href="{{ route('peserta.undangan') }}" class="btn btn-primary mr-2 mb-2"><i class="fas fa-envelope-open-text mr-1"></i>Undangan / RSVP</a>
        <a href="{{ route('jadwal') }}" class="btn btn-info mr-2 mb-2"><i class="fas fa-calendar-check mr-1"></i>Jadwal Saya</a>
        <a href="{{ route('administrasi') }}" class="btn btn-secondary mr-2 mb-2"><i class="fas fa-file-pdf mr-1"></i>Administrasi (CV)</a>
        <a href="{{ route('pengumuman') }}" class="btn btn-success mb-2"><i class="fas fa-bullhorn mr-1"></i>Pengumuman Hasil</a>
    </div>
</div>
@endsection
