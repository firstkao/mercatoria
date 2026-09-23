<?php
/**
 * Query produk, variasi, dan kategori.
 */

require_once __DIR__ . '/db.php';

function product_effective_price(array $produk): array
{
    // harga_jual = harga yang dibayar customer, selalu dipakai.
    // harga_coret = harga pembanding (lebih tinggi), ditampilkan dicoret kalau
    // lebih besar dari harga_jual dan (kalau diisi) masih dalam periode sale_start/sale_end.
    $now = date('Y-m-d H:i:s');
    $hargaJual  = (int) $produk['harga_jual'];
    $hargaCoret = (int) ($produk['harga_coret'] ?? 0);

    $inPeriod = (empty($produk['sale_start']) || $produk['sale_start'] <= $now)
        && (empty($produk['sale_end']) || $produk['sale_end'] >= $now);

    $onSale = $hargaCoret > $hargaJual && $inPeriod;

    return [
        'price'      => $hargaJual,
        'compare_at' => $onSale ? $hargaCoret : null,
        'on_sale'    => $onSale,
    ];
}

/** Daftar produk published, terbaru dulu. */
function product_list(int $limit = 20, int $offset = 0, ?int $kategoriId = null): array
{
    $pdo = get_db();
    $sql = "SELECT * FROM produk WHERE status = 'published'";
    $params = [];

    if ($kategoriId !== null) {
        $sql .= ' AND kategori_id = ?';
        $params[] = $kategoriId;
    }

    $sql .= ' ORDER BY created_at DESC LIMIT ? OFFSET ?';
    $params[] = $limit;
    $params[] = $offset;

    $stmt = $pdo->prepare($sql);
    foreach ($params as $i => $val) {
        $stmt->bindValue($i + 1, $val, PDO::PARAM_INT);
    }
    $stmt->execute();

    return $stmt->fetchAll();
}

function product_find_by_slug(string $slug): ?array
{
    $pdo = get_db();
    $stmt = $pdo->prepare("SELECT * FROM produk WHERE slug = ? AND status = 'published' LIMIT 1");
    $stmt->execute([$slug]);
    $produk = $stmt->fetch();
    return $produk ?: null;
}

function product_variations(int $produkId): array
{
    $pdo = get_db();
    $stmt = $pdo->prepare('SELECT * FROM produk_variasi WHERE produk_id = ? ORDER BY urutan ASC, id ASC');
    $stmt->execute([$produkId]);
    return $stmt->fetchAll();
}

function product_gallery(int $produkId): array
{
    $pdo = get_db();
    $stmt = $pdo->prepare('SELECT * FROM produk_gambar WHERE produk_id = ? ORDER BY urutan ASC, id ASC');
    $stmt->execute([$produkId]);
    return $stmt->fetchAll();
}

function category_list(): array
{
    $pdo = get_db();
    return $pdo->query('SELECT * FROM kategori_produk ORDER BY nama ASC')->fetchAll();
}
