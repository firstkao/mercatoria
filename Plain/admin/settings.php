<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

admin_require_login();

$pdo = get_db();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_method') {
    $id = trim($_POST['id'] ?? '');
    $label = trim($_POST['label'] ?? '');
    $type = in_array($_POST['type'] ?? '', ['bank', 'qris', 'other'], true) ? $_POST['type'] : 'bank';
    $enabled = !empty($_POST['enabled']) ? 1 : 0;
    $accountNumber = trim($_POST['account_number'] ?? '');
    $accountName = trim($_POST['account_name'] ?? '');
    $instructions = trim($_POST['instructions'] ?? '');

    if ($id === '' || $label === '') {
        $message = 'ID dan Label wajib diisi.';
    } else {
        $stmt = $pdo->prepare(
            'INSERT INTO payment_methods (id, label, type, enabled, account_number, account_name, instructions, urutan)
             VALUES (?, ?, ?, ?, ?, ?, ?, 0)
             ON DUPLICATE KEY UPDATE label=VALUES(label), type=VALUES(type), enabled=VALUES(enabled),
                account_number=VALUES(account_number), account_name=VALUES(account_name), instructions=VALUES(instructions)'
        );
        $stmt->execute([$id, $label, $type, $enabled, $accountNumber, $accountName, $instructions]);
        $message = 'Metode pembayaran disimpan.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_method') {
    $pdo->prepare('DELETE FROM payment_methods WHERE id = ?')->execute([$_POST['id'] ?? '']);
    $message = 'Metode pembayaran dihapus.';
}

$methods = $pdo->query('SELECT * FROM payment_methods ORDER BY urutan ASC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pengaturan — Admin Mercatoria</title>
    <link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body>
<?php require __DIR__ . '/_nav.php'; ?>

<h1>Pengaturan Metode Pembayaran</h1>
<?php if ($message): ?><p class="admin-success"><?= h($message) ?></p><?php endif; ?>

<h2>Tambah / Edit Metode</h2>
<form method="post">
    <input type="hidden" name="action" value="save_method">
    <label>ID (unik, huruf kecil, tanpa spasi, mis. "bca") <input type="text" name="id" required></label>
    <label>Label <input type="text" name="label" required></label>
    <label>Tipe
        <select name="type">
            <option value="bank">Transfer Bank</option>
            <option value="qris">QRIS</option>
            <option value="other">Lainnya</option>
        </select>
    </label>
    <label>No. Rekening <input type="text" name="account_number"></label>
    <label>Atas Nama <input type="text" name="account_name"></label>
    <label>Instruksi <textarea name="instructions"></textarea></label>
    <label><input type="checkbox" name="enabled" checked> Aktif</label>
    <button type="submit">Simpan</button>
</form>

<h2>Daftar Metode</h2>
<table class="admin-table">
    <thead><tr><th>ID</th><th>Label</th><th>Tipe</th><th>Aktif</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($methods as $m): ?>
        <tr>
            <td><?= h($m['id']) ?></td>
            <td><?= h($m['label']) ?></td>
            <td><?= h($m['type']) ?></td>
            <td><?= $m['enabled'] ? 'Ya' : 'Tidak' ?></td>
            <td>
                <form method="post" style="display:inline" onsubmit="return confirm('Hapus metode ini?');">
                    <input type="hidden" name="action" value="delete_method">
                    <input type="hidden" name="id" value="<?= h($m['id']) ?>">
                    <button type="submit">Hapus</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</body>
</html>
