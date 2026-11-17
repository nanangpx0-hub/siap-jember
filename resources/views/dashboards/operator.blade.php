@extends('layouts.app')

@section('content')
<div class="card card-primary card-outline">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-tachometer-alt mr-1"></i>Dashboard Operator/Admin</h3></div>
    <div class="card-body">
        <p class="mb-3">Kelola master mitra, peserta terpilih, penugasan, jadwal, administrasi penyelenggaraan, dan pengumuman hasil.</p>
        <div class="row">
            <div class="col-sm-6 col-md-4 col-lg-3 mb-3"><a href="{{ route('operator.mitra.index') }}" class="btn btn-app bg-primary w-100" style="height:auto;padding:1rem"><i class="fas fa-users fa-2x mb-1 d-block"></i>Master Mitra & Seleksi</a></div>
            <div class="col-sm-6 col-md-4 col-lg-3 mb-3"><a href="{{ route('tugas') }}" class="btn btn-app bg-info w-100" style="height:auto;padding:1rem"><i class="fas fa-user-tie fa-2x mb-1 d-block"></i>Alokasi Tugas</a></div>
            <div class="col-sm-6 col-md-4 col-lg-3 mb-3"><a href="{{ route('jadwal') }}" class="btn btn-app bg-success w-100" style="height:auto;padding:1rem"><i class="fas fa-calendar-alt fa-2x mb-1 d-block"></i>Jadwal Wawancara</a></div>
            <div class="col-sm-6 col-md-4 col-lg-3 mb-3"><a href="{{ route('operator.administrasi') }}" class="btn btn-app bg-secondary w-100" style="height:auto;padding:1rem"><i class="fas fa-folder-open fa-2x mb-1 d-block"></i>Administrasi/Penyelenggaraan</a></div>
            <div class="col-sm-6 col-md-4 col-lg-3 mb-3"><a href="{{ route('operator.pengumuman') }}" class="btn btn-app bg-warning w-100" style="height:auto;padding:1rem"><i class="fas fa-bullhorn fa-2x mb-1 d-block"></i>Rekap & Pengumuman</a></div>
        </div>
    </div>
</div>
@endsection
