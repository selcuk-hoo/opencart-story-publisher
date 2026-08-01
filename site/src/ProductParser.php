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
    /**
     * Metadata fields that every product must provide.
     *
     * "model" is not here on purpose: when it is missing it defaults to the
     * folder name, so the author can name a folder "klp-001" instead of
     * inventing a code by hand.
     */
    private const REQUIRED_FIELDS = ['name', 'price'];

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

        // Collect every problem, so the author sees them all at once instead
        // of fixing one, re-running, and finding the next.
        $errors = $this->collectErrors($meta);
        if ($errors) {
            throw new ImportException($this->formatErrors($slug, $errors));
        }

        // The model is the product's unique id. When the author does not set
        // one, the folder name is used (it is already unique and stable).
        if (!isset($meta['model']) || $meta['model'] === '') {
            $meta['model'] = $slug;
        }

        $product = new Product();
        $product->slug = $slug;
        $product->dir = $productDir;
        $product->meta = $meta;
        $product->markdown = $markdown;
        $product->images = $this->listFiles($productDir . '/images');
        $product->downloads = $this->listFiles($productDir . '/downloads');
        $product->warnings = $this->collectWarnings($meta);

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

    /**
     * Return every metadata problem as a short, fixable message.
     *
     * @return string[]
     */
    private function collectErrors(array $meta): array
    {
        $errors = [];

        foreach (self::REQUIRED_FIELDS as $field) {
            if (!isset($meta[$field]) || $meta[$field] === '') {
                $errors[] = "Missing field: {$field}";
            }
        }

        // Only check the value when the field is actually there; a missing
        // field is already reported above.
        if (isset($meta['price']) && $meta['price'] !== '' && !is_numeric($meta['price'])) {
            $errors[] = "Invalid price '{$meta['price']}' (must be a number, e.g. price: 149)";
        }

        if (isset($meta['status']) && $meta['status'] !== '') {
            $status = strtolower($meta['status']);
            if ($status !== 'enabled' && $status !== 'disabled') {
                $errors[] = "Invalid status '{$meta['status']}' (use 'enabled' or 'disabled')";
            }
        }

        return $errors;
    }

    private function formatErrors(string $slug, array $errors): string
    {
        $count = count($errors);
        $head = $count === 1 ? '1 problem:' : "{$count} problems:";
        $lines = array_map(fn($error) => "  - {$error}", $errors);

        return $head . "\n" . implode("\n", $lines) .
            "\n  Fix in site/products/{$slug}/product.md";
    }

    /**
     * Return non-fatal warnings. An unknown field is almost always a typo,
     * so we point it out but still import the product.
     *
     * @return string[]
     */
    private function collectWarnings(array $meta): array
    {
        $warnings = [];

        foreach (array_keys($meta) as $key) {
            if (!in_array($key, self::KNOWN_FIELDS, true)) {
                $warnings[] = "Unknown field '{$key}' was ignored.";
            }
        }

        return $warnings;
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
