<?php

/**
 * The only class that knows about OpenCart's database.
 *
 * Everything OpenCart specific lives here, so the rest of the code
 * (Scanner, ProductParser, MarkdownRenderer, Publisher) stays plain PHP.
 * This matches the architecture in docs/ARCHITECTURE.md:
 *
 *     Publisher -> OpenCartApi -> Database
 *
 * It targets the default OpenCart 4 schema. Table names use the configured
 * prefix (DB_PREFIX), e.g. "oc_product".
 */
class OpenCartApi
{
    private mysqli $db;
    private string $prefix;
    private int $languageId;
    private int $storeId;

    public function __construct()
    {
        $this->prefix = DB_PREFIX;
        $this->languageId = (int) OPENCART_LANGUAGE_ID;
        $this->storeId = (int) OPENCART_STORE_ID;

        mysqli_report(MYSQLI_REPORT_OFF);

        $db = @new mysqli(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE, (int) DB_PORT);
        if ($db->connect_errno) {
            throw new ImportException(
                "Could not connect to the OpenCart database: {$db->connect_error}\n" .
                "Check the DB_* settings in config.php."
            );
        }
        $db->set_charset('utf8mb4');

        $this->db = $db;
    }

    /** Table name with the configured prefix. */
    private function table(string $name): string
    {
        return $this->prefix . $name;
    }

    private function escape(string $value): string
    {
        return $this->db->real_escape_string($value);
    }

    private function query(string $sql)
    {
        $result = $this->db->query($sql);
        if ($result === false) {
            throw new ImportException("Database error: {$this->db->error}");
        }
        return $result;
    }

    public function begin(): void
    {
        $this->db->begin_transaction();
    }

    public function commit(): void
    {
        $this->db->commit();
    }

    public function rollback(): void
    {
        $this->db->rollback();
    }

    /** Return the product_id for a model, or null when it does not exist yet. */
    public function findProductIdByModel(string $model): ?int
    {
        $model = $this->escape($model);
        $result = $this->query(
            "SELECT product_id FROM `{$this->table('product')}` WHERE model = '{$model}' LIMIT 1"
        );
        $row = $result->fetch_assoc();

        return $row ? (int) $row['product_id'] : null;
    }

    /**
     * Create a product row and return its new product_id.
     *
     * Only a small, sensible set of columns is filled. Everything else keeps
     * its OpenCart default. Products are treated as digital goods: stock is
     * not subtracted and a large quantity keeps them in stock.
     */
    public function createProduct(string $model, float $price, string $image, int $status): int
    {
        $model = $this->escape($model);
        $image = $this->escape($image);

        // The string columns below are NOT NULL with no default in the
        // OpenCart schema, so we set them explicitly to keep working under
        // MySQL strict mode. The rest keep OpenCart's own defaults.
        $this->query(
            "INSERT INTO `{$this->table('product')}` SET
                model = '{$model}',
                sku = '',
                upc = '',
                ean = '',
                jan = '',
                isbn = '',
                mpn = '',
                location = '',
                price = {$price},
                image = '{$image}',
                status = {$status},
                quantity = 1000,
                subtract = 0,
                minimum = 1,
                shipping = 0,
                stock_status_id = 5,
                manufacturer_id = 0,
                tax_class_id = 0,
                weight_class_id = 1,
                length_class_id = 1,
                date_available = CURDATE(),
                date_added = NOW(),
                date_modified = NOW()"
        );

        return (int) $this->db->insert_id;
    }

    /** Update the core columns of an existing product. */
    public function updateProduct(int $productId, float $price, string $image, int $status): void
    {
        $image = $this->escape($image);

        $this->query(
            "UPDATE `{$this->table('product')}` SET
                price = {$price},
                image = '{$image}',
                status = {$status},
                date_modified = NOW()
             WHERE product_id = {$productId}"
        );
    }

    /** Insert or replace the product's description (name + HTML story). */
    public function saveDescription(int $productId, string $name, string $html): void
    {
        $name = $this->escape($name);
        $html = $this->escape($html);

        $this->query(
            "DELETE FROM `{$this->table('product_description')}`
             WHERE product_id = {$productId} AND language_id = {$this->languageId}"
        );

        $this->query(
            "INSERT INTO `{$this->table('product_description')}` SET
                product_id = {$productId},
                language_id = {$this->languageId},
                name = '{$name}',
                description = '{$html}',
                tag = '',
                meta_title = '{$name}',
                meta_description = '',
                meta_keyword = ''"
        );
    }

    /** Make sure the product is assigned to the configured store. */
    public function linkStore(int $productId): void
    {
        $this->query(
            "DELETE FROM `{$this->table('product_to_store')}`
             WHERE product_id = {$productId} AND store_id = {$this->storeId}"
        );
        $this->query(
            "INSERT INTO `{$this->table('product_to_store')}` SET
                product_id = {$productId}, store_id = {$this->storeId}"
        );
    }

    /** Find a category by its name, or null when it does not exist. */
    public function findCategoryIdByName(string $categoryName): ?int
    {
        $name = $this->escape($categoryName);
        $result = $this->query(
            "SELECT category_id FROM `{$this->table('category_description')}`
             WHERE name = '{$name}' AND language_id = {$this->languageId} LIMIT 1"
        );
        $row = $result->fetch_assoc();

        return $row ? (int) $row['category_id'] : null;
    }

