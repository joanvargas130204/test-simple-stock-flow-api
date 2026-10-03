<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Table `category`
        DB::statement("
            CREATE TABLE category (
                id CHAR(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
                name VARCHAR(120) NOT NULL,
                PRIMARY KEY (id),
                CONSTRAINT uq_category_name UNIQUE (name),
                CONSTRAINT ck_category_name_not_blank CHECK (CHAR_LENGTH(TRIM(name)) > 0)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
        ");

        // 2. Table `product`
        DB::statement("
            CREATE TABLE product (
                id CHAR(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
                name VARCHAR(200) NOT NULL,
                price DECIMAL(12,2) NOT NULL,
                stock INT NOT NULL,
                category_id CHAR(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
                image_key VARCHAR(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL,
                deleted_at DATETIME(6) NULL,
                version INT NOT NULL,
                PRIMARY KEY (id),
                KEY idx_product_category_active_name (category_id, deleted_at, name),
                KEY idx_product_active_name (deleted_at, name),
                CONSTRAINT fk_product_category_id FOREIGN KEY (category_id)
                    REFERENCES category (id) ON DELETE RESTRICT ON UPDATE NO ACTION,
                CONSTRAINT ck_product_name_not_blank CHECK (CHAR_LENGTH(TRIM(name)) > 0),
                CONSTRAINT ck_product_price_positive CHECK (price > 0),
                CONSTRAINT ck_product_stock_non_negative CHECK (stock >= 0)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
        ");

        // 3. Table `user`
        DB::statement("
            CREATE TABLE `user` (
                id CHAR(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
                username VARCHAR(120) NOT NULL,
                password_hash VARCHAR(512) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
                role VARCHAR(40) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
                PRIMARY KEY (id),
                CONSTRAINT uq_user_username UNIQUE (username),
                CONSTRAINT ck_user_username_normalized CHECK (
                    CHAR_LENGTH(username) > 0
                    AND CAST(username AS BINARY) = CAST(LOWER(TRIM(username)) AS BINARY)
                ),
                CONSTRAINT ck_user_role_allowed CHECK (role IN ('admin','seller')),
                CONSTRAINT ck_user_password_hash_not_blank CHECK (CHAR_LENGTH(password_hash) > 0)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
        ");

        // 4. Table `sale`
        DB::statement("
            CREATE TABLE sale (
                id CHAR(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
                sold_at DATETIME(6) NOT NULL,
                sold_by_username VARCHAR(120) NOT NULL,
                sold_by_user_id CHAR(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
                PRIMARY KEY (id),
                KEY idx_sale_sold_at (sold_at),
                KEY idx_sale_sold_by_user_id (sold_by_user_id),
                CONSTRAINT fk_sale_sold_by_user_id FOREIGN KEY (sold_by_user_id)
                    REFERENCES `user` (id) ON DELETE RESTRICT ON UPDATE NO ACTION
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
        ");

        // 5. Table `sale_item`
        DB::statement("
            CREATE TABLE sale_item (
                id CHAR(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
                sale_id CHAR(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
                product_id CHAR(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
                product_name VARCHAR(200) NOT NULL,
                category_name VARCHAR(120) NOT NULL,
                quantity INT NOT NULL,
                unit_price DECIMAL(12,2) NOT NULL,
                PRIMARY KEY (id),
                CONSTRAINT uq_sale_item_sale_product UNIQUE (sale_id, product_id),
                KEY idx_sale_item_product_id (product_id),
                CONSTRAINT fk_sale_item_sale_id FOREIGN KEY (sale_id)
                    REFERENCES sale (id) ON DELETE CASCADE ON UPDATE NO ACTION,
                CONSTRAINT fk_sale_item_product_id FOREIGN KEY (product_id)
                    REFERENCES product (id) ON DELETE RESTRICT ON UPDATE NO ACTION,
                CONSTRAINT ck_sale_item_quantity_positive CHECK (quantity > 0),
                CONSTRAINT ck_sale_item_unit_price_positive CHECK (unit_price > 0)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
        ");
    }

    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS sale_item;');
        DB::statement('DROP TABLE IF EXISTS sale;');
        DB::statement('DROP TABLE IF EXISTS product;');
        DB::statement('DROP TABLE IF EXISTS `user`;');
        DB::statement('DROP TABLE IF EXISTS category;');
    }
};
