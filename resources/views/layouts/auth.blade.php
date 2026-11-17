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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/icheck-bootstrap@3.0.1/icheck-bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <style>
        h1, h2, h3, h4, h5, h6, .login-logo { font-family: 'Plus Jakarta Sans', 'Source Sans Pro', sans-serif; }
        .siap-tagline { color: #1E88E5; }
        .siap-closed {
            font-size: .78rem; color: #7A5A00; background: #FFF4D6; border: 1px solid #F2DE9B;
            border-radius: 8px; padding: 6px 8px; margin-top: 10px;
        }
    </style>
    @stack('styles')
</head>
<body class="hold-transition login-page">
<div class="login-box" style="width:420px;max-width:95vw">
    <div class="login-logo">
        <a href="{{ url('/') }}" class="d-inline-block">
            <img src="{{ asset('img/brand/siap-logo.svg') }}" alt="Logo SIAP Jember" style="width:64px;height:64px">
        </a>
        <div style="font-size:1.35rem;font-weight:800;color:#0A4D8C">SIAP <b>Jember</b></div>
        <div class="siap-tagline" style="font-size:.85rem;font-weight:600">Siap Menjadi Mitra Statistik Terbaik</div>
        <div class="siap-closed">Portal tertutup — akses hanya untuk peserta &amp; panitia terdaftar</div>
    </div>

    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show"><button type="button" class="close" data-dismiss="alert">&times;</button>{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <ul class="mb-0 pl-3">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="card card-outline card-primary">
        <div class="card-body login-card-body">
            @yield('content')
        </div>
    </div>
    <div class="text-center text-muted small mt-2">SIAP Jember — Sistem Informasi Administrasi &amp; Penilaian Mitra Statistik<br>&copy; BPS Kabupaten Jember · bpsjember.com</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.4/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
@stack('scripts')
</body>
</html>
