<?php

/**
 * Reads a product folder and turns product.md into a Product object.
 *
 * The front matter is a simple block of "key: value" lines between two
 * "---" markers. We parse it by hand on purpose: the fields are flat, so a
 * full YAML library would be an unnecessary dependency (see talimatlar.md,
 * "External Libraries").
 */
class ProductParser
{
    /** Metadata fields that every product must provide. */
    private const REQUIRED_FIELDS = ['name', 'model', 'price'];

    /** Front matter keys we understand (docs/SPECIFICATION.md, section 6). */
    private const KNOWN_FIELDS = ['name', 'model', 'price', 'category', 'image', 'status', 'summary'];

    public function parse(string $productDir): Product
    {
        $productDir = rtrim($productDir, '/');
        $slug = basename($productDir);

        $raw = file_get_contents($productDir . '/product.md');
        if ($raw === false) {
            throw new ImportException("Could not read product.md in '{$slug}'.");
        }

        [$meta, $markdown] = $this->splitFrontMatter($raw, $slug);

        $this->validate($meta, $slug);

        $product = new Product();
        $product->slug = $slug;
        $product->dir = $productDir;
        $product->meta = $meta;
        $product->markdown = $markdown;
        $product->images = $this->listFiles($productDir . '/images');
        $product->downloads = $this->listFiles($productDir . '/downloads');

        return $product;
    }

    /**
     * Separate the front matter block from the Markdown body.
     *
     * @return array{0: array<string,string>, 1: string}
     */
    private function splitFrontMatter(string $raw, string $slug): array
    {
        $text = preg_replace('/^\xEF\xBB\xBF/', '', $raw); // strip a UTF-8 BOM if present
        $text = str_replace("\r\n", "\n", $text);
        $lines = explode("\n", $text);

        if (trim($lines[0]) !== '---') {
            throw new ImportException(
                "Missing front matter in '{$slug}'.\n" .
                "product.md must start with a '---' line followed by the metadata."
            );
        }

        $meta = [];
        $i = 1;
        $closed = false;

        for (; $i < count($lines); $i++) {
            $line = $lines[$i];

            if (trim($line) === '---') {
                $closed = true;
                $i++;
                break;
            }

            if (trim($line) === '') {
                continue;
            }

            $pos = strpos($line, ':');
            if ($pos === false) {
                throw new ImportException(
                    "Bad metadata line in '{$slug}': {$line}\n" .
                    "Every metadata line must look like 'key: value'."
                );
            }

            $key = strtolower(trim(substr($line, 0, $pos)));
            $value = trim(substr($line, $pos + 1));
            $value = trim($value, "\"'"); // allow optional quotes around the value

            $meta[$key] = $value;
        }

        if (!$closed) {
            throw new ImportException(
                "Front matter in '{$slug}' is never closed.\n" .
                "Add a second '---' line after the metadata."
            );
        }

        $markdown = implode("\n", array_slice($lines, $i));

        return [$meta, trim($markdown)];
    }

    private function validate(array $meta, string $slug): void
    {
        foreach (self::REQUIRED_FIELDS as $field) {
            if (!isset($meta[$field]) || $meta[$field] === '') {
                throw new ImportException("Missing field: {$field} (in product '{$slug}')");
            }
        }

        if (!is_numeric($meta['price'])) {
            throw new ImportException(
                "Invalid price in '{$slug}': {$meta['price']}\n" .
                "Price must be a number, for example: price: 149"
            );
        }

        if (isset($meta['status']) && $meta['status'] !== '') {
            $status = strtolower($meta['status']);
            if ($status !== 'enabled' && $status !== 'disabled') {
                throw new ImportException(
                    "Invalid status in '{$slug}': {$meta['status']}\n" .
                    "Status must be either 'enabled' or 'disabled'."
                );
            }
        }

        // An unknown field is almost always a typo. Warn, but do not stop.
        foreach (array_keys($meta) as $key) {
            if (!in_array($key, self::KNOWN_FIELDS, true)) {
                fwrite(STDERR, "Warning: unknown metadata field '{$key}' in '{$slug}' (ignored).\n");
            }
        }
    }

    /**
     * List the plain file names inside a folder, sorted. Missing folder is fine.
     *
     * @return string[]
     */
    private function listFiles(string $dir): array
    {
        if (!is_dir($dir)) {
            return [];
        }

        $files = [];
        foreach (scandir($dir) as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }
            if (is_file($dir . '/' . $entry)) {
                $files[] = $entry;
            }
        }

        sort($files);

        return $files;
    }
}
