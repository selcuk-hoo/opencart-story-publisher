<?php

/**
 * Small test runner for the parts that do not need a database.
 *
 * Run with:  php tests/run.php
 *
 * OpenCartApi and the database side of Publisher are not covered here,
 * because they need a real OpenCart installation to run against.
 */

require __DIR__ . '/../lib/Parsedown.php';
require __DIR__ . '/../src/ImportException.php';
require __DIR__ . '/../src/Product.php';
require __DIR__ . '/../src/Scanner.php';
require __DIR__ . '/../src/ProductParser.php';
require __DIR__ . '/../src/MarkdownRenderer.php';

$tests = 0;
$failures = 0;

function check(string $name, bool $condition): void
{
    global $tests, $failures;
    $tests++;
    if ($condition) {
        echo "  ok   {$name}\n";
    } else {
        $failures++;
        echo "  FAIL {$name}\n";
    }
}

/** Run a callback and return the ImportException message it throws, or ''. */
function catchError(callable $fn): string
{
    try {
        $fn();
    } catch (ImportException $e) {
        return $e->getMessage();
    }
    return '';
}

// A throwaway product folder for the tests.
$tmp = sys_get_temp_dir() . '/story-publisher-tests-' . getmypid();
@mkdir($tmp, 0777, true);

function makeProduct(string $base, string $slug, string $content): string
{
    $dir = $base . '/' . $slug;
    @mkdir($dir . '/images', 0777, true);
    @mkdir($dir . '/downloads', 0777, true);
    file_put_contents($dir . '/product.md', $content);
    return $dir;
}

// --- Scanner ------------------------------------------------------------
echo "Scanner\n";
makeProduct($tmp, 'alpha', "---\nname: A\nmodel: A1\nprice: 10\n---\nBody");
makeProduct($tmp, 'beta', "---\nname: B\nmodel: B1\nprice: 20\n---\nBody");
@mkdir($tmp . '/not-a-product'); // no product.md, must be ignored

$scanner = new Scanner($tmp);
$found = $scanner->findProducts();
check('finds only folders with product.md', count($found) === 2);
check('folders are sorted', basename($found[0]) === 'alpha' && basename($found[1]) === 'beta');

$missingDirError = catchError(fn() => (new Scanner($tmp . '/nope'))->findProducts());
check('missing products dir gives a clear error', str_contains($missingDirError, 'Products directory not found'));

// --- ProductParser ------------------------------------------------------
echo "ProductParser\n";
$parser = new ProductParser();

$dir = makeProduct($tmp, 'gamma',
    "---\nname: Servo Mount\nmodel: SMP001\nprice: 149\ncategory: 3D Models\nimage: hero.jpg\nstatus: enabled\n---\n# Problem\n\nText here.");
file_put_contents($dir . '/images/hero.jpg', 'x');
file_put_contents($dir . '/downloads/part.stl', 'x');

$product = $parser->parse($dir);
check('reads name', $product->meta('name') === 'Servo Mount');
check('reads model', $product->meta('model') === 'SMP001');
check('reads price', $product->meta('price') === '149');
check('slug is the folder name', $product->slug === 'gamma');
check('body excludes front matter', str_starts_with($product->markdown, '# Problem'));
check('lists images', $product->images === ['hero.jpg']);
check('lists downloads', $product->downloads === ['part.stl']);

$dir = makeProduct($tmp, 'noprice', "---\nname: X\nmodel: X1\n---\nBody");
$err = catchError(fn() => $parser->parse($dir));
check('missing price is reported', str_contains($err, 'Missing field: price'));

$dir = makeProduct($tmp, 'badprice', "---\nname: X\nmodel: X1\nprice: cheap\n---\nBody");
$err = catchError(fn() => $parser->parse($dir));
check('non-numeric price is reported', str_contains($err, 'Invalid price'));

$dir = makeProduct($tmp, 'badstatus', "---\nname: X\nmodel: X1\nprice: 5\nstatus: maybe\n---\nBody");
$err = catchError(fn() => $parser->parse($dir));
check('bad status is reported', str_contains($err, 'Invalid status'));

$dir = makeProduct($tmp, 'nofront', "no front matter here");
$err = catchError(fn() => $parser->parse($dir));
check('missing front matter is reported', str_contains($err, 'Missing front matter'));

$dir = makeProduct($tmp, 'quoted', "---\nname: \"Quoted Name\"\nmodel: Q1\nprice: 5\n---\nBody");
$product = $parser->parse($dir);
check('quotes around a value are stripped', $product->meta('name') === 'Quoted Name');

// --- MarkdownRenderer ---------------------------------------------------
echo "MarkdownRenderer\n";
$renderer = new MarkdownRenderer();

$html = $renderer->render("# Title\n\nSome **bold** text.");
check('renders headings', str_contains($html, '<h1>Title</h1>'));
check('renders bold', str_contains($html, '<strong>bold</strong>'));

$html = $renderer->render(
    "![](prototype.jpg)",
    ['prototype.jpg' => 'image/catalog/story/gamma/prototype.jpg']
);
check('rewrites image src to the public URL',
    str_contains($html, 'src="image/catalog/story/gamma/prototype.jpg"'));

$html = $renderer->render("![](unknown.jpg)", ['prototype.jpg' => 'x']);
check('leaves unknown image src untouched', str_contains($html, 'src="unknown.jpg"'));

// --- Result -------------------------------------------------------------
echo "\n{$tests} checks, {$failures} failed.\n";

// Clean up.
exec('rm -rf ' . escapeshellarg($tmp));

exit($failures > 0 ? 1 : 0);
