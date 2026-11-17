@extends('layouts.auth')

@section('content')
<p class="login-box-msg">Silakan masuk dengan email terdaftar Anda</p>
<form method="POST" action="{{ route('login.store') }}">
    @csrf
    <div class="input-group mb-3">
        <input type="email" name="email" value="{{ old('email') }}" class="form-control" placeholder="Email" required autofocus>
        <div class="input-group-append"><div class="input-group-text"><span class="fas fa-envelope"></span></div></div>
    </div>
    <div class="input-group mb-3">
        <input type="password" name="password" class="form-control" placeholder="Password" required>
        <div class="input-group-append"><div class="input-group-text"><span class="fas fa-lock"></span></div></div>
    </div>
    <div class="row mb-2">
        <div class="col-7">
            <div class="icheck-primary">
                <input type="checkbox" id="remember" name="remember">
                <label for="remember">Ingat saya</label>
            </div>
        </div>
        <div class="col-5">
            <button type="submit" class="btn btn-primary btn-block">Login</button>
        </div>
    </div>
</form>
<p class="mb-1"><a href="{{ route('password.request') }}">Lupa password?</a></p>
@endsection
