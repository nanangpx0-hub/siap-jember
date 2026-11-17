@extends('layouts.auth')

@section('content')
<p class="login-box-msg">Reset Password</p>
<form method="POST" action="{{ route('password.update') }}">
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">
    <div class="input-group mb-3">
        <input type="email" name="email" value="{{ old('email', $email) }}" class="form-control" placeholder="Email" required>
        <div class="input-group-append"><div class="input-group-text"><span class="fas fa-envelope"></span></div></div>
    </div>
    <div class="input-group mb-3">
        <input type="password" name="password" class="form-control" placeholder="Password baru" required>
        <div class="input-group-append"><div class="input-group-text"><span class="fas fa-lock"></span></div></div>
    </div>
    <div class="input-group mb-3">
        <input type="password" name="password_confirmation" class="form-control" placeholder="Konfirmasi password baru" required>
        <div class="input-group-append"><div class="input-group-text"><span class="fas fa-lock"></span></div></div>
    </div>
    <button type="submit" class="btn btn-primary btn-block">Reset Password</button>
</form>
@endsection
