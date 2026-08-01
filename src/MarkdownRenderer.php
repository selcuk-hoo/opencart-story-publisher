<?php

/**
 * Converts the Markdown story into HTML.
 *
 * Markdown conversion is a real problem worth a library, so we use Parsedown
 * (a single file in lib/). See talimatlar.md, "External Libraries".
 *
 * Image handling: the author writes plain references such as ![](hero.jpg).
 * The builder copies the images next to the page, so here we only rewrite the
 * <img src="..."> paths to their published location. The author never types a
 * site path (docs/SPECIFICATION.md).
 */
class MarkdownRenderer
{
    private Parsedown $parsedown;

    public function __construct()
    {
        $this->parsedown = new Parsedown();
        $this->parsedown->setSafeMode(false); // the author's own content is trusted
    }

    /**
     * Render Markdown to HTML.
     *
     * @param array<string,string> $imageUrls Maps an image file name to its
     *                                         final public URL. Any <img> that
     *                                         points at a listed file name is
     *                                         rewritten to that URL.
     */
    public function render(string $markdown, array $imageUrls = []): string
    {
        $html = $this->parsedown->text($markdown);

        if ($imageUrls) {
            $html = $this->rewriteImageUrls($html, $imageUrls);
        }

        $html = $this->buildGalleries($html);

        return $this->makeImagesResponsive($html);
    }

    /**
     * Turn runs of neighbouring images into a responsive gallery grid.
     *
     * When the author writes two or more images next to each other, they are
     * shown side by side instead of stacked. A single image is left alone. No
     * new syntax is needed: the author just puts the images together
     * (docs/ROADMAP.md, "Automatic galleries").
     */
    private function buildGalleries(string $html): string
    {
        // Case 1: several images inside one paragraph (consecutive lines,
        // no blank line between them).
        $html = preg_replace_callback(
            '#<p>\s*((?:<img\b[^>]*>\s*){2,})</p>#i',
            fn($m) => $this->galleryFrom($m[1]),
            $html
        );

        // Case 2: several single-image paragraphs in a row (a blank line
        // between each image).
        $html = preg_replace_callback(
            '#(?:<p>\s*<img\b[^>]*>\s*</p>\s*){2,}#i',
            fn($m) => $this->galleryFrom($m[0]),
            $html
        );

        return $html;
    }

    /** Build a responsive grid from all the images found in an HTML chunk. */
    private function galleryFrom(string $chunk): string
    {
        preg_match_all('#<img\b[^>]*>#i', $chunk, $matches);
        $images = $matches[0];

        // Two images sit two-per-row; three or more sit three-per-row on wide
        // screens. On phones they are always two-per-row.
        $colMd = count($images) === 2 ? 6 : 4;

        $cells = '';
        foreach ($images as $img) {
            $cells .= '<div class="col-6 col-md-' . $colMd . '">' . $img . '</div>';
        }

        return '<div class="row g-2 story-gallery">' . $cells . '</div>';
    }

    /**
     * Add the "img-fluid" class to images that do not already have a class,
     * so the site's stylesheet keeps them inside their container instead of
     * overflowing the page.
     */
    private function makeImagesResponsive(string $html): string
    {
        return preg_replace(
            '/<img\b(?![^>]*\bclass=)/i',
            '<img class="img-fluid"',
            $html
        );
    }

    /**
     * Render the story as tabs, one tab per top-level "# " heading.
     *
     * The heading text becomes the tab label and everything under it (until
     * the next "# ") becomes the tab body. The markup uses simple, framework-
     * free classes that the site's own CSS + tabs.js style and drive.
     *
     * If the story has no "# " headings, it falls back to a plain render.
     *
     * @param array<string,string> $imageUrls See render().
     * @param string $idPrefix Prefix for the HTML ids, so several products on
     *                         one page never collide. Usually the product slug.
     */
    public function renderTabs(string $markdown, array $imageUrls = [], string $idPrefix = 'story'): string
    {
        [$preamble, $sections] = $this->splitByHeading($markdown);

        if (!$sections) {
            return $this->render($markdown, $imageUrls);
        }

        $safePrefix = preg_replace('/[^a-z0-9\-]+/i', '-', $idPrefix);

        $tabs = '';
        $panes = '';
        foreach ($sections as $i => $section) {
            $id = $safePrefix . '-tab-' . $i;
            $active = $i === 0 ? ' active' : '';
            $label = htmlspecialchars($section['title'], ENT_QUOTES);
            $body = $this->render($section['body'], $imageUrls);

            $tabs .= '<li class="nav-item" role="presentation">'
                . '<a class="nav-link' . $active . '" data-bs-toggle="tab" '
                . 'href="#' . $id . '" role="tab">' . $label . '</a></li>';

            $panes .= '<div class="tab-pane' . $active . '" id="' . $id . '" role="tabpanel">'
                . $body . '</div>';
        }

        $html = '';
        if (trim($preamble) !== '') {
            $html .= $this->render($preamble, $imageUrls);
        }
        $html .= '<ul class="nav nav-tabs" role="tablist">' . $tabs . '</ul>'
            . '<div class="tab-content">' . $panes . '</div>';

        return $html;
    }

    /**
     * Split the Markdown into any leading text plus one section per "# " heading.
     *
     * @return array{0: string, 1: array<int,array{title:string,body:string}>}
     */
    private function splitByHeading(string $markdown): array
    {
        $lines = explode("\n", str_replace("\r\n", "\n", $markdown));

        $preamble = [];
        $sections = [];
        $current = null;

        foreach ($lines as $line) {
            if (preg_match('/^#\s+(.+?)\s*$/', $line, $m)) {
                if ($current !== null) {
                    $sections[] = $current;
                }
                $current = ['title' => $m[1], 'body' => []];
            } elseif ($current === null) {
                $preamble[] = $line;
            } else {
                $current['body'][] = $line;
            }
        }

        if ($current !== null) {
            $sections[] = $current;
        }

        foreach ($sections as &$section) {
            $section['body'] = trim(implode("\n", $section['body']));
        }
        unset($section);

        return [trim(implode("\n", $preamble)), $sections];
    }

    private function rewriteImageUrls(string $html, array $imageUrls): string
    {
        return preg_replace_callback(
            '/(<img\b[^>]*\bsrc=")([^"]*)(")/i',
            function ($m) use ($imageUrls) {
                $src = $m[2];
                $name = basename($src);
                if (isset($imageUrls[$name])) {
                    return $m[1] . $imageUrls[$name] . $m[3];
                }
                return $m[0];
            },
            $html
        );
    }
}
