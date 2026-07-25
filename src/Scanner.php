<?php

/**
 * Finds product folders.
 *
 * A product folder is any direct subdirectory of the products directory
 * that contains a product.md file. See docs/ARCHITECTURE.md.
 */
class Scanner
{
    private string $productsDir;

    public function __construct(string $productsDir)
    {
        $this->productsDir = rtrim($productsDir, '/');
    }

    /**
     * Return the absolute path of every product folder, sorted by name.
     *
     * @return string[]
     */
    public function findProducts(): array
    {
        if (!is_dir($this->productsDir)) {
            throw new ImportException(
                "Products directory not found: {$this->productsDir}\n" .
                "Create the folder or fix PRODUCTS_DIR in config.php."
            );
        }

        $found = [];

        foreach (scandir($this->productsDir) as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $dir = $this->productsDir . '/' . $entry;

            if (is_dir($dir) && is_file($dir . '/product.md')) {
                $found[] = $dir;
            }
        }

        sort($found);

        return $found;
    }
}
