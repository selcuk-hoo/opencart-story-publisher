<?php

/**
 * Publishes products into OpenCart.
 *
 * The Publisher ties the whole pipeline together and follows the steps from
 * docs/SPECIFICATION.md, section 10:
 *
 *   1. find product folders        (Scanner)
 *   2. read product.md             (ProductParser)
 *   3. validate metadata           (ProductParser)
 *   4. convert Markdown to HTML     (MarkdownRenderer)
 *   5. copy images
 *   6. copy downloads
 *   7. create or update the product (OpenCartApi)
 *   8. report success or failure
 *
 * Each product is imported on its own. One broken product never stops the
 * others; it is reported and the importer moves on.
 */
class Publisher
{
    private Scanner $scanner;
    private ProductParser $parser;
    private MarkdownRenderer $renderer;
    private OpenCartApi $api;
    private ImageOptimizer $optimizer;

    // File locations, taken from config.php.
    private string $imageDir;      // OpenCart image/ directory
    private string $imageUrlBase;  // public URL prefix for images, e.g. "image/"
    private string $downloadDir;   // OpenCart download storage directory

    public function __construct(
        Scanner $scanner,
        ProductParser $parser,
        MarkdownRenderer $renderer,
        OpenCartApi $api,
        ImageOptimizer $optimizer,
        string $imageDir,
        string $imageUrlBase,
        string $downloadDir
    ) {
        $this->scanner = $scanner;
        $this->parser = $parser;
        $this->renderer = $renderer;
        $this->api = $api;
        $this->optimizer = $optimizer;
        $this->imageDir = rtrim($imageDir, '/');
        $this->imageUrlBase = rtrim($imageUrlBase, '/') . '/';
        $this->downloadDir = rtrim($downloadDir, '/');
    }

    /**
     * Import every product folder (or a single one, when $onlySlug is given).
     *
     * @return array<int,array{slug:string,ok:bool,action:string,message:string}>
     */
    public function importAll(?string $onlySlug = null): array
    {
        $report = [];

        foreach ($this->scanner->findProducts() as $dir) {
            $slug = basename($dir);

            if ($onlySlug !== null && $slug !== $onlySlug) {
                continue;
            }

            try {
                $action = $this->importOne($dir);
                $report[] = [
                    'slug' => $slug,
                    'ok' => true,
                    'action' => $action,
                    'message' => "Product '{$slug}' {$action}.",
                ];
            } catch (ImportException $e) {
                $report[] = [
                    'slug' => $slug,
                    'ok' => false,
                    'action' => 'failed',
                    'message' => $e->getMessage(),
                ];
            }
        }

        return $report;
    }

    /**
     * Import one product folder. Returns "created" or "updated".
     */
    private function importOne(string $dir): string
    {
        // Steps 2 & 3: read and validate.
        $product = $this->parser->parse($dir);

        // Step 4: Markdown to HTML. Each "# " heading becomes a tab, and the
        // inline image URLs are rewritten to their public OpenCart location.
        $imageUrls = $this->imageUrlMap($product);
        $html = $this->renderer->renderTabs($product->markdown, $imageUrls, $product->slug);

        // A plain-text summary at the very top. It reads as a short intro on
        // the product page, and it is what OpenCart shows in category and
        // search listings (OpenCart strips the tags there, which would
        // otherwise turn the tab labels into gibberish).
        $summary = $product->meta('summary');
        if ($summary !== '') {
            $html = '<p class="story-summary">' . htmlspecialchars($summary, ENT_QUOTES) . "</p>\n" . $html;
        }

        // Step 5: copy images into OpenCart.
        $this->copyImages($product);

        // Step 6: copy downloads into OpenCart storage.
        $downloadRows = $this->copyDownloads($product);

        // Step 7: create or update the product (a re-import updates in place).
        $this->api->begin();
        try {
            $model = $product->meta('model');
            $price = (float) $product->meta('price');
            $status = strtolower($product->meta('status', 'enabled')) === 'disabled' ? 0 : 1;
            $mainImage = $this->mainImagePath($product);

            $existingId = $this->api->findProductIdByModel($model);
            if ($existingId === null) {
                $productId = $this->api->createProduct($model, $price, $mainImage, $status);
                $action = 'created';
            } else {
                $productId = $existingId;
                $this->api->updateProduct($productId, $price, $mainImage, $status);
                $action = 'updated';
            }

            $this->api->saveDescription($productId, $product->meta('name'), $html);
            $this->api->linkStore($productId);
            $this->api->saveSeoUrl($productId, $product->slug);
            $this->api->replaceDownloads($productId, $downloadRows);

            $category = $product->meta('category');
            if ($category !== '') {
                $linked = $this->api->linkCategoryByName($productId, $category);
                if (!$linked) {
                    fwrite(STDERR, "Warning: category '{$category}' not found for '{$product->slug}'. " .
                        "Create it in OpenCart, then import again.\n");
                }
            }

            $this->api->commit();
        } catch (ImportException $e) {
            $this->api->rollback();
            throw $e;
        }

        return $action;
    }

