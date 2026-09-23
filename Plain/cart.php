<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/cart.php';

$cart = cart_get_full();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Keranjang — Mercatoria</title>
    <link rel="stylesheet" href="/assets/css/base.css">
</head>
<body>
<h1>Keranjang Belanja</h1>

<?php if (empty($cart['items'])): ?>
    <p>Keranjang kamu masih kosong. <a href="/index.php">Belanja sekarang</a></p>
<?php else: ?>
    <table class="cart-table">
        <thead>
        <tr>
            <th>Produk</th>
            <th>Harga</th>
            <th>Qty</th>
            <th>Subtotal</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($cart['items'] as $item): ?>
            <tr data-key="<?= h($item['key']) ?>">
                <td>
                    <img src="/uploads/products/<?= h($item['gambar']) ?>" alt="" width="60">
                    <a href="/produk.php?slug=<?= h($item['slug']) ?>"><?= h($item['produk_nama']) ?></a>
                    <?php if ($item['variasi_label']): ?>
                        <br><small><?= h($item['variasi_label']) ?></small>
                    <?php endif; ?>
                    <?php if ($item['is_oos']): ?>
                        <br><small style="color:red;">Stok habis</small>
                    <?php endif; ?>
                </td>
                <td><?= format_rupiah($item['harga']) ?></td>
                <td>
                    <input type="number" class="cart-qty" min="1" value="<?= (int) $item['qty'] ?>" data-key="<?= h($item['key']) ?>">
                </td>
                <td><?= format_rupiah($item['subtotal']) ?></td>
                <td><button class="cart-remove" data-key="<?= h($item['key']) ?>">Hapus</button></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <div class="cart-summary">
        <p>Subtotal: <strong><?= format_rupiah($cart['subtotal']) ?></strong></p>
        <a href="/checkout.php" class="btn-checkout">Lanjut ke Checkout</a>
    </div>
<?php endif; ?>

<script src="/assets/js/cart.js"></script>
</body>
</html>
