<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Masuk - Admin MERCATORIA</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}?v=4">
</head>
<body class="login-page">
    <main class="login">
        <p class="login__brand">MERCATORIA <span>Admin</span></p>
        <form method="POST" action="{{ route('admin.login') }}" class="panel stack" novalidate>
            @csrf
            <h1>Masuk</h1>

            <label class="field">
                <span>Email</span>
                <input type="email" name="email" value="{{ old('email') }}" autocomplete="username" required autofocus>
                @include('admin.partials.error', ['name' => 'email'])
            </label>

            <label class="field">
                <span>Kata sandi</span>
                <input type="password" name="password" autocomplete="current-password" required>
                @include('admin.partials.error', ['name' => 'password'])
            </label>

            <label class="check">
                <input type="checkbox" name="remember" value="1">
                <span>Ingat saya</span>
            </label>

            <div class="cf-turnstile" data-sitekey="{{ config('services.turnstile.site_key') }}" data-language="id"></div>
            @include('admin.partials.error', ['name' => 'cf-turnstile-response'])

            <button type="submit" class="btn btn--primary btn--block">Masuk</button>
        </form>
    </main>
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
</body>
</html>