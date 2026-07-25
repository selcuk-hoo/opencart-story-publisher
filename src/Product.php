<?php

/**
 * One product, read from a single product folder.
 *
 * This is a plain data holder. It does not talk to OpenCart and it does
 * not touch the database. The Publisher is responsible for that.
 */
class Product
{
    /** Folder name, e.g. "servo-mount". Also used as the URL keyword. */
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

    /**
     * Read a metadata value, or return a default when it is missing.
     */
    public function meta(string $key, string $default = ''): string
    {
        return isset($this->meta[$key]) ? $this->meta[$key] : $default;
    }
}
