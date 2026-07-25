<?php

/**
 * Story Publisher - command line importer.
 *
 * Usage:
 *   php import.php            Import every product folder.
 *   php import.php <slug>     Import a single product folder by name.
 *
 * Before the first run, copy config.example.php to config.php and fill in
 * your OpenCart details.
 */

if (PHP_SAPI !== 'cli') {
    exit("This script must be run from the command line.\n");
}

$configFile = __DIR__ . '/config.php';
if (!is_file($configFile)) {
    fwrite(STDERR,
        "Missing config.php.\n" .
        "Copy config.example.php to config.php and fill in your OpenCart details.\n"
    );
    exit(1);
}
require $configFile;

// Load the Markdown library and our own classes. No autoloader is needed for
// a project this small (talimatlar.md, "Keep It Simple").
require __DIR__ . '/lib/Parsedown.php';
require __DIR__ . '/src/ImportException.php';
require __DIR__ . '/src/Product.php';
require __DIR__ . '/src/Scanner.php';
require __DIR__ . '/src/ProductParser.php';
require __DIR__ . '/src/MarkdownRenderer.php';
require __DIR__ . '/src/ImageOptimizer.php';
require __DIR__ . '/src/OpenCartApi.php';
require __DIR__ . '/src/Publisher.php';

// Image optimization settings. Defaults are used when config.php does not
// set them, so older config files keep working without any change.
if (!defined('MAX_IMAGE_WIDTH')) {
    define('MAX_IMAGE_WIDTH', 1600);
}
if (!defined('IMAGE_QUALITY')) {
    define('IMAGE_QUALITY', 82);
}

$onlySlug = isset($argv[1]) ? $argv[1] : null;

try {
    $publisher = new Publisher(
        new Scanner(PRODUCTS_DIR),
        new ProductParser(),
        new MarkdownRenderer(),
        new OpenCartApi(),
        new ImageOptimizer(MAX_IMAGE_WIDTH, IMAGE_QUALITY),
        OPENCART_IMAGE_DIR,
        OPENCART_IMAGE_URL,
        OPENCART_DOWNLOAD_DIR
    );

    $report = $publisher->importAll($onlySlug);
} catch (ImportException $e) {
    fwrite(STDERR, $e->getMessage() . "\n");
    exit(1);
}

if (!$report) {
    echo $onlySlug !== null
        ? "No product folder named '{$onlySlug}' was found.\n"
        : "No products found in " . PRODUCTS_DIR . "\n";
    exit(1);
}

// Print a short, readable report (docs/SPECIFICATION.md, section 12).
$failures = 0;
foreach ($report as $line) {
    if ($line['ok']) {
        echo "OK   {$line['message']}\n";
    } else {
        $failures++;
        echo "FAIL {$line['slug']}: {$line['message']}\n";
    }
}

$total = count($report);
$ok = $total - $failures;
echo "\n{$ok} succeeded, {$failures} failed, {$total} total.\n";

exit($failures > 0 ? 1 : 0);
