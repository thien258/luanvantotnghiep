-- ============================================================
-- MÔ HÌNH DỮ LIỆU MỨC VẬT LÝ (Physical Model)
-- Triển khai thực tế trên MySQL / InnoDB
-- Hệ thống AROMA — Bán nước hoa online
-- ============================================================

-- Quan tâm: Kiểu dữ liệu thật, index, bộ nhớ lưu trữ,
--           tối ưu truy vấn, gắn với DBMS cụ thể (MySQL)

CREATE TABLE categories (
    id      BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name    VARCHAR(191)    NOT NULL,
    status  INT             NOT NULL DEFAULT 0,
    created_at TIMESTAMP    NULL,
    updated_at TIMESTAMP    NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE brands (
    id      BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name    VARCHAR(255)    NOT NULL,
    image   TEXT            NOT NULL,
    descrip TEXT            NOT NULL,
    status  INT             NOT NULL DEFAULT 0,
    created_at TIMESTAMP    NULL,
    updated_at TIMESTAMP    NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE concentrations (
    id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    concentration   VARCHAR(255)    NOT NULL,
    status          INT             NOT NULL DEFAULT 0,
    created_at TIMESTAMP    NULL,
    updated_at TIMESTAMP    NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE users (
    id                  BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name                VARCHAR(191)    NOT NULL,
    email               VARCHAR(191)    NOT NULL UNIQUE,
    email_verified_at   TIMESTAMP       NULL,
    phone               VARCHAR(15)     NOT NULL,
    address             VARCHAR(255)    NOT NULL,
    password            VARCHAR(191)    NOT NULL,
    remember_token      VARCHAR(100)    NULL,
    role                VARCHAR(20)     NOT NULL DEFAULT 'customer',
    is_active           TINYINT(1)      NOT NULL DEFAULT 1,
    manufacturer_id     BIGINT          NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE user_addresses (
    id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    idUser      BIGINT UNSIGNED NOT NULL,
    name        VARCHAR(255)    NOT NULL,
    phone       VARCHAR(20)     NOT NULL,
    address     VARCHAR(255)    NOT NULL,
    is_default  TINYINT(1)      NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX user_addresses_iduser_foreign (idUser),
    FOREIGN KEY (idUser) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE products (
    id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    title           VARCHAR(250)    NOT NULL,
    decription      TEXT            NOT NULL,
    volume          VARCHAR(255)    NULL,
    price           INT             NOT NULL DEFAULT 0,
    quantity        INT             NOT NULL DEFAULT 0,
    image           TEXT            NOT NULL,
    status          TEXT            NOT NULL,
    idCategory      BIGINT UNSIGNED NOT NULL DEFAULT 23,
    idBrand         BIGINT UNSIGNED NOT NULL,
    idConcentration BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX fk_product_category (idCategory),
    INDEX fk_product_brand (idBrand),
    INDEX fk_product_concentration (idConcentration),
    FOREIGN KEY (idCategory)      REFERENCES categories(id)     ON DELETE RESTRICT,
    FOREIGN KEY (idBrand)         REFERENCES brands(id)          ON DELETE RESTRICT,
    FOREIGN KEY (idConcentration) REFERENCES concentrations(id)  ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE festivals (
    id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(150)    NOT NULL,
    discount    INT             NOT NULL DEFAULT 0,
    status      INT             NOT NULL DEFAULT 1,
    start_date  DATE            NOT NULL,
    end_date    DATE            NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE festival_product (
    id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    idFestival  BIGINT UNSIGNED NULL,
    idProduct   BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX fk_fp_festival (idFestival),
    INDEX fk_fp_product  (idProduct),
    FOREIGN KEY (idFestival) REFERENCES festivals(id) ON DELETE SET NULL,
    FOREIGN KEY (idProduct)  REFERENCES products(id)  ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE carts (
    id          BIGINT          NOT NULL AUTO_INCREMENT PRIMARY KEY,
    quantity    INT             NOT NULL DEFAULT 1,
    idUser      BIGINT UNSIGNED NOT NULL,
    product_id  BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX fk_carts_user (idUser),
    INDEX carts_product_id_foreign (product_id),
    FOREIGN KEY (idUser)     REFERENCES users(id)    ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE orders (
    id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    idUser          BIGINT UNSIGNED NOT NULL,
    fullname        VARCHAR(255)    NULL,
    phone           VARCHAR(255)    NULL,
    address         VARCHAR(255)    NOT NULL,
    payment_method  VARCHAR(255)    NOT NULL DEFAULT 'CREDIT CARD',
    total_price     INT             NOT NULL DEFAULT 0,
    status          INT             NOT NULL DEFAULT 0,
    note            TEXT            NULL,
    tracking_code   VARCHAR(255)    NULL UNIQUE,
    created_at  TIMESTAMP           NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP           NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX fk_orders_user (idUser),
    FOREIGN KEY (idUser) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE order_details (
    id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    idProduct   BIGINT UNSIGNED NOT NULL,
    idOrder     BIGINT UNSIGNED NOT NULL,
    quantity    INT             NOT NULL,
    price       INT             NOT NULL,
    name        VARCHAR(255)    NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX fk_detail_product (idProduct),
    INDEX fk_detail_order   (idOrder),
    FOREIGN KEY (idProduct) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (idOrder)   REFERENCES orders(id)   ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE comments (
    id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    idProduct   BIGINT UNSIGNED NOT NULL,
    name        VARCHAR(255)    NOT NULL,
    chat        TEXT            NOT NULL,
    created_at  TIMESTAMP       NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP       NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX fk_comment_product (idProduct),
    FOREIGN KEY (idProduct) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE contacts (
    id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(255)    NOT NULL,
    email       VARCHAR(255)    NOT NULL,
    message     TEXT            NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE warehouse_receipts (
    id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    receipt_code    VARCHAR(255)    NOT NULL UNIQUE,
    supplier        VARCHAR(255)    NULL,
    note            TEXT            NULL,
    total_items     INT             NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE warehouse_stock_logs (
    id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    receipt_id  BIGINT UNSIGNED NULL,
    product_id  BIGINT UNSIGNED NOT NULL,
    type        ENUM('import','export') NOT NULL,
    quantity    INT             NOT NULL,
    stock_after INT             NOT NULL,
    reason      VARCHAR(255)    NULL,
    expiry_date DATE            NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (receipt_id)  REFERENCES warehouse_receipts(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id)  REFERENCES products(id)           ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE warehouse_imports (
    id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    file_path       VARCHAR(255)    NOT NULL,
    original_name   VARCHAR(255)    NOT NULL,
    supplier        VARCHAR(255)    NULL,
    note            TEXT            NULL,
    uploaded_by     BIGINT UNSIGNED NULL,
    status          ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    reviewed_by     BIGINT UNSIGNED NULL,
    reviewed_at     TIMESTAMP       NULL,
    approved_items  JSON            NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE procurement_requests (
    id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    request_code    VARCHAR(255)    NOT NULL UNIQUE,
    status          ENUM('open','closed') NOT NULL DEFAULT 'open',
    note            TEXT            NULL,
    deadline        DATE            NULL,
    created_by      BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE procurement_request_items (
    id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    request_id      BIGINT UNSIGNED NOT NULL,
    product_id      BIGINT UNSIGNED NULL,
    product_name    VARCHAR(255)    NOT NULL,
    qty_needed      INT             NOT NULL,
    note            TEXT            NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (request_id) REFERENCES procurement_requests(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)             ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE supplier_offers (
    id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    manufacturer_id BIGINT UNSIGNED NOT NULL,
    request_id      BIGINT UNSIGNED NULL,
    offer_code      VARCHAR(255)    NOT NULL UNIQUE,
    note            TEXT            NULL,
    status          ENUM('draft','submitted','accepted','rejected') NOT NULL DEFAULT 'submitted',
    submitted_at    TIMESTAMP       NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (manufacturer_id) REFERENCES users(id)                  ON DELETE CASCADE,
    FOREIGN KEY (request_id)      REFERENCES procurement_requests(id)   ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE supplier_offer_items (
    id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    offer_id        BIGINT UNSIGNED NOT NULL,
    product_id      BIGINT UNSIGNED NULL,
    product_name    VARCHAR(255)    NOT NULL,
    unit_price      DECIMAL(15,2)   NOT NULL,
    note            TEXT            NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (offer_id)   REFERENCES supplier_offers(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE purchase_orders (
    id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    offer_id        BIGINT UNSIGNED NOT NULL,
    manufacturer_id BIGINT UNSIGNED NOT NULL,
    order_code      VARCHAR(255)    NOT NULL UNIQUE,
    total_amount    DECIMAL(15,2)   NOT NULL DEFAULT 0,
    status          ENUM('pending','confirmed','delivering','received','cancelled') NOT NULL DEFAULT 'pending',
    expected_date   DATE            NULL,
    note            TEXT            NULL,
    created_by      BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (offer_id)        REFERENCES supplier_offers(id) ON DELETE RESTRICT,
    FOREIGN KEY (manufacturer_id) REFERENCES users(id)           ON DELETE CASCADE,
    FOREIGN KEY (created_by)      REFERENCES users(id)           ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE purchase_order_items (
    id                  BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    purchase_order_id   BIGINT UNSIGNED NOT NULL,
    product_id          BIGINT UNSIGNED NULL,
    product_name        VARCHAR(255)    NOT NULL,
    quantity            INT             NOT NULL,
    unit_price          DECIMAL(15,2)   NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (purchase_order_id) REFERENCES purchase_orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id)        REFERENCES products(id)        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE root_activity_logs (
    id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id     BIGINT UNSIGNED NOT NULL,   -- snapshot, không FK tránh mất log
    user_name   VARCHAR(255)    NOT NULL,   -- snapshot tên tại thời điểm
    user_email  VARCHAR(255)    NOT NULL,   -- snapshot email tại thời điểm
    action      VARCHAR(255)    NOT NULL,
    created_at  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE footer (
    id          INT             NOT NULL AUTO_INCREMENT PRIMARY KEY,
    header      TEXT            NOT NULL,
    textheader  TEXT            NOT NULL,
    header2     TEXT            NOT NULL,
    address     TEXT            NOT NULL,
    phone       INT             NOT NULL,
    email       TEXT            NOT NULL,
    created_by  BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (created_by) REFERENCES users(id)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE title (
    idTitle     INT             NOT NULL AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(255)    NOT NULL,
    image       TEXT            NOT NULL,
    button      VARCHAR(255)    NOT NULL,
    descrip     VARCHAR(255)    NOT NULL,
    created_by  BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (created_by) REFERENCES users(id)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE manufacturers_product (
    id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    manufacturer_id BIGINT UNSIGNED NOT NULL,
    product_id      BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (manufacturer_id) REFERENCES users(id)    ON DELETE CASCADE,
    FOREIGN KEY (product_id)      REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
