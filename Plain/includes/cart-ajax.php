<?php
/** Endpoint untuk tambah/update/hapus item cart. Menerima POST, balas JSON. */

header('Content-Type: application/json');
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/cart.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'Method tidak diizinkan.']);
    exit;
}

$action = $_POST['action'] ?? 'add';

if ($action === 'add') {
    $produkId  = (int) ($_POST['produk_id'] ?? 0);
    $qty       = (int) ($_POST['qty'] ?? 1);
    $variasiId = isset($_POST['variasi_id']) && $_POST['variasi_id'] !== ''
        ? (int) $_POST['variasi_id']
        : null;

    $result = cart_add($produkId, $qty, $variasiId);
} elseif ($action === 'update') {
    $key = $_POST['key'] ?? '';
    $qty = (int) ($_POST['qty'] ?? 1);
    $result = cart_update_qty($key, $qty);
} elseif ($action === 'remove') {
    $key = $_POST['key'] ?? '';
    $result = cart_remove($key);
} else {
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => 'Aksi tidak dikenal.']);
    exit;
}

if ($result !== true) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'message' => $result]);
    exit;
}

$cart = cart_get_full();
echo json_encode([
    'ok'    => true,
    'count' => $cart['count'],
    'subtotal' => $cart['subtotal'],
    'subtotal_formatted' => format_rupiah($cart['subtotal']),
]);
