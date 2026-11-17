<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'SIAP Jember') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('img/brand/siap-logo.svg') }}">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@600;700;800&display=swap">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <style>
        h1, h2, h3, h4, h5, h6, .brand-text, .brand-link, .card-title, .nav-header {
            font-family: 'Plus Jakarta Sans', 'Source Sans Pro', sans-serif;
        }
        .brand-text .bps-sub { display: block; font-size: .68rem; font-weight: 500; color: #9EC7F0; letter-spacing: .06em; text-transform: uppercase; }
    </style>
    @stack('styles')
</head>
<body class="hold-transition sidebar-mini layout-fixed">
@php($role = auth()->user()->role ?? null)
<div class="wrapper">
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <ul class="navbar-nav">
            <li class="nav-item"><a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a></li>
            <li class="nav-item d-none d-sm-inline-block"><span class="nav-link font-weight-bold">{{ config('app.name') }}</span></li>
        </ul>
        <ul class="navbar-nav ml-auto align-items-center">
            @auth
            <li class="nav-item d-none d-sm-inline-block"><span class="nav-link text-muted small">{{ auth()->user()->name }} <span class="badge badge-info text-uppercase">{{ $role }}</span></span></li>
            <li class="nav-item">
                <form method="POST" action="{{ route('logout') }}" class="m-0">
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary btn-sm"><i class="fas fa-sign-out-alt mr-1"></i>Logout</button>
                </form>
            </li>
            @endauth
        </ul>
    </nav>

    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <a href="{{ route('dashboard') }}" class="brand-link">
            <img src="{{ asset('img/brand/siap-logo-mark.svg') }}" alt="Logo SIAP Jember" class="brand-image ml-2 elevation-2" style="width:34px;height:34px;border-radius:8px">
            <span class="brand-text font-weight-bold">SIAP <b>Jember</b><span class="bps-sub">Mitra Statistik 2027</span></span>
        </a>
        <div class="sidebar">
            @auth
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="image"><span class="img-circle elevation-2 d-inline-flex align-items-center justify-content-center bg-secondary" style="width:34px;height:34px"><i class="fas fa-user"></i></span></div>
                <div class="info"><a href="{{ route('dashboard') }}" class="d-block">{{ auth()->user()->name }}</a><small class="text-muted text-uppercase">{{ $role }}</small></div>
            </div>
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                    <li class="nav-item"><a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard','*.dashboard') ? 'active' : '' }}"><i class="nav-icon fas fa-tachometer-alt"></i><p>Dashboard</p></a></li>

                    @if(in_array($role,['superadmin','operator']))
                        <li class="nav-header">SUPERADMIN / OPERATOR</li>
                        <li class="nav-item"><a href="{{ route('operator.mitra.index') }}" class="nav-link {{ request()->routeIs('operator.mitra.*') ? 'active' : '' }}"><i class="nav-icon fas fa-users"></i><p>Master Mitra & Seleksi</p></a></li>
                        <li class="nav-item"><a href="{{ route('tugas') }}" class="nav-link {{ request()->routeIs('tugas*') ? 'active' : '' }}"><i class="nav-icon fas fa-user-tie"></i><p>Alokasi Tugas</p></a></li>
                        <li class="nav-item"><a href="{{ route('jadwal') }}" class="nav-link {{ request()->is('jadwal*') ? 'active' : '' }}"><i class="nav-icon fas fa-calendar-alt"></i><p>Jadwal Wawancara</p></a></li>
                        <li class="nav-item"><a href="{{ route('operator.administrasi') }}" class="nav-link {{ request()->routeIs('operator.administrasi*') ? 'active' : '' }}"><i class="nav-icon fas fa-folder-open"></i><p>Administrasi/Penyelenggaraan</p></a></li>
                        <li class="nav-item"><a href="{{ route('operator.pengumuman') }}" class="nav-link {{ request()->routeIs('operator.pengumuman*') ? 'active' : '' }}"><i class="nav-icon fas fa-bullhorn"></i><p>Rekap & Pengumuman</p></a></li>
                    @endif

                    @if($role === 'pewawancara')
                        <li class="nav-header">PEWAWANCARA</li>
                        <li class="nav-item"><a href="{{ route('tugas') }}" class="nav-link {{ request()->routeIs('tugas') ? 'active' : '' }}"><i class="nav-icon fas fa-tasks"></i><p>Alokasi Tugas</p></a></li>
                        <li class="nav-item"><a href="{{ route('jadwal') }}" class="nav-link {{ request()->is('jadwal') ? 'active' : '' }}"><i class="nav-icon fas fa-calendar-alt"></i><p>Jadwal Wawancara</p></a></li>
                        <li class="nav-item"><a href="{{ route('pewawancara.dashboard') }}" class="nav-link {{ request()->routeIs('pewawancara.*') ? 'active' : '' }}"><i class="nav-icon fas fa-star-half-alt"></i><p>Penilaian Wawancara</p></a></li>
                    @endif

                    @if($role === 'superadmin')
                        <li class="nav-header">PENILAIAN</li>
                        <li class="nav-item"><a href="{{ route('pewawancara.dashboard') }}" class="nav-link {{ request()->routeIs('pewawancara.*') ? 'active' : '' }}"><i class="nav-icon fas fa-star-half-alt"></i><p>Penilaian Wawancara</p></a></li>
                    @endif

                    @if($role === 'peserta')
                        <li class="nav-header">PESERTA</li>
                        <li class="nav-item"><a href="{{ route('peserta.undangan') }}" class="nav-link {{ request()->routeIs('peserta.undangan*') ? 'active' : '' }}"><i class="nav-icon fas fa-envelope-open-text"></i><p>Undangan / RSVP</p></a></li>
                        <li class="nav-item"><a href="{{ route('jadwal') }}" class="nav-link {{ request()->is('jadwal') || request()->routeIs('peserta.jadwal*') ? 'active' : '' }}"><i class="nav-icon fas fa-calendar-check"></i><p>Jadwal Saya</p></a></li>
                        <li class="nav-item"><a href="{{ route('administrasi') }}" class="nav-link {{ request()->routeIs('administrasi') ? 'active' : '' }}"><i class="nav-icon fas fa-file-pdf"></i><p>Administrasi (CV)</p></a></li>
                        <li class="nav-item"><a href="{{ route('pengumuman') }}" class="nav-link {{ request()->routeIs('pengumuman') ? 'active' : '' }}"><i class="nav-icon fas fa-bullhorn"></i><p>Pengumuman Hasil</p></a></li>
                    @endif

                    <li class="nav-header">AKUN</li>
                    <li class="nav-item"><a href="{{ route('password.change.form') }}" class="nav-link {{ request()->routeIs('password.change.*') ? 'active' : '' }}"><i class="nav-icon fas fa-key"></i><p>Ganti Password</p></a></li>
                </ul>
            </nav>
            @endauth
        </div>
    </aside>

    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">@yield('content-header')</div>
        </section>
        <section class="content">
            <div class="container-fluid">
                @if (session('status'))
                    <div class="alert alert-success alert-dismissible fade show"><button type="button" class="close" data-dismiss="alert">&times;</button>{{ session('status') }}</div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <div class="font-weight-bold mb-1">Terjadi kesalahan:</div>
                        <ul class="mb-0 pl-3">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif
                @yield('content')
            </div>
        </section>
    </div>

    <footer class="main-footer small">
        <strong>Sistem Rekrutmen Keputusan Kepala BPS Tahun 2027</strong> — BPS Kabupaten Jember
        <div class="float-right d-none d-sm-inline">bpsjember.com</div>
    </footer>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.4/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
@stack('scripts')
</body>
</html>
