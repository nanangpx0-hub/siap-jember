@extends('layouts.auth')

@section('content')
<p class="login-box-msg">Ganti Password</p>
@if($forced)
    <div class="alert alert-warning"><i class="fas fa-exclamation-triangle mr-1"></i>Ini adalah login pertama Anda. Anda wajib mengganti password sebelum melanjutkan.</div>
@endif
<form method="POST" action="{{ route('password.change.update') }}">
    @csrf
    @unless($forced)
    <div class="input-group mb-3">
        <input type="password" name="current_password" class="form-control" placeholder="Password saat ini" required>
        <div class="input-group-append"><div class="input-group-text"><span class="fas fa-lock"></span></div></div>
    </div>
    @endunless
    <div class="input-group mb-3">
        <input type="password" name="password" class="form-control" placeholder="Password baru" required>
        <div class="input-group-append"><div class="input-group-text"><span class="fas fa-key"></span></div></div>
    </div>
    <div class="input-group mb-3">
        <input type="password" name="password_confirmation" class="form-control" placeholder="Konfirmasi password baru" required>
        <div class="input-group-append"><div class="input-group-text"><span class="fas fa-key"></span></div></div>
    </div>
    <button type="submit" class="btn btn-primary btn-block">Simpan Password</button>
</form>
@unless($forced)
<p class="mt-3 mb-0 text-center"><a href="{{ route('dashboard') }}">Kembali ke dashboard</a></p>
@endunless
@endsection
