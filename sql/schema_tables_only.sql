-- ------------------------------------------------------------
-- シーズン（例: 2025-2026シーズン = 2025年11月〜2026年3月）
-- ------------------------------------------------------------
CREATE TABLE seasons (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(50) NOT NULL,              -- 表示名 例: "2025-2026シーズン"
  start_date DATE NOT NULL,               -- 例: 2025-11-01
  end_date DATE NOT NULL,                 -- 例: 2026-03-31
  is_active TINYINT(1) NOT NULL DEFAULT 0,-- 現在使用中のシーズンかどうか（1件のみ1にする）
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- ジャンル（おたより／連絡帳・出席簿／画材 など）
-- ------------------------------------------------------------
CREATE TABLE genres (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  display_order INT NOT NULL DEFAULT 0,   -- 一覧での並び順（固定表示用）
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 商品マスタ
-- ------------------------------------------------------------
CREATE TABLE products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  genre_id INT NOT NULL,
  product_code VARCHAR(30) NOT NULL,      -- 例: 3071061
  product_name VARCHAR(255) NOT NULL,     -- 例: おたより用紙A
  display_order INT NOT NULL DEFAULT 0,   -- 一覧での並び順（固定表示用）
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_product_code (product_code),
  CONSTRAINT fk_products_genre FOREIGN KEY (genre_id) REFERENCES genres(id)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 取引先（自由入力だが、入力履歴を補完候補として蓄積する）
-- ------------------------------------------------------------
CREATE TABLE clients (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_client_name (name)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 受注（1件 = 1取引先・1商品・1納期の注文）
-- ------------------------------------------------------------
CREATE TABLE orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  season_id INT NOT NULL,
  product_id INT NOT NULL,
  client_name VARCHAR(255) NOT NULL,      -- 自由入力のためclientsとは正規化しない
  order_date DATE NOT NULL,               -- 受注日
  delivery_type ENUM('date','即納','初旬','中旬','下旬') NOT NULL DEFAULT 'date',
  delivery_date DATE NULL,                -- delivery_type='date'の場合のみ使用
  quantity INT NOT NULL,                  -- 受注数
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_orders_season FOREIGN KEY (season_id) REFERENCES seasons(id),
  CONSTRAINT fk_orders_product FOREIGN KEY (product_id) REFERENCES products(id)
) ENGINE=InnoDB;

CREATE INDEX idx_orders_season_product ON orders(season_id, product_id);

-- ------------------------------------------------------------
-- シーズン開始時在庫（商品詳細ページから入力。商品×シーズンごとに1件）
-- ------------------------------------------------------------
CREATE TABLE initial_stocks (
  id INT AUTO_INCREMENT PRIMARY KEY,
  season_id INT NOT NULL,
  product_id INT NOT NULL,
  quantity INT NOT NULL DEFAULT 0,        -- シーズン開始時点の在庫数
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_season_product (season_id, product_id),
  CONSTRAINT fk_is_season FOREIGN KEY (season_id) REFERENCES seasons(id),
  CONSTRAINT fk_is_product FOREIGN KEY (product_id) REFERENCES products(id)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 発注履歴（1商品に対して複数回発注することがあるため履歴で保持）
-- ------------------------------------------------------------
CREATE TABLE purchase_orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  season_id INT NOT NULL,
  product_id INT NOT NULL,
  order_date DATE NOT NULL,               -- 発注日
  quantity INT NOT NULL,                  -- 発注数
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_po_season FOREIGN KEY (season_id) REFERENCES seasons(id),
  CONSTRAINT fk_po_product FOREIGN KEY (product_id) REFERENCES products(id)
) ENGINE=InnoDB;

CREATE INDEX idx_po_season_product ON purchase_orders(season_id, product_id);

-- ------------------------------------------------------------
-- 初期データ例（必要に応じて編集）
-- ------------------------------------------------------------
INSERT INTO seasons (name, start_date, end_date, is_active) VALUES
  ('2025-2026シーズン', '2025-11-01', '2026-03-31', 1);

INSERT INTO genres (name, display_order) VALUES
  ('おたより', 1),
  ('連絡帳・出席簿', 2),
  ('画材', 3);

INSERT INTO products (genre_id, product_code, product_name, display_order) VALUES
  (1, '3071061', 'おたより用紙A', 1),
  (1, '3071062', 'おたより用紙B', 2),
  (2, '3081001', '連絡帳', 1),
  (2, '3081002', '出席簿', 2),
  (3, '4011001', 'クレヨン12色', 1);