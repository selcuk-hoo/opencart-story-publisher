<?php

/**
 * Converts the Markdown story into HTML.
 *
 * Markdown conversion is a real problem worth a library, so we use Parsedown
 * (a single file in lib/). See talimatlar.md, "External Libraries".
 *
 * Image handling: the author writes plain references such as ![](hero.jpg).
 * The importer copies the images into OpenCart, so here we only rewrite the
 * <img src="..."> paths to the public URL. The author never types an
 * OpenCart path (docs/SPECIFICATION.md, section 8).
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

        return $html;
    }

    /**
     * Render the story as tabs, one tab per top-level "# " heading.
     *
     * The heading text becomes the tab label and everything under it (until
     * the next "# ") becomes the tab body. The markup is self-contained
     * Bootstrap 5, which OpenCart 4 already loads, so no template is touched
     * (talimatlar.md, "Source of Truth").
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
