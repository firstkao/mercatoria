-- =============================================================
-- Mercatoria (Plain PHP) — Skema Database, Fase 1
-- Jalankan sekali di database kosong. Charset utf8mb4 untuk emoji/unicode aman.
-- =============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- -------------------------------------------------------------
-- Admin (login panel admin, terpisah dari user pembeli)
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS admin_users (
    id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    username      VARCHAR(60)  NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    display_name  VARCHAR(120) DEFAULT '',
    created_at    DATETIME NOT NULL,
    updated_at    DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------------
-- Kategori produk
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS kategori_produk (
    id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    nama       VARCHAR(120) NOT NULL,
    slug       VARCHAR(140) NOT NULL,
    created_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------------
-- Produk
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS produk (
    id                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    kategori_id       BIGINT UNSIGNED DEFAULT NULL,
    nama              VARCHAR(255) NOT NULL,
    slug              VARCHAR(280) NOT NULL,
    deskripsi         TEXT,
    sku               VARCHAR(80)  DEFAULT '',

    -- Harga (satuan rupiah, integer biar tidak ada masalah floating point)
    harga_modal_yuan  DECIMAL(10,2) DEFAULT NULL,
    harga_jual        BIGINT UNSIGNED NOT NULL DEFAULT 0,
    harga_coret       BIGINT UNSIGNED DEFAULT NULL,
    sale_start        DATETIME DEFAULT NULL,
    sale_end          DATETIME DEFAULT NULL,

    -- Stok & pengiriman
    status_stok       ENUM('instock','outofstock') NOT NULL DEFAULT 'instock',
    berat_gram        INT UNSIGNED DEFAULT 0,
    level_ongkir      VARCHAR(40) DEFAULT '',

    -- Gambar utama (relatif ke /uploads/products/)
    gambar_utama      VARCHAR(255) DEFAULT '',

    status            ENUM('draft','published','archived') NOT NULL DEFAULT 'draft',
    created_at        DATETIME NOT NULL,
    updated_at        DATETIME NOT NULL,

    PRIMARY KEY (id),
    UNIQUE KEY slug (slug),
    KEY kategori_id (kategori_id),
    KEY status (status),
    CONSTRAINT fk_produk_kategori
        FOREIGN KEY (kategori_id) REFERENCES kategori_produk (id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Galeri gambar tambahan per produk
CREATE TABLE IF NOT EXISTS produk_gambar (
    id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    produk_id  BIGINT UNSIGNED NOT NULL,
    file_path  VARCHAR(255) NOT NULL,
    urutan     INT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    KEY produk_id (produk_id),
    CONSTRAINT fk_gambar_produk
        FOREIGN KEY (produk_id) REFERENCES produk (id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Variasi produk (misal ukuran/warna), harga_override NULL = pakai harga_jual induk
CREATE TABLE IF NOT EXISTS produk_variasi (
    id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    produk_id       BIGINT UNSIGNED NOT NULL,
    label           VARCHAR(120) NOT NULL,
    sku             VARCHAR(80) DEFAULT '',
    harga_override  BIGINT UNSIGNED DEFAULT NULL,
    stok            INT UNSIGNED NOT NULL DEFAULT 0,
    urutan          INT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    KEY produk_id (produk_id),
    CONSTRAINT fk_variasi_produk
        FOREIGN KEY (produk_id) REFERENCES produk (id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------------
-- Cart (satu baris per pengunjung, items disimpan sebagai JSON)
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS carts (
    id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    cart_token  VARCHAR(64) NOT NULL,
    user_id     BIGINT UNSIGNED DEFAULT NULL,
    items       LONGTEXT,
    created_at  DATETIME NOT NULL,
    updated_at  DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY cart_token (cart_token),
    KEY user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------------
-- Orders
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS orders (
    id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    order_number    VARCHAR(32) NOT NULL,
    order_token     VARCHAR(64) NOT NULL,
    user_id         BIGINT UNSIGNED DEFAULT NULL,

    customer_name   VARCHAR(120) NOT NULL,
    customer_email  VARCHAR(160) NOT NULL,
    customer_phone  VARCHAR(32)  NOT NULL,
    address_line1   VARCHAR(255) NOT NULL,
    address_line2   VARCHAR(255) DEFAULT '',
    city            VARCHAR(80)  NOT NULL,
    province        VARCHAR(80)  NOT NULL,
    postal_code     VARCHAR(16)  DEFAULT '',
    notes           TEXT,

    subtotal        BIGINT UNSIGNED NOT NULL DEFAULT 0,
    ongkir          BIGINT UNSIGNED NOT NULL DEFAULT 0,
    ongkir_label    VARCHAR(80) DEFAULT '',
    total           BIGINT UNSIGNED NOT NULL DEFAULT 0,

    payment_method  VARCHAR(32) DEFAULT '',
    status          ENUM(
                        'pending_payment',
                        'waiting_verify',
                        'paid',
                        'processing',
                        'shipped',
                        'completed',
                        'cancelled',
                        'refunded'
                    ) NOT NULL DEFAULT 'pending_payment',

    created_at      DATETIME NOT NULL,
    updated_at      DATETIME NOT NULL,

    PRIMARY KEY (id),
    UNIQUE KEY order_number (order_number),
    UNIQUE KEY order_token (order_token),
    KEY user_id (user_id),
    KEY status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS order_items (
    id               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    order_id         BIGINT UNSIGNED NOT NULL,
    produk_id        BIGINT UNSIGNED NOT NULL,
    variasi_id       BIGINT UNSIGNED DEFAULT NULL,
    produk_nama      VARCHAR(255) NOT NULL,
    variasi_label    VARCHAR(255) DEFAULT '',
    harga            BIGINT UNSIGNED NOT NULL DEFAULT 0,
    qty              INT UNSIGNED NOT NULL DEFAULT 1,
    subtotal         BIGINT UNSIGNED NOT NULL DEFAULT 0,
    gambar_url       VARCHAR(500) DEFAULT '',
    sku              VARCHAR(80) DEFAULT '',

    PRIMARY KEY (id),
    KEY order_id (order_id),
    KEY produk_id (produk_id),
    CONSTRAINT fk_item_order
        FOREIGN KEY (order_id) REFERENCES orders (id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------------
-- Metode pembayaran (transfer manual, QRIS, dst)
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS payment_methods (
    id              VARCHAR(32)  NOT NULL,
    label           VARCHAR(120) NOT NULL,
    type            ENUM('bank','qris','other') NOT NULL DEFAULT 'bank',
    enabled         TINYINT(1) NOT NULL DEFAULT 1,
    account_number  VARCHAR(60)  DEFAULT '',
    account_name    VARCHAR(120) DEFAULT '',
    qris_image_path VARCHAR(255) DEFAULT '',
    instructions    TEXT,
    urutan          INT UNSIGNED NOT NULL DEFAULT 0,

    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data awal metode pembayaran (mirip default di theme WP lama)
INSERT INTO payment_methods (id, label, type, enabled, account_number, account_name, instructions, urutan)
VALUES
    ('bca', 'Transfer BCA', 'bank', 1, '1234567890', 'PT Mercatoria',
     'Transfer sesuai total ke rekening BCA di atas. Simpan bukti transfer.', 1),
    ('qris', 'QRIS', 'qris', 1, '', '',
     'Scan QRIS pakai aplikasi bank atau e-wallet apapun.', 2)
ON DUPLICATE KEY UPDATE label = VALUES(label);

SET FOREIGN_KEY_CHECKS = 1;
