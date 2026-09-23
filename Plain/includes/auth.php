<?php
/**
 * Auth untuk panel admin (bukan akun pembeli).
 * Session-based, password di-hash dengan password_hash() (bcrypt).
 */

require_once __DIR__ . '/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
        // Aktifkan baris di bawah kalau sudah pakai HTTPS di production.
        // 'cookie_secure' => true,
    ]);
}

const AUTH_MAX_ATTEMPTS   = 5;   // percobaan gagal sebelum di-lock sementara
const AUTH_LOCKOUT_SECONDS = 300; // 5 menit

/**
 * Coba login admin. Return true kalau sukses, string pesan error kalau gagal.
 */
function admin_attempt_login(string $username, string $password)
{
    $username = trim($username);

    // Rate limit sederhana berbasis session, per-username.
    $key = 'login_attempts_' . $username;
    $attempts = $_SESSION[$key] ?? ['count' => 0, 'locked_until' => 0];

    if ($attempts['locked_until'] > time()) {
        $wait = $attempts['locked_until'] - time();
        return "Terlalu banyak percobaan gagal. Coba lagi dalam {$wait} detik.";
    }

    $pdo = get_db();
    $stmt = $pdo->prepare('SELECT id, username, password_hash, display_name FROM admin_users WHERE username = ? LIMIT 1');
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if (!$admin || !password_verify($password, $admin['password_hash'])) {
        $attempts['count']++;
        if ($attempts['count'] >= AUTH_MAX_ATTEMPTS) {
            $attempts['locked_until'] = time() + AUTH_LOCKOUT_SECONDS;
            $attempts['count'] = 0;
        }
        $_SESSION[$key] = $attempts;
        return 'Username atau password salah.';
    }

    unset($_SESSION[$key]);

    // Regenerate session id setelah login sukses, mencegah session fixation.
    session_regenerate_id(true);
    $_SESSION['admin_id']       = (int) $admin['id'];
    $_SESSION['admin_username'] = $admin['username'];
    $_SESSION['admin_name']     = $admin['display_name'] ?: $admin['username'];

    return true;
}

function admin_is_logged_in(): bool
{
    return !empty($_SESSION['admin_id']);
}

/**
 * Panggil di baris pertama setiap file di admin/ (kecuali index.php/login)
 * untuk memaksa login.
 */
function admin_require_login(): void
{
    if (!admin_is_logged_in()) {
        header('Location: /admin/index.php');
        exit;
    }
}

function admin_logout(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie('PHPSESSID', '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}

/**
 * Helper sekali pakai untuk membuat admin pertama lewat CLI:
 * php -r "require 'includes/auth.php'; admin_create('kaoadmin', 'password-kuat-disini');"
 */
function admin_create(string $username, string $password, string $displayName = ''): void
{
    $pdo = get_db();
    $stmt = $pdo->prepare(
        'INSERT INTO admin_users (username, password_hash, display_name, created_at, updated_at)
         VALUES (?, ?, ?, NOW(), NOW())'
    );
    $stmt->execute([$username, password_hash($password, PASSWORD_DEFAULT), $displayName]);
}