    /**
     * Create a top-level category and return its new category_id.
     *
     * Only the columns needed for the category to appear in the store are
     * set; everything else keeps its OpenCart default.
     */
    public function createCategory(string $categoryName): int
    {
        $name = $this->escape($categoryName);

        // `column` is a reserved word, so it stays quoted.
        $this->query(
            "INSERT INTO `{$this->table('category')}` SET
                image = '',
                parent_id = 0,
                top = 1,
                `column` = 1,
                sort_order = 0,
                status = 1,
                date_added = NOW(),
                date_modified = NOW()"
        );
        $categoryId = (int) $this->db->insert_id;

        $this->query(
            "INSERT INTO `{$this->table('category_description')}` SET
                category_id = {$categoryId},
                language_id = {$this->languageId},
                name = '{$name}',
                description = '',
                meta_title = '{$name}',
                meta_description = '',
                meta_keyword = ''"
        );

        $this->query(
            "INSERT INTO `{$this->table('category_to_store')}` SET
                category_id = {$categoryId}, store_id = {$this->storeId}"
        );

        // A top-level category has a single path row pointing at itself.
        $this->query(
            "INSERT INTO `{$this->table('category_path')}` SET
                category_id = {$categoryId}, path_id = {$categoryId}, level = 0"
        );

        $keyword = $this->escape($this->slug($categoryName));
        $this->query(
            "INSERT INTO `{$this->table('seo_url')}` SET
                store_id = {$this->storeId},
                language_id = {$this->languageId},
                `key` = 'category_id',
                `value` = '{$categoryId}',
                keyword = '{$keyword}'"
        );

        return $categoryId;
    }

    /** Attach a product to a category, replacing any previous assignment. */
    public function linkProductToCategory(int $productId, int $categoryId): void
    {
        $this->query(
            "DELETE FROM `{$this->table('product_to_category')}`
             WHERE product_id = {$productId}"
        );
        $this->query(
            "INSERT INTO `{$this->table('product_to_category')}` SET
                product_id = {$productId}, category_id = {$categoryId}"
        );
    }

    /** Turn a name into a URL-friendly keyword (e.g. "3D Modeller" -> "3d-modeller"). */
    private function slug(string $text): string
    {
        $map = [
            'ç' => 'c', 'ğ' => 'g', 'ı' => 'i', 'ö' => 'o', 'ş' => 's', 'ü' => 'u',
            'Ç' => 'c', 'Ğ' => 'g', 'İ' => 'i', 'Ö' => 'o', 'Ş' => 's', 'Ü' => 'u',
        ];
        $text = strtr($text, $map);
        $text = mb_strtolower($text, 'UTF-8');
        $text = preg_replace('/[^a-z0-9]+/u', '-', $text);

        return trim($text, '-');
    }

    /** Give the product a clean URL keyword (its slug). */
    public function saveSeoUrl(int $productId, string $keyword): void
    {
        $keyword = $this->escape($keyword);

        $this->query(
            "DELETE FROM `{$this->table('seo_url')}`
             WHERE `key` = 'product_id' AND `value` = '{$productId}'"
        );
        $this->query(
            "INSERT INTO `{$this->table('seo_url')}` SET
                store_id = {$this->storeId},
                language_id = {$this->languageId},
                `key` = 'product_id',
                `value` = '{$productId}',
                keyword = '{$keyword}'"
        );
    }

    /**
     * Replace all downloads attached to the product.
     *
     * @param array<int,array{filename:string,mask:string,name:string}> $downloads
     */
    public function replaceDownloads(int $productId, array $downloads): void
    {
        // Remove old download rows we created for this product, then the links.
        $result = $this->query(
            "SELECT download_id FROM `{$this->table('product_to_download')}`
             WHERE product_id = {$productId}"
        );
        while ($row = $result->fetch_assoc()) {
            $downloadId = (int) $row['download_id'];
            $this->query("DELETE FROM `{$this->table('download')}` WHERE download_id = {$downloadId}");
            $this->query("DELETE FROM `{$this->table('download_description')}` WHERE download_id = {$downloadId}");
        }
        $this->query(
            "DELETE FROM `{$this->table('product_to_download')}` WHERE product_id = {$productId}"
        );

        foreach ($downloads as $download) {
            $filename = $this->escape($download['filename']);
            $mask = $this->escape($download['mask']);
            $name = $this->escape($download['name']);

            $this->query(
                "INSERT INTO `{$this->table('download')}` SET
                    filename = '{$filename}',
                    mask = '{$mask}',
                    date_added = NOW()"
            );
            $downloadId = (int) $this->db->insert_id;

            $this->query(
                "INSERT INTO `{$this->table('download_description')}` SET
                    download_id = {$downloadId},
                    language_id = {$this->languageId},
                    name = '{$name}'"
            );

            $this->query(
                "INSERT INTO `{$this->table('product_to_download')}` SET
                    product_id = {$productId},
                    download_id = {$downloadId}"
            );
        }
    }
}
