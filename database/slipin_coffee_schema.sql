-- =====================================================================
-- SLIPIN COFFEE - Database Schema (MariaDB 10.4+)
-- Sistem pencatatan penjualan & keuangan kopi keliling (bukan POS)
-- =====================================================================
-- Cara import:
--   mysql -u root -p < slipin_coffee_schema.sql
-- =====================================================================

CREATE DATABASE IF NOT EXISTS slipin_coffee
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE slipin_coffee;

-- ---------------------------------------------------------------------
-- 1. USERS (owner & crew yang jualan)
-- ---------------------------------------------------------------------
CREATE TABLE users (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name          VARCHAR(100) NOT NULL,
  username      VARCHAR(50)  NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role          ENUM('owner','crew') NOT NULL DEFAULT 'crew',
  is_active     TINYINT(1) NOT NULL DEFAULT 1,
  created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 2. LOCATIONS (titik / rute mangkal)
-- ---------------------------------------------------------------------
CREATE TABLE locations (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name       VARCHAR(100) NOT NULL,
  notes      VARCHAR(255) NULL,
  is_active  TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 3. PRODUCTS (menu minuman)
--    cups_per_unit: berapa polycup kepake per 1 item (default 1)
-- ---------------------------------------------------------------------
CREATE TABLE products (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name          VARCHAR(100) NOT NULL,
  price         DECIMAL(12,0) NOT NULL DEFAULT 0,
  cogs          DECIMAL(12,0) NOT NULL DEFAULT 0 COMMENT 'estimasi HPP per cup (opsional)',
  cups_per_unit TINYINT UNSIGNED NOT NULL DEFAULT 1,
  is_active     TINYINT(1) NOT NULL DEFAULT 1,
  created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 4. SALES SESSIONS (1 sesi = 1x jualan / 1 hari / 1 shift)
--    Di sini stok awal polycup, sisa polycup, dan uang masuk dicatat.
--    cash_masuk = uang cash hasil jualan (TIDAK termasuk modal kembalian)
-- ---------------------------------------------------------------------
CREATE TABLE sales_sessions (
  id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  sales_date      DATE NOT NULL,
  location_id     INT UNSIGNED NULL,
  user_id         INT UNSIGNED NULL,
  -- Polycup
  polycup_awal    INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'stok polycup yang dibawa',
  polycup_tambah  INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'tambahan polycup di tengah jualan',
  polycup_rusak   INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'rusak / tumpah / bocor / free',
  polycup_sisa    INT UNSIGNED NULL COMMENT 'sisa fisik hasil hitung saat closing',
  -- Uang
  modal_kembalian DECIMAL(12,0) NOT NULL DEFAULT 0 COMMENT 'uang receh yang dibawa',
  cash_masuk      DECIMAL(12,0) NOT NULL DEFAULT 0,
  qris_masuk      DECIMAL(12,0) NOT NULL DEFAULT 0,
  -- Status
  status          ENUM('open','closed') NOT NULL DEFAULT 'open',
  notes           TEXT NULL,
  opened_at       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  closed_at       TIMESTAMP NULL,
  CONSTRAINT fk_ss_location FOREIGN KEY (location_id) REFERENCES locations(id) ON DELETE SET NULL,
  CONSTRAINT fk_ss_user     FOREIGN KEY (user_id)     REFERENCES users(id)     ON DELETE SET NULL,
  INDEX idx_ss_date (sales_date)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 5. SALES SESSION ITEMS (laporan terjual per menu)
--    unit_price disimpan per transaksi supaya kalau harga berubah,
--    laporan lama tetap akurat.
-- ---------------------------------------------------------------------
CREATE TABLE sales_session_items (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  session_id  INT UNSIGNED NOT NULL,
  product_id  INT UNSIGNED NOT NULL,
  qty         INT UNSIGNED NOT NULL DEFAULT 0,
  unit_price  DECIMAL(12,0) NOT NULL,
  subtotal    DECIMAL(14,0) GENERATED ALWAYS AS (qty * unit_price) STORED,
  UNIQUE KEY uq_session_product (session_id, product_id),
  CONSTRAINT fk_ssi_session FOREIGN KEY (session_id) REFERENCES sales_sessions(id) ON DELETE CASCADE,
  CONSTRAINT fk_ssi_product FOREIGN KEY (product_id) REFERENCES products(id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 6. EXPENSE CATEGORIES
-- ---------------------------------------------------------------------
CREATE TABLE expense_categories (
  id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name      VARCHAR(100) NOT NULL UNIQUE,
  type      ENUM('bahan','operasional','peralatan','tenaga_kerja','lainnya') NOT NULL DEFAULT 'bahan',
  is_active TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 7. EXPENSES (pengeluaran: es batu, susu, kopi, polycup, bensin, dll)
--    session_id opsional: isi kalau pengeluaran terjadi saat jualan
--    paid_from: asal uangnya (kas jualan / uang pribadi owner / dll)
-- ---------------------------------------------------------------------
CREATE TABLE expenses (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  expense_date  DATE NOT NULL,
  category_id   INT UNSIGNED NOT NULL,
  session_id    INT UNSIGNED NULL,
  description   VARCHAR(255) NOT NULL,
  qty           DECIMAL(10,2) NULL,
  unit          VARCHAR(20) NULL COMMENT 'kg, liter, pcs, pack, dll',
  amount        DECIMAL(14,0) NOT NULL,
  payment_method ENUM('cash','qris','transfer') NOT NULL DEFAULT 'cash',
  paid_from     ENUM('kas_jualan','modal_owner') NOT NULL DEFAULT 'modal_owner',
  supplier      VARCHAR(100) NULL,
  receipt_path  VARCHAR(255) NULL COMMENT 'path foto nota',
  created_by    INT UNSIGNED NULL,
  created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_exp_cat     FOREIGN KEY (category_id) REFERENCES expense_categories(id),
  CONSTRAINT fk_exp_session FOREIGN KEY (session_id)  REFERENCES sales_sessions(id) ON DELETE SET NULL,
  CONSTRAINT fk_exp_user    FOREIGN KEY (created_by)  REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_exp_date (expense_date)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 8. POLYCUP PURCHASES (restock polycup, buat tracking stok gudang)
--    Stok gudang = total beli - total yang dibawa jualan (polycup_awal+tambah)
-- ---------------------------------------------------------------------
CREATE TABLE polycup_purchases (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  purchase_date DATE NOT NULL,
  qty           INT UNSIGNED NOT NULL,
  total_price   DECIMAL(14,0) NOT NULL DEFAULT 0,
  supplier      VARCHAR(100) NULL,
  expense_id    INT UNSIGNED NULL COMMENT 'opsional: link ke tabel expenses',
  notes         VARCHAR(255) NULL,
  CONSTRAINT fk_pp_expense FOREIGN KEY (expense_id) REFERENCES expenses(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 9. OTHER INCOME (pemasukan lain di luar jualan, mis. tambahan modal)
-- ---------------------------------------------------------------------
CREATE TABLE other_incomes (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  income_date  DATE NOT NULL,
  description  VARCHAR(255) NOT NULL,
  amount       DECIMAL(14,0) NOT NULL,
  method       ENUM('cash','qris','transfer') NOT NULL DEFAULT 'cash'
) ENGINE=InnoDB;

-- =====================================================================
-- VIEWS (laporan siap pakai)
-- =====================================================================

-- Ringkasan per sesi: terjual, sisa polycup, selisih, total sales, cash/qris
CREATE OR REPLACE VIEW v_session_summary AS
SELECT
  s.id                                   AS session_id,
  s.sales_date,
  l.name                                 AS location,
  u.name                                 AS crew,
  s.status,
  -- polycup
  s.polycup_awal,
  s.polycup_tambah,
  s.polycup_rusak,
  COALESCE(i.cup_terjual, 0)             AS cup_terjual,
  (s.polycup_awal + s.polycup_tambah - s.polycup_rusak - COALESCE(i.cup_terjual,0))
                                         AS sisa_seharusnya,
  s.polycup_sisa                         AS sisa_fisik,
  (s.polycup_sisa - (s.polycup_awal + s.polycup_tambah - s.polycup_rusak - COALESCE(i.cup_terjual,0)))
                                         AS selisih_polycup,
  -- uang
  COALESCE(i.total_sales, 0)             AS total_sales,
  s.cash_masuk,
  s.qris_masuk,
  (s.cash_masuk + s.qris_masuk)          AS uang_masuk,
  ((s.cash_masuk + s.qris_masuk) - COALESCE(i.total_sales,0))
                                         AS selisih_uang,
  COALESCE(e.total_expense, 0)           AS pengeluaran_sesi
FROM sales_sessions s
LEFT JOIN locations l ON l.id = s.location_id
LEFT JOIN users u     ON u.id = s.user_id
LEFT JOIN (
  SELECT ssi.session_id,
         SUM(ssi.qty * p.cups_per_unit) AS cup_terjual,
         SUM(ssi.subtotal)              AS total_sales
  FROM sales_session_items ssi
  JOIN products p ON p.id = ssi.product_id
  GROUP BY ssi.session_id
) i ON i.session_id = s.id
LEFT JOIN (
  SELECT session_id, SUM(amount) AS total_expense
  FROM expenses
  WHERE session_id IS NOT NULL
  GROUP BY session_id
) e ON e.session_id = s.id;

-- Laba rugi harian: sales vs semua pengeluaran di tanggal itu
CREATE OR REPLACE VIEW v_daily_report AS
SELECT
  d.tgl                                   AS report_date,
  COALESCE(s.total_sales, 0)              AS total_sales,
  COALESCE(s.cash_masuk, 0)               AS cash_masuk,
  COALESCE(s.qris_masuk, 0)               AS qris_masuk,
  COALESCE(x.total_expense, 0)            AS total_pengeluaran,
  COALESCE(s.total_sales, 0) - COALESCE(x.total_expense, 0) AS profit
FROM (
  SELECT sales_date AS tgl FROM sales_sessions
  UNION
  SELECT expense_date FROM expenses
) d
LEFT JOIN (
  SELECT ss.sales_date,
         SUM(sm.total_sales) AS total_sales,
         SUM(ss.cash_masuk)  AS cash_masuk,
         SUM(ss.qris_masuk)  AS qris_masuk
  FROM sales_sessions ss
  JOIN v_session_summary sm ON sm.session_id = ss.id
  GROUP BY ss.sales_date
) s ON s.sales_date = d.tgl
LEFT JOIN (
  SELECT expense_date, SUM(amount) AS total_expense
  FROM expenses GROUP BY expense_date
) x ON x.expense_date = d.tgl;

-- Laporan bulanan
CREATE OR REPLACE VIEW v_monthly_report AS
SELECT
  DATE_FORMAT(report_date, '%Y-%m') AS bulan,
  SUM(total_sales)       AS total_sales,
  SUM(cash_masuk)        AS cash_masuk,
  SUM(qris_masuk)        AS qris_masuk,
  SUM(total_pengeluaran) AS total_pengeluaran,
  SUM(profit)            AS profit
FROM v_daily_report
GROUP BY DATE_FORMAT(report_date, '%Y-%m');

-- Menu terlaris
CREATE OR REPLACE VIEW v_product_ranking AS
SELECT p.id, p.name,
       SUM(ssi.qty)      AS total_qty,
       SUM(ssi.subtotal) AS total_omzet
FROM sales_session_items ssi
JOIN products p ON p.id = ssi.product_id
GROUP BY p.id, p.name
ORDER BY total_qty DESC;

-- Pengeluaran per kategori per bulan
CREATE OR REPLACE VIEW v_expense_by_category AS
SELECT DATE_FORMAT(e.expense_date, '%Y-%m') AS bulan,
       c.name AS kategori,
       SUM(e.amount) AS total
FROM expenses e
JOIN expense_categories c ON c.id = e.category_id
GROUP BY DATE_FORMAT(e.expense_date, '%Y-%m'), c.name;

-- Stok polycup di gudang/rumah
CREATE OR REPLACE VIEW v_polycup_stock AS
SELECT
  (SELECT COALESCE(SUM(qty),0) FROM polycup_purchases) AS total_dibeli,
  (SELECT COALESCE(SUM(polycup_awal + polycup_tambah),0) FROM sales_sessions) AS total_dibawa_jualan,
  (SELECT COALESCE(SUM(polycup_sisa),0) FROM sales_sessions WHERE status='closed' AND polycup_sisa IS NOT NULL) AS total_sisa_balik,
  (SELECT COALESCE(SUM(qty),0) FROM polycup_purchases)
   - (SELECT COALESCE(SUM(polycup_awal + polycup_tambah),0) FROM sales_sessions)
   + (SELECT COALESCE(SUM(polycup_sisa),0) FROM sales_sessions WHERE status='closed' AND polycup_sisa IS NOT NULL)
   AS estimasi_stok_gudang;

-- =====================================================================
-- SEED DATA (silakan ubah sesuai kondisi lu)
-- =====================================================================
INSERT INTO expense_categories (name, type) VALUES
  ('Es Batu',               'bahan'),
  ('Susu',                  'bahan'),
  ('Kopi / Biji Kopi',      'bahan'),
  ('Gula / Sirup / Creamer','bahan'),
  ('Polycup & Packaging',   'bahan'),
  ('Bensin / Transport',    'operasional'),
  ('Gas / Listrik / Air',   'operasional'),
  ('Upah Crew',             'tenaga_kerja'),
  ('Peralatan',             'peralatan'),
  ('Lain-lain',             'lainnya');

INSERT INTO products (name, price, cogs) VALUES
  ('Kopi Susu',  10000, 0),
  ('Americano',   8000, 0),
  ('Kopi Gula Aren', 12000, 0);

INSERT INTO locations (name) VALUES ('Titik 1'), ('Titik 2');

-- User owner default. GANTI password_hash dengan hash bcrypt dari aplikasi!
INSERT INTO users (name, username, password_hash, role)
VALUES ('Owner', 'owner', 'GANTI_DENGAN_HASH_BCRYPT', 'owner');

-- =====================================================================
-- USER DATABASE UNTUK APLIKASI (jalankan manual, ganti password)
-- =====================================================================
-- CREATE USER 'slipin_app'@'localhost' IDENTIFIED BY 'GANTI_PASSWORD_KUAT';
-- GRANT SELECT, INSERT, UPDATE, DELETE ON slipin_coffee.* TO 'slipin_app'@'localhost';
-- FLUSH PRIVILEGES;
