<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/product.php';

$slug = $_GET['slug'] ?? '';
$produk = $slug !== '' ? product_find_by_slug($slug) : null;

if (!$produk) {
    http_response_code(404);
    echo '<h1>Produk tidak ditemukan</h1>';
    exit;
}

$harga = product_effective_price($produk);
$variasi = product_variations($produk['id']);
$galeri = product_gallery($produk['id']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= h($produk['nama']) ?> — Mercatoria</title>
    <link rel="stylesheet" href="/assets/css/base.css">
</head>
<body>
<h1><?= h($produk['nama']) ?></h1>
<img src="/uploads/products/<?= h($produk['gambar_utama']) ?>" alt="<?= h($produk['nama']) ?>">

<div class="harga">
    <?php if ($harga['on_sale']): ?>
        <span class="coret"><?= format_rupiah($harga['compare_at']) ?></span>
    <?php endif; ?>
    <span class="jual"><?= format_rupiah($harga['price']) ?></span>
</div>

<p><?= nl2br(h($produk['deskripsi'])) ?></p>

<?php if (!empty($variasi)): ?>
    <form method="post" action="/includes/cart-ajax.php">
        <input type="hidden" name="produk_id" value="<?= (int) $produk['id'] ?>">
        <label for="variasi">Pilih Varian</label>
        <select name="variasi_id" id="variasi">
            <?php foreach ($variasi as $v): ?>
                <option value="<?= (int) $v['id'] ?>">
                    <?= h($v['label']) ?>
                    <?php if ($v['harga_override']): ?>
                        (<?= format_rupiah((int) $v['harga_override']) ?>)
                    <?php endif; ?>
                    <?= $v['stok'] <= 0 ? ' - Habis' : '' ?>
                </option>
            <?php endforeach; ?>
        </select>
        <input type="number" name="qty" value="1" min="1">
        <button type="submit">Tambah ke Keranjang</button>
    </form>
<?php elseif ($produk['status_stok'] === 'instock'): ?>
    <form method="post" action="/includes/cart-ajax.php">
        <input type="hidden" name="produk_id" value="<?= (int) $produk['id'] ?>">
        <input type="number" name="qty" value="1" min="1">
        <button type="submit">Tambah ke Keranjang</button>
    </form>
<?php else: ?>
    <p>Stok habis.</p>
<?php endif; ?>

<?php if (!empty($galeri)): ?>
    <div class="galeri">
        <?php foreach ($galeri as $g): ?>
            <img src="/uploads/products/<?= h($g['file_path']) ?>" alt="">
        <?php endforeach; ?>
    </div>
<?php endif; ?>
</body>
</html>