    /**
     * Where a product's images live once published, keyed by file name.
     * Used to rewrite <img src> in the story HTML.
     *
     * @return array<string,string>
     */
    private function imageUrlMap(Product $product): array
    {
        $map = [];
        foreach ($product->images as $name) {
            $map[$name] = $this->imageUrlBase . 'catalog/story/' . $product->slug . '/' . $name;
        }
        return $map;
    }

    /** The main product image, as OpenCart stores it (relative to image/). */
    private function mainImagePath(Product $product): string
    {
        $image = $product->meta('image');
        if ($image === '') {
            return '';
        }
        return 'catalog/story/' . $product->slug . '/' . $image;
    }

    private function copyImages(Product $product): void
    {
        if (!$product->images) {
            return;
        }

        $target = $this->imageDir . '/catalog/story/' . $product->slug;
        $this->makeDir($target);

        foreach ($product->images as $name) {
            $from = $product->dir . '/images/' . $name;
            $to = $target . '/' . $name;
            // Shrinks the image first when it is larger than the configured
            // maximum width; small images are copied unchanged.
            if (!$this->optimizer->copy($from, $to)) {
                throw new ImportException(
                    "Could not copy image '{$name}' in '{$product->slug}'.\n" .
                    "Check that the file exists and that OpenCart's image folder is writable."
                );
            }
        }

        // A product may name an image in the front matter that is not on disk.
        $image = $product->meta('image');
        if ($image !== '' && !in_array($image, $product->images, true)) {
            throw new ImportException(
                "Image '{$image}' listed in '{$product->slug}' was not found in images/.\n" .
                "Add the file, or fix the 'image:' field in product.md."
            );
        }
    }

    /**
     * Copy the downloadable files and describe them for OpenCartApi.
     *
     * @return array<int,array{filename:string,mask:string,name:string}>
     */
    private function copyDownloads(Product $product): array
    {
        if (!$product->downloads) {
            return [];
        }

        $this->makeDir($this->downloadDir);

        $rows = [];
        foreach ($product->downloads as $name) {
            // A stable stored name so re-importing overwrites instead of
            // leaving orphan files behind.
            $stored = $product->slug . '-' . $name;
            $from = $product->dir . '/downloads/' . $name;
            $to = $this->downloadDir . '/' . $stored;

            if (!@copy($from, $to)) {
                throw new ImportException(
                    "Could not copy download '{$name}' in '{$product->slug}'.\n" .
                    "Check that the file exists and that OpenCart's download folder is writable."
                );
            }

            $rows[] = [
                'filename' => $stored, // what OpenCart keeps on disk
                'mask' => $name,       // the name the customer downloads
                'name' => $name,       // shown in the admin
            ];
        }

        return $rows;
    }

    private function makeDir(string $dir): void
    {
        if (is_dir($dir)) {
            return;
        }
        if (!@mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new ImportException(
                "Could not create folder: {$dir}\n" .
                "Check that OpenCart's folders are writable."
            );
        }
    }
}
