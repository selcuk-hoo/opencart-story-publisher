<?php

/**
 * 3D Harikalar Diyarı - static site builder.
 *
 * Generates a self-contained static web site from the product folders. No
 * database and no PHP server are needed to view the result — just open the
 * generated output/ or host it on any static host (Netlify, GitHub Pages,
 * Cloudflare Pages, ...).
 *
 * Usage:
 *   php build.php
 */

if (PHP_SAPI !== 'cli') {
    exit("This script must be run from the command line.\n");
}

// --- Settings ----------------------------------------------------------
const PRODUCTS_DIR   = __DIR__ . '/products';
const OUTPUT_DIR     = __DIR__ . '/output';
const ASSETS_DIR     = __DIR__ . '/assets';
const ABOUT_FILE     = __DIR__ . '/about.md'; // optional "Hakkımızda" page
const SITE_NAME      = '3D Harikalar Diyarı';
const SITE_TAGLINE   = 'Tasarımdan baskıya — her parçanın bir hikâyesi var.';
const CURRENCY       = '₺';
const MAX_IMAGE_WIDTH = 1200; // static pages control their own layout
const IMAGE_QUALITY   = 82;

require __DIR__ . '/lib/Parsedown.php';
require __DIR__ . '/src/ImportException.php';
require __DIR__ . '/src/Product.php';
require __DIR__ . '/src/Scanner.php';
require __DIR__ . '/src/ProductParser.php';
require __DIR__ . '/src/MarkdownRenderer.php';
require __DIR__ . '/src/ImageOptimizer.php';
require __DIR__ . '/src/SiteBuilder.php';

$aboutMarkdown = is_file(ABOUT_FILE) ? (string) file_get_contents(ABOUT_FILE) : '';

try {
    $builder = new SiteBuilder(
        new Scanner(PRODUCTS_DIR),
        new ProductParser(),
        new MarkdownRenderer(),
        new ImageOptimizer(MAX_IMAGE_WIDTH, IMAGE_QUALITY),
        ASSETS_DIR,
        OUTPUT_DIR,
        SITE_NAME,
        SITE_TAGLINE,
        CURRENCY,
        $aboutMarkdown
    );

    $report = $builder->build();
} catch (ImportException $e) {
    fwrite(STDERR, $e->getMessage() . "\n");
    exit(1);
}

$failures = 0;
foreach ($report as $line) {
    if ($line['ok']) {
        echo "OK    {$line['slug']}\n";
    } else {
        $failures++;
        echo "FAIL  {$line['slug']}\n";
        foreach (explode("\n", trim($line['message'])) as $ml) {
            echo "        {$ml}\n";
        }
    }
}

$built = count($report) - $failures;
echo "\n{$built} built, {$failures} failed. Output: " . OUTPUT_DIR . "/index.html\n";

exit($failures > 0 ? 1 : 0);
