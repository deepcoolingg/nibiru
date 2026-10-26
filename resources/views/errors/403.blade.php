<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 – Akses Ditolak</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #0f172a;
            color: #e2e8f0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card {
            background-color: #1e293b;
            border: 1px solid #334155;
            border-radius: 1rem;
            padding: 2.5rem;
            max-width: 420px;
            width: 90%;
            text-align: center;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);
        }
        .icon-wrap {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 4rem;
            height: 4rem;
            background-color: rgba(239, 68, 68, 0.15);
            border-radius: 50%;
            margin: 0 auto 1.5rem;
        }
        .icon-wrap svg {
            width: 2rem;
            height: 2rem;
            color: #ef4444;
        }
        .code {
            font-size: 0.875rem;
            font-weight: 600;
            color: #ef4444;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            margin-bottom: 0.5rem;
        }
        h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #f1f5f9;
            margin-bottom: 0.75rem;
        }
        p {
            font-size: 0.9rem;
            color: #94a3b8;
            line-height: 1.6;
            margin-bottom: 2rem;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            font-family: 'Poppins', sans-serif;
            font-size: 0.875rem;
            font-weight: 600;
            color: #0f172a;
            background-color: #10b981;
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.2s;
            width: 100%;
        }
        .btn:hover { background-color: #059669; }
        .btn svg { width: 1rem; height: 1rem; }
        .divider {
            border-top: 1px solid #334155;
            margin: 1.5rem 0;
        }
        .user-info {
            font-size: 0.75rem;
            color: #64748b;
        }
    </style>
</head>
<body>
    @php
        try {
            $logoutUrl = route('filament.nibiru.auth.logout');
            $loginUrl  = route('filament.nibiru.auth.login');
        } catch (\Exception $e) {
            $logoutUrl = '/nibiru/logout';
            $loginUrl  = '/nibiru/login';
        }
        $message = isset($exception) ? $exception->getMessage() : 'Anda tidak memiliki izin untuk mengakses halaman ini.';
    @endphp

    <div class="card">
        <div class="icon-wrap">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
            </svg>
        </div>

        <div class="code">Error 403</div>
        <h1>Akses Ditolak</h1>
        <p>{{ $message }}</p>

        @auth
            <form method="POST" action="{{ $logoutUrl }}">
                @csrf
                <button type="submit" class="btn">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9"/>
                    </svg>
                    Kembali ke Halaman Login
                </button>
            </form>

            <div class="divider"></div>
            <p class="user-info">Login sebagai: {{ auth()->user()->email }}</p>
        @else
            <a href="{{ $loginUrl }}" class="btn">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3"/>
                </svg>
                Kembali ke Halaman Login
            </a>
        @endauth
    </div>
</body>
</html>
