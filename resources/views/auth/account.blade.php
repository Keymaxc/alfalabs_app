<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Akun Saya | Alfalabs</title>
    <link rel="icon" href="{{ asset('template/assets/img/kaiadmin/favicon.ico') }}" type="image/x-icon" />
    <link rel="stylesheet" href="{{ asset('template/assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('template/assets/css/kaiadmin.min.css') }}">
    <style>
        body {
            background: #f5f7fd;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Public Sans', Arial, sans-serif;
        }
        .profile-card {
            border: 1px solid #e5e7f0;
            box-shadow: 0 10px 30px rgba(21,114,232,0.08);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8">
                <div class="card profile-card rounded-4">
                    <div class="card-body p-4 p-md-5">
                        <div class="d-flex align-items-center mb-4">
                            <div class="brand-circle me-3">AL</div>
                            <div>
                                <h5 class="mb-0 fw-bold">Akun Saya</h5>
                                <small class="text-muted">{{ auth()->user()->email ?? '' }}</small>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nama</label>
                            <input type="text" class="form-control form-control-lg" value="{{ auth()->user()->name ?? '' }}" disabled>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="text" class="form-control form-control-lg" value="{{ auth()->user()->email ?? '' }}" disabled>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ url('/home') }}" class="btn btn-outline-secondary rounded-pill px-3">Kembali</a>
                            <a href="{{ route('logout') }}" class="btn btn-danger rounded-pill px-3" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
                        </div>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="{{ asset('template/assets/js/core/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('template/assets/js/core/bootstrap.min.js') }}"></script>
</body>
</html>
