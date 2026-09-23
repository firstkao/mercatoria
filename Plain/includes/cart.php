<?php
/**
 * Cart berbasis token cookie. Isi cart (items) disimpan sebagai JSON
 * di tabel `carts`. Harga selalu dihitung ulang live dari tabel produk,
 * bukan disimpan statis di cart, biar harga selalu akurat.
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/product.php';

function cart_get_token(): string
{
    if (!empty($_COOKIE['merc_cart_token']) && preg_match('/^[a-f0-9]{32}$/', $_COOKIE['merc_cart_token'])) {
        return $_COOKIE['merc_cart_token'];
    }

    $token = bin2hex(random_bytes(16));
    if (!headers_sent()) {
        setcookie('merc_cart_token', $token, time() + 30 * 24 * 3600, '/', '', false, true);
    }
    $_COOKIE['merc_cart_token'] = $token;

    return $token;
}

function cart_get_row(bool $create = true): ?array
{
    $pdo = get_db();
    $token = cart_get_token();
    $stmt = $pdo->prepare('SELECT * FROM carts WHERE cart_token = ? LIMIT 1');
    $stmt->execute([$token]);
    $row = $stmt->fetch();

    if (!$row && $create) {
        $stmt = $pdo->prepare(
            'INSERT INTO carts (cart_token, items, created_at, updated_at) VALUES (?, ?, NOW(), NOW())'
        );
        $stmt->execute([$token, json_encode([])]);

        $stmt = $pdo->prepare('SELECT * FROM carts WHERE cart_token = ? LIMIT 1');
        $stmt->execute([$token]);
        $row = $stmt->fetch();
    }

    return $row ?: null;
}

function cart_get_items(): array
{
    $cart = cart_get_row(false);
    if (!$cart) return [];

    $items = json_decode($cart['items'], true);
    return is_array($items) ? $items : [];
}

function cart_save_items(array $items): bool
{
    $cart = cart_get_row(true);
    if (!$cart) return false;

    $pdo = get_db();
    $stmt = $pdo->prepare('UPDATE carts SET items = ?, updated_at = NOW() WHERE id = ?');
    $stmt->execute([json_encode(array_values($items)), $cart['id']]);

    return true;
}

/** Satu baris cart diidentifikasi oleh kombinasi produk + variasi. */
function cart_line_key(int $produkId, ?int $variasiId): string
{
    return $produkId . ':' . ($variasiId === null ? 'x' : $variasiId);
}

/**
 * Tambah item ke cart. Return true kalau sukses, string pesan error kalau gagal.
 */
function cart_add(int $produkId, int $qty = 1, ?int $variasiId = null)
{
    $qty = max(1, $qty);

    $pdo = get_db();
    $stmt = $pdo->prepare("SELECT * FROM produk WHERE id = ? AND status = 'published' LIMIT 1");
    $stmt->execute([$produkId]);
    $produk = $stmt->fetch();

    if (!$produk) return 'Produk tidak ditemukan.';
    if ($produk['status_stok'] === 'outofstock') return 'Stok produk ini habis.';

    $variasi = null;
    $variations = product_variations($produkId);

    if (!empty($variations)) {
        if ($variasiId === null) return 'Produk ini butuh pilihan varian.';

        foreach ($variations as $v) {
            if ((int) $v['id'] === $variasiId) {
                $variasi = $v;
                break;
            }
        }
        if (!$variasi) return 'Varian yang dipilih tidak valid.';
        if ((int) $variasi['stok'] <= 0) return 'Stok varian ini habis.';
        if ((int) $variasi['stok'] < $qty) return 'Stok varian tidak cukup. Sisa: ' . (int) $variasi['stok'];
    } else {
        $variasiId = null;
    }

    $items = cart_get_items();
    $key = cart_line_key($produkId, $variasiId);

    $found = false;
    foreach ($items as &$item) {
        if ($item['key'] === $key) {
            $item['qty'] = (int) $item['qty'] + $qty;
            $found = true;
            break;
        }
    }
    unset($item);

    if (!$found) {
        $items[] = [
            'key'        => $key,
            'produk_id'  => $produkId,
            'variasi_id' => $variasiId,
            'qty'        => $qty,
        ];
    }

    cart_save_items($items);
    return true;
}

function cart_update_qty(string $lineKey, int $qty)
{
    $qty = max(1, $qty);
    $items = cart_get_items();

    foreach ($items as &$item) {
        if ($item['key'] === $lineKey) {
            $item['qty'] = $qty;
            unset($item);
            cart_save_items($items);
            return true;
        }
    }
    unset($item);

    return 'Baris cart tidak ditemukan.';
}

function cart_remove(string $lineKey)
{
    $items = cart_get_items();
    $new = array_values(array_filter($items, fn($i) => $i['key'] !== $lineKey));

    if (count($new) === count($items)) {
        return 'Baris cart tidak ditemukan.';
    }

    cart_save_items($new);
    return true;
}

function cart_clear(): bool
{
    return cart_save_items([]);
}

/** Ambil 1 baris cart dengan data produk terkini (harga live). */
function cart_resolve_line(array $item): ?array
{
    $pdo = get_db();
    $stmt = $pdo->prepare("SELECT * FROM produk WHERE id = ? AND status = 'published' LIMIT 1");
    $stmt->execute([$item['produk_id']]);
    $produk = $stmt->fetch();
    if (!$produk) return null;

    $harga = 0;
    $variasiLabel = '';
    $gambar = $produk['gambar_utama'];

    if (!empty($item['variasi_id'])) {
        $stmt = $pdo->prepare('SELECT * FROM produk_variasi WHERE id = ? LIMIT 1');
        $stmt->execute([$item['variasi_id']]);
        $variasi = $stmt->fetch();
        if (!$variasi) return null;

        $harga = $variasi['harga_override'] !== null
            ? (int) $variasi['harga_override']
            : product_effective_price($produk)['price'];
        $variasiLabel = $variasi['label'];
    } else {
        $harga = product_effective_price($produk)['price'];
    }

    $qty = (int) $item['qty'];

    return [
        'key'          => $item['key'],
        'produk_id'    => (int) $produk['id'],
        'produk_nama'  => $produk['nama'],
        'slug'         => $produk['slug'],
        'variasi_id'   => $item['variasi_id'] ? (int) $item['variasi_id'] : null,
        'variasi_label'=> $variasiLabel,
        'qty'          => $qty,
        'harga'        => $harga,
        'subtotal'     => $harga * $qty,
        'gambar'       => $gambar,
        'is_oos'       => $produk['status_stok'] === 'outofstock',
        'sku'          => $produk['sku'],
    ];
}

function cart_get_full(): array
{
    $lines = [];
    foreach (cart_get_items() as $item) {
        $line = cart_resolve_line($item);
        if ($line) $lines[] = $line;
    }

    $subtotal = 0;
    $count = 0;
    foreach ($lines as $l) {
        $subtotal += $l['subtotal'];
        $count += $l['qty'];
    }

    return ['items' => $lines, 'subtotal' => $subtotal, 'count' => $count];
}
