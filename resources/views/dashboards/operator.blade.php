@extends('layouts.app')

@section('content')
<div class="card card-primary card-outline">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-tachometer-alt mr-1"></i>Dashboard Superadmin / Operator</h3></div>
    <div class="card-body">
        <p class="mb-3">Kelola master mitra, peserta terpilih, penugasan, jadwal, administrasi penyelenggaraan, dan pengumuman hasil.</p>

        <p class="text-muted small">Kegiatan aktif: <strong>{{ $kegiatanAktif->nama_kegiatan ?? 'Tidak ada kegiatan aktif' }}</strong></p>
        <div class="row">
            <div class="col-lg-2 col-md-4 col-6">
                <div class="small-box bg-info"><div class="inner"><h3>{{ $totalMitra }}</h3><p>Total Mitra</p></div><div class="icon"><i class="fas fa-users"></i></div></div>
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <div class="small-box bg-primary"><div class="inner"><h3>{{ $totalPeserta }}</h3><p>Peserta Terpilih</p></div><div class="icon"><i class="fas fa-user-check"></i></div></div>
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <div class="small-box bg-success"><div class="inner"><h3>{{ $totalRsvpHadir }}</h3><p>RSVP Hadir</p></div><div class="icon"><i class="fas fa-check-circle"></i></div></div>
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <div class="small-box bg-secondary"><div class="inner"><h3>{{ $totalUploadCv }}</h3><p>Upload CV</p></div><div class="icon"><i class="fas fa-file-pdf"></i></div></div>
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <div class="small-box bg-warning"><div class="inner"><h3>{{ $totalJadwal }}</h3><p>Jadwal Wawancara</p></div><div class="icon"><i class="fas fa-calendar-alt"></i></div></div>
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <div class="small-box bg-danger"><div class="inner"><h3>{{ $totalDinilai }}</h3><p>Sudah Dinilai</p></div><div class="icon"><i class="fas fa-star-half-alt"></i></div></div>
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-sm-6 col-md-4 col-lg-3 mb-3"><a href="{{ route('operator.mitra.index') }}" class="btn btn-app bg-primary w-100" style="height:auto;padding:1rem"><i class="fas fa-users fa-2x mb-1 d-block"></i>Master Mitra & Seleksi</a></div>
            <div class="col-sm-6 col-md-4 col-lg-3 mb-3"><a href="{{ route('tugas') }}" class="btn btn-app bg-info w-100" style="height:auto;padding:1rem"><i class="fas fa-user-tie fa-2x mb-1 d-block"></i>Alokasi Tugas</a></div>
            <div class="col-sm-6 col-md-4 col-lg-3 mb-3"><a href="{{ route('jadwal') }}" class="btn btn-app bg-success w-100" style="height:auto;padding:1rem"><i class="fas fa-calendar-alt fa-2x mb-1 d-block"></i>Jadwal Wawancara</a></div>
            <div class="col-sm-6 col-md-4 col-lg-3 mb-3"><a href="{{ route('operator.administrasi') }}" class="btn btn-app bg-secondary w-100" style="height:auto;padding:1rem"><i class="fas fa-folder-open fa-2x mb-1 d-block"></i>Administrasi/Penyelenggaraan</a></div>
            <div class="col-sm-6 col-md-4 col-lg-3 mb-3"><a href="{{ route('operator.pengumuman') }}" class="btn btn-app bg-warning w-100" style="height:auto;padding:1rem"><i class="fas fa-bullhorn fa-2x mb-1 d-block"></i>Rekap & Pengumuman</a></div>
            @if(auth()->user()->role === 'superadmin')
            <div class="col-sm-6 col-md-4 col-lg-3 mb-3"><a href="{{ route('pewawancara.dashboard') }}" class="btn btn-app bg-danger w-100" style="height:auto;padding:1rem"><i class="fas fa-star-half-alt fa-2x mb-1 d-block"></i>Penilaian Wawancara</a></div>
            @endif
        </div>
    </div>
</div>
@endsection
