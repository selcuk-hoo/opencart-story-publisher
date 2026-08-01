<?php

/**
 * One product, read from a single product folder.
 *
 * This is a plain data holder. It does no rendering and writes nothing;
 * the SiteBuilder turns it into HTML pages.
 */
class Product
{
    /** Folder name, e.g. "servo-yatagi". Also used as the URL slug. */
    public string $slug;

    /** Absolute path to the product folder. */
    public string $dir;

    /** Metadata from the front matter (name, model, price, category, image, status). */
    public array $meta = [];

    /** The Markdown story, without the front matter. */
    public string $markdown = '';

    /** Image file names found inside images/, e.g. ["hero.jpg", "prototype.jpg"]. */
    public array $images = [];

    /** Download file names found inside downloads/, e.g. ["servo_mount.stl"]. */
    public array $downloads = [];

    /** Non-fatal warnings found while reading the product, e.g. unknown fields. */
    public array $warnings = [];

    /**
     * Read a metadata value, or return a default when it is missing.
     */
    public function meta(string $key, string $default = ''): string
    {
        return isset($this->meta[$key]) ? $this->meta[$key] : $default;
    }
}
