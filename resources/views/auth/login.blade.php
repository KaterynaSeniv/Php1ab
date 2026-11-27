@extends('layouts.app')

@section('title', 'Вхід у систему')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-lg border-0">
            <div class="card-header bg-primary text-white">
                <h1 class="h4 mb-0">Вхід у систему</h1>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" required autofocus>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Пароль</label>
                        <input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-check mb-4">
                        <input id="remember" type="checkbox" name="remember" class="form-check-input">
                        <label for="remember" class="form-check-label">Запам'ятати мене</label>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Увійти</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
