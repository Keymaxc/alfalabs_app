<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login | Alfalabs</title>
    <link rel="icon" href="{{ asset('template/assets/img/kaiadmin/favicon.ico') }}" type="image/x-icon" />
    <link rel="stylesheet" href="{{ asset('template/assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('template/assets/css/kaiadmin.min.css') }}">
    <style>
        body {
            background: radial-gradient(circle at 20% 20%, #e4edff, #f7f9ff 45%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Public Sans', Arial, sans-serif;
        }
        .auth-card {
            border: 1px solid #e5e7f0;
            box-shadow: 0 20px 50px rgba(21,114,232,0.08);
        }
        .brand-logo {
            width: 56px;
            height: 56px;
            border-radius: 14px;
            background: #fff;
            box-shadow: 0 10px 30px rgba(21,114,232,0.12);
            display: grid;
            place-items: center;
        }
        .brand-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        .btn-primary {
            background: linear-gradient(135deg, #1572e8, #125dcc);
            border: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-5 col-md-7">
                <div class="card auth-card rounded-4">
                    <div class="card-body p-4 p-md-5">
                        <div class="d-flex align-items-center mb-4">
                            <div class="brand-logo me-3">
                                <img src="{{ asset('template/assets/img/alfa-labs-logo.png') }}" alt="Alfa Labs">
                            </div>
                            <div>
                                <h5 class="mb-0 fw-bold">Alfalabs Panel</h5>
                                <small class="text-muted">Masuk untuk melanjutkan</small>
                            </div>
                        </div>

                        @if ($errors->any())
                            <div class="alert alert-danger py-2">
                                <ul class="mb-0 ps-3">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('login') }}">
                            @csrf
                            <div class="mb-3">
                                <label for="email" class="form-label fw-semibold">Email</label>
                                <input
                                    id="email"
                                    type="email"
                                    class="form-control form-control-lg @error('email') is-invalid @enderror"
                                    name="email"
                                    value="{{ old('email') }}"
                                    required
                                    autofocus
                                    placeholder="email@domain.com"
                                >
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <label for="password" class="form-label fw-semibold mb-0">Password</label>
                                    @if (Route::has('password.request'))
                                        <a class="small text-primary" href="{{ route('password.request') }}">Lupa password?</a>
                                    @endif
                                </div>
                                <input
                                    id="password"
                                    type="password"
                                    class="form-control form-control-lg @error('password') is-invalid @enderror"
                                    name="password"
                                    required
                                    autocomplete="current-password"
                                    placeholder="••••••••"
                                >
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                <label class="form-check-label" for="remember">Ingat saya</label>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg rounded-pill">
                                    Masuk
                                </button>
                            </div>
                        </form>
                        <p class="text-center text-muted mt-3 mb-0" style="font-size: 0.9rem;">
                            Registrasi akun admin/staff? <a href="{{ route('register') }}" class="text-primary">Registrasi</a>
                        </p>
                    </div>
                </div>
                <p class="text-center text-muted mt-3 mb-0" style="font-size: 0.9rem;">
                    &copy; {{ date('Y') }} Alfalabs
                </p>
            </div>
        </div>
    </div>

    <script src="{{ asset('template/assets/js/core/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('template/assets/js/core/bootstrap.min.js') }}"></script>
</body>
</html>
