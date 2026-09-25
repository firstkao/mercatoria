-- Mercatoria: tambahan schema yang tidak ada di dump awal.
-- Jalankan satu kali setelah tabel users, orders, admins, dan payment_proofs tersedia.
-- Target: MariaDB/MySQL.

CREATE TABLE IF NOT EXISTS appeals (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NULL,
    identity_record_id BIGINT UNSIGNED NULL,
    type VARCHAR(40) NOT NULL,
    reason TEXT NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'pending',
    admin_id BIGINT UNSIGNED NULL,
    admin_note TEXT NULL,
    resolved_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    PRIMARY KEY (id),
    KEY appeals_user_id_status_index (user_id, status),
    KEY appeals_identity_record_id_index (identity_record_id),
    CONSTRAINT appeals_user_id_foreign FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL,
    CONSTRAINT appeals_identity_record_id_foreign FOREIGN KEY (identity_record_id) REFERENCES identity_records (id) ON DELETE SET NULL,
    CONSTRAINT appeals_admin_id_foreign FOREIGN KEY (admin_id) REFERENCES admins (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS order_addresses (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    order_id BIGINT UNSIGNED NOT NULL,
    recipient_name VARCHAR(255) NOT NULL,
    whatsapp VARCHAR(20) NULL,
    province VARCHAR(255) NULL,
    city VARCHAR(255) NULL,
    district VARCHAR(255) NULL,
    postal_code VARCHAR(10) NULL,
    street_address TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    PRIMARY KEY (id),
    UNIQUE KEY order_addresses_order_id_unique (order_id),
    CONSTRAINT order_addresses_order_id_foreign FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Jalankan query ini bila payment_proofs belum membedakan DP dan pelunasan.
-- Jika kolom sudah ada, jangan jalankan statement ALTER ini lagi.
ALTER TABLE payment_proofs
    ADD COLUMN payment_stage VARCHAR(10) NOT NULL DEFAULT 'dp' AFTER payment_method_id;

ALTER TABLE payment_proofs
    ADD KEY payment_proofs_order_status_index (order_id, status);
