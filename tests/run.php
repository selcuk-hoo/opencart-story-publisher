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
require __DIR__ . '/../src/ImageOptimizer.php';

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

$dir = makeProduct($tmp, 'manyerrors', "---\nname: X\nmodel: M1\nstatus: maybe\n---\nBody");
$err = catchError(fn() => $parser->parse($dir));
check('reports all problems at once',
    str_contains($err, 'Missing field: price') && str_contains($err, 'Invalid status'));
check('error points at the product.md file', str_contains($err, 'products/manyerrors/product.md'));

$dir = makeProduct($tmp, 'unknownfield', "---\nname: X\nmodel: M1\nprice: 5\ncolour: blue\n---\nBody");
$product = $parser->parse($dir);
check('unknown field is a warning, not an error',
    count($product->warnings) === 1 && str_contains($product->warnings[0], 'colour'));

$dir = makeProduct($tmp, 'klp-001', "---\nname: Beton Kalıbı\nprice: 5\n---\nBody");
$product = $parser->parse($dir);
check('model is optional', $product->warnings === []);
check('model defaults to the folder name', $product->meta('model') === 'klp-001');

$dir = makeProduct($tmp, 'explicit', "---\nname: X\nmodel: ABC-9\nprice: 5\n---\nBody");
$product = $parser->parse($dir);
check('an explicit model is kept', $product->meta('model') === 'ABC-9');

$dir = makeProduct($tmp, 'quoted', "---\nname: \"Quoted Name\"\nmodel: Q1\nprice: 5\n---\nBody");
$product = $parser->parse($dir);
check('quotes around a value are stripped', $product->meta('name') === 'Quoted Name');
check('a clean product has no warnings', $product->warnings === []);

$dir = makeProduct($tmp, 'withsummary', "---\nname: X\nmodel: SM1\nprice: 5\nsummary: Kısa bir özet.\n---\nBody");
$product = $parser->parse($dir);
check('reads the summary field', $product->meta('summary') === 'Kısa bir özet.');

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

$html = $renderer->render("![](hero.jpg)");
check('adds img-fluid so images stay inside the page', str_contains($html, 'class="img-fluid"'));

// --- MarkdownRenderer: galleries ---------------------------------------
echo "MarkdownRenderer (galleries)\n";

$html = $renderer->render("![](a.jpg)\n\n![](b.jpg)");
check('two images become a gallery', str_contains($html, 'story-gallery'));
check('gallery has one cell per image', substr_count($html, 'col-md-') === 2);
check('two images use half-width columns', str_contains($html, 'col-md-6'));

$html = $renderer->render("![](a.jpg)\n\n![](b.jpg)\n\n![](c.jpg)");
check('three images use third-width columns', str_contains($html, 'col-md-4'));
check('three images make three cells', substr_count($html, 'col-md-4') === 3);

$html = $renderer->render("![](a.jpg)\n![](b.jpg)");
check('images on consecutive lines also form a gallery', str_contains($html, 'story-gallery'));

$html = $renderer->render("![](only.jpg)");
check('a single image is not a gallery', !str_contains($html, 'story-gallery'));

$html = $renderer->render("![](a.jpg)\n\nAralarında metin var.\n\n![](b.jpg)");
check('images split by text are not merged', !str_contains($html, 'story-gallery'));

// --- MarkdownRenderer: tabs --------------------------------------------
echo "MarkdownRenderer (tabs)\n";

$story = "# Problem\n\nIt flexed.\n\n# Dream\n\nSolid.\n\n# Sharing\n\nHere it is.";
$html = $renderer->renderTabs($story, [], 'servo-mount');
check('builds a tab list', str_contains($html, 'nav nav-tabs'));
check('one tab per heading', substr_count($html, 'data-bs-toggle="tab"') === 3);
check('heading text becomes the tab label', str_contains($html, '>Problem</a>'));
check('the heading line is not repeated in the body', !str_contains($html, '<h1>Problem</h1>'));
check('first tab is active', str_contains($html, 'nav-link active'));
check('ids are prefixed with the slug', str_contains($html, 'id="servo-mount-tab-0"'));
check('section body is rendered', str_contains($html, '<p>It flexed.</p>'));

$html = $renderer->renderTabs("Just a paragraph, no headings.", []);
check('falls back to plain render without headings', !str_contains($html, 'nav-tabs'));

$html = $renderer->renderTabs("![](prototype.jpg)\n\n# Problem\n\nText.",
    ['prototype.jpg' => 'image/catalog/story/x/prototype.jpg'], 'x');
check('preamble before the first heading is kept',
    str_contains($html, 'src="image/catalog/story/x/prototype.jpg"'));

// --- ImageOptimizer -----------------------------------------------------
echo "ImageOptimizer\n";

if (!extension_loaded('gd')) {
    echo "  (skipped: the GD extension is not available)\n";
} else {
    $optimizer = new ImageOptimizer(1600, 82);

    // A wide image must be shrunk to the maximum width, keeping its ratio.
    $bigPath = $tmp . '/big.jpg';
    $big = imagecreatetruecolor(3000, 1500);
    imagejpeg($big, $bigPath);
    $out = $tmp . '/big-out.jpg';
    $optimizer->copy($bigPath, $out);
    $size = getimagesize($out);
    check('shrinks a too-wide image to the max width', $size[0] === 1600);
    check('keeps the aspect ratio when shrinking', $size[1] === 800);

    // A small image must be copied unchanged.
    $smallPath = $tmp . '/small.jpg';
    $small = imagecreatetruecolor(400, 300);
    imagejpeg($small, $smallPath);
    $out = $tmp . '/small-out.jpg';
    $optimizer->copy($smallPath, $out);
    $size = getimagesize($out);
    check('leaves a small image at its original size', $size[0] === 400 && $size[1] === 300);

    // A non-image file is still copied.
    file_put_contents($tmp . '/notes.txt', 'hello');
    $optimizer->copy($tmp . '/notes.txt', $tmp . '/notes-out.txt');
    check('copies a non-image file as-is', file_get_contents($tmp . '/notes-out.txt') === 'hello');
}

// --- Result -------------------------------------------------------------
echo "\n{$tests} checks, {$failures} failed.\n";

// Clean up.
exec('rm -rf ' . escapeshellarg($tmp));

exit($failures > 0 ? 1 : 0);
