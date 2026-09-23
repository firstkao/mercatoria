<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/product.php';

$produkList = product_list(12);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Mercatoria</title>
    <link rel="stylesheet" href="/assets/css/base.css">
</head>
<body>
<h1>Produk Terbaru</h1>
<div class="produk-grid">
    <?php if (empty($produkList)): ?>
        <p>Belum ada produk.</p>
    <?php endif; ?>
    <?php foreach ($produkList as $produk): ?>
        <?php $harga = product_effective_price($produk); ?>
        <a class="produk-card" href="/produk.php?slug=<?= h($produk['slug']) ?>">
            <img src="/uploads/products/<?= h($produk['gambar_utama']) ?>" alt="<?= h($produk['nama']) ?>">
            <h3><?= h($produk['nama']) ?></h3>
            <div class="harga">
                <?php if ($harga['on_sale']): ?>
                    <span class="coret"><?= format_rupiah($harga['compare_at']) ?></span>
                <?php endif; ?>
                <span class="jual"><?= format_rupiah($harga['price']) ?></span>
            </div>
        </a>
    <?php endforeach; ?>
</div>
</body>
</html>
