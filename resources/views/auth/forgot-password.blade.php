@extends('layouts.auth')

@section('content')
<p class="login-box-msg">Lupa password — masukkan email untuk menerima tautan reset.</p>
<form method="POST" action="{{ route('password.email') }}">
    @csrf
    <div class="input-group mb-3">
        <input type="email" name="email" value="{{ old('email') }}" class="form-control" placeholder="Email" required autofocus>
        <div class="input-group-append"><div class="input-group-text"><span class="fas fa-envelope"></span></div></div>
    </div>
    <button type="submit" class="btn btn-primary btn-block mb-2">Kirim Tautan Reset</button>
</form>
<p class="mb-0 text-center"><a href="{{ route('login') }}">Kembali ke login</a></p>
@endsection
