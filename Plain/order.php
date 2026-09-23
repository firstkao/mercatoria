<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/order.php';

$orderNumber = $_GET['order'] ?? '';
$token = $_GET['token'] ?? '';

$order = ($orderNumber && $token) ? order_get_by_number_token($orderNumber, $token) : null;

if (!$order) {
    http_response_code(404);
    echo '<h1>Order tidak ditemukan</h1>';
    exit;
}

$items = order_items((int) $order['id']);
$paymentMethod = payment_method_get($order['payment_method']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Order <?= h($order['order_number']) ?> — Mercatoria</title>
    <link rel="stylesheet" href="/assets/css/base.css">
</head>
<body>
<h1>Terima kasih, <?= h($order['customer_name']) ?>!</h1>
<p>Nomor Order: <strong><?= h($order['order_number']) ?></strong></p>
<p>Status: <strong><?= h(order_status_label($order['status'])) ?></strong></p>

<h2>Detail Pesanan</h2>
<ul>
    <?php foreach ($items as $item): ?>
        <li>
            <?= h($item['produk_nama']) ?>
            <?php if ($item['variasi_label']): ?> (<?= h($item['variasi_label']) ?>)<?php endif; ?>
            x<?= (int) $item['qty'] ?> — <?= format_rupiah((int) $item['subtotal']) ?>
        </li>
    <?php endforeach; ?>
</ul>
<p>Subtotal: <?= format_rupiah((int) $order['subtotal']) ?></p>
<p>Ongkir: <?= format_rupiah((int) $order['ongkir']) ?></p>
<p>Total: <strong><?= format_rupiah((int) $order['total']) ?></strong></p>

<?php if ($paymentMethod): ?>
    <h2>Instruksi Pembayaran</h2>
    <p><?= h($paymentMethod['label']) ?></p>
    <?php if ($paymentMethod['account_number']): ?>
        <p>No. Rekening: <?= h($paymentMethod['account_number']) ?> a.n. <?= h($paymentMethod['account_name']) ?></p>
    <?php endif; ?>
    <p><?= nl2br(h($paymentMethod['instructions'])) ?></p>
<?php endif; ?>

<p>Simpan link halaman ini untuk cek status order kamu kapan saja.</p>
</body>
</html>
