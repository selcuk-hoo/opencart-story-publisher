<?php

/**
 * Builds a static web site from the product folders.
 *
 * This is the static counterpart of Publisher: instead of writing to an
 * OpenCart database, it writes plain HTML files into an output directory.
 * The rest of the pipeline is reused unchanged — Scanner, ProductParser,
 * MarkdownRenderer (tabs + galleries) and ImageOptimizer.
 *
 * The site has no framework and no external requests: a small local CSS file
 * and a tiny tab script (assets/) are copied into the output, so it works
 * offline and can be hosted anywhere (Netlify, GitHub Pages, ...).
 *
 * Downloadable files are intentionally NOT published: in a "buy to download"
 * model the file is delivered by the payment provider after purchase, so the
 * page only lists what the buyer will receive.
 */
class SiteBuilder
{
    private Scanner $scanner;
    private ProductParser $parser;
    private MarkdownRenderer $renderer;
    private ImageOptimizer $optimizer;

    private string $assetsDir;
    private string $outputDir;
    private string $siteName;
    private string $tagline;
    private string $currency;

    public function __construct(
        Scanner $scanner,
        ProductParser $parser,
        MarkdownRenderer $renderer,
        ImageOptimizer $optimizer,
        string $assetsDir,
        string $outputDir,
        string $siteName,
        string $tagline,
        string $currency
    ) {
        $this->scanner = $scanner;
        $this->parser = $parser;
        $this->renderer = $renderer;
        $this->optimizer = $optimizer;
        $this->assetsDir = rtrim($assetsDir, '/');
        $this->outputDir = rtrim($outputDir, '/');
        $this->siteName = $siteName;
        $this->tagline = $tagline;
        $this->currency = $currency;
    }

    /**
     * Build the whole site. Returns a per-product report.
     *
     * @return array<int,array{slug:string,ok:bool,message:string}>
     */
    public function build(): array
    {
        $this->resetOutput();
        $this->copyAssets();

        $report = [];
        $products = [];

        foreach ($this->scanner->findProducts() as $dir) {
            $slug = basename($dir);
            try {
                $product = $this->parser->parse($dir);
                $this->writeProductPage($product);
                $products[] = $product;
                $report[] = ['slug' => $slug, 'ok' => true, 'message' => "Built '{$slug}'."];
            } catch (ImportException $e) {
                $report[] = ['slug' => $slug, 'ok' => false, 'message' => $e->getMessage()];
            }
        }

        $this->writeIndexPage($products);

        return $report;
    }

    // --- Pages -----------------------------------------------------------

    private function writeProductPage(Product $product): void
    {
        // Copy (and shrink) the images next to the page.
        $imageUrls = [];
        if ($product->images) {
            $target = $this->outputDir . '/' . $product->slug . '/images';
            $this->makeDir($target);
            foreach ($product->images as $name) {
                $this->optimizer->copy($product->dir . '/images/' . $name, $target . '/' . $name);
                $imageUrls[$name] = 'images/' . $name;
            }
        }

        $story = $this->renderer->renderTabs($product->markdown, $imageUrls, $product->slug);

        $name = $this->esc($product->meta('name'));
        $summary = $this->esc($product->meta('summary'));
        $cover = $product->meta('image');
        $coverHtml = $cover !== ''
            ? '<img class="product-cover" src="images/' . $this->esc($cover) . '" alt="' . $name . '">'
            : '';

        $pack = '';
        if ($product->downloads) {
            $items = '';
            foreach ($product->downloads as $file) {
                $items .= '<li>' . $this->esc($file) . '</li>';
            }
            $pack = '<div class="pack"><h4>Bu pakette</h4><ul>' . $items . '</ul></div>';
        }

        $content = <<<HTML
<div class="container product-wrap">
<nav class="crumb"><a href="../index.html">&larr; Tüm ürünler</a></nav>
<article class="product">
  <div class="product-hero">
    {$coverHtml}
    <div class="product-hero-info">
      <h1>{$name}</h1>
      <p class="product-summary">{$summary}</p>
      <div class="product-price">{$this->price($product)}</div>
      <a class="buy-btn" href="#">Satın Al</a>
      <p class="buy-note">Ödeme sonrası dosyalar güvenli şekilde teslim edilir. (Ödeme entegrasyonu bir sonraki adımda.)</p>
      {$pack}
    </div>
  </div>
  <section class="product-story">{$story}</section>
</article>
</div>
HTML;

        $html = $this->layout($product->meta('name'), $content, '../');
        $this->makeDir($this->outputDir . '/' . $product->slug);
        file_put_contents($this->outputDir . '/' . $product->slug . '/index.html', $html);
    }

