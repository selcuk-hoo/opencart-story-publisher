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