    /**
     * @param Product[] $products
     */
    private function writeIndexPage(array $products): void
    {
        // Group by category, keeping first-seen order.
        $groups = [];
        foreach ($products as $product) {
            $category = $product->meta('category', 'Diğer');
            $groups[$category][] = $product;
        }

        $navItems = '<li><a class="active" data-filter="__all__">Tümü'
            . '<span class="count">' . count($products) . '</span></a></li>';
        $sections = '';
        foreach ($groups as $category => $items) {
            $cat = $this->esc($category);
            $navItems .= '<li><a data-filter="' . $cat . '">' . $cat
                . '<span class="count">' . count($items) . '</span></a></li>';

            $cards = '';
            foreach ($items as $product) {
                $cards .= $this->card($product);
            }
            $sections .= '<section class="category" data-category="' . $cat . '"><h2>' . $cat . '</h2>'
                . '<div class="product-grid">' . $cards . '</div></section>';
        }

        $content = '<section class="masthead"><div class="container">'
            . '<h1>' . $this->esc($this->siteName) . '</h1>'
            . '<p class="tagline">' . $this->esc($this->tagline) . '</p></div></section>'
            . '<div class="container"><div class="catalog-layout">'
            . '<aside class="cat-nav"><h4>Kategoriler</h4><ul>' . $navItems . '</ul></aside>'
            . '<div class="catalog-main">' . $sections . '</div>'
            . '</div></div>';

        $html = $this->layout('Ürünler', $content, '');
        file_put_contents($this->outputDir . '/index.html', $html);
    }

    private function card(Product $product): string
    {
        $slug = rawurlencode($product->slug);
        $name = $this->esc($product->meta('name'));
        $summary = $this->esc($product->meta('summary'));
        $cover = $product->meta('image');
        $img = $cover !== ''
            ? '<div class="thumb"><img src="' . $slug . '/images/' . $this->esc($cover) . '" alt="' . $name . '"></div>'
            : '';

        return <<<HTML
<a class="card" href="{$slug}/index.html">
  {$img}
  <div class="card-body">
    <h3>{$name}</h3>
    <p class="summary">{$summary}</p>
    <span class="price">{$this->price($product)}</span>
  </div>
</a>
HTML;
    }

    // --- Layout & helpers -----------------------------------------------

    private function layout(string $title, string $content, string $base): string
    {
        $siteName = $this->esc($this->siteName);
        $title = $this->esc($title);
        $year = date('Y');

        return <<<HTML
<!doctype html>
<html lang="tr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{$title} · {$siteName}</title>
<link rel="stylesheet" href="{$base}assets/style.css">
</head>
<body>
<header class="site-header"><div class="container"><a class="brand" href="{$base}index.html">{$siteName}</a></div></header>
<main>
{$content}
</main>
<footer class="site-footer"><div class="container">{$siteName} · {$year}</div></footer>
<script src="{$base}assets/tabs.js"></script>
</body>
</html>
HTML;
    }

    private function price(Product $product): string
    {
        $value = (float) $product->meta('price');
        return number_format($value, 0, ',', '.') . ' ' . $this->currency;
    }

    private function esc(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }

    private function copyAssets(): void
    {
        $target = $this->outputDir . '/assets';
        $this->makeDir($target);
        foreach (['style.css', 'tabs.js'] as $file) {
            $from = $this->assetsDir . '/' . $file;
            if (is_file($from)) {
                copy($from, $target . '/' . $file);
            }
        }
    }

    private function resetOutput(): void
    {
        if (is_dir($this->outputDir)) {
            $this->removeDir($this->outputDir);
        }
        $this->makeDir($this->outputDir);
    }

    private function makeDir(string $dir): void
    {
        if (!is_dir($dir) && !@mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new ImportException("Could not create folder: {$dir}");
        }
    }

    private function removeDir(string $dir): void
    {
        foreach (scandir($dir) as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }
            $path = $dir . '/' . $entry;
            is_dir($path) ? $this->removeDir($path) : unlink($path);
        }
        rmdir($dir);
    }
}
