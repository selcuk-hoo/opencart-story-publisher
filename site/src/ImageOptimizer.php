<?php

/**
 * Copies an image into place, shrinking it first when it is too large.
 *
 * Photos from a phone or camera are often several thousand pixels wide and a
 * few megabytes each. Publishing them untouched makes the product page slow.
 * The author should never have to resize anything by hand (talimatlar.md),
 * so the importer does it: images wider than a limit are scaled down and
 * re-encoded. Images that are already small enough are copied as they are.
 *
 * The source files in products/ are never changed. Only the copy that goes
 * into OpenCart is optimized.
 */
class ImageOptimizer
{
    private int $maxWidth;
    private int $quality;

    public function __construct(int $maxWidth, int $quality)
    {
        $this->maxWidth = $maxWidth;
        $this->quality = $quality;
    }

    /**
     * Copy $from to $to, scaling down to the maximum width when needed.
     *
     * Returns false only when the file could not be written at all.
     */
    public function copy(string $from, string $to): bool
    {
        $info = @getimagesize($from);

        // Not an image we can read, or GD is missing: copy the file as-is.
        if ($info === false || !extension_loaded('gd')) {
            return @copy($from, $to);
        }

        [$width, $height] = $info;
        $type = $info[2];

        $canResize = in_array($type, [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP], true);
        if (!$canResize || $width <= $this->maxWidth) {
            // Already small enough, or a format we do not touch: copy as-is.
            return @copy($from, $to);
        }

        $src = $this->load($from, $type);
        if (!$src) {
            return @copy($from, $to);
        }

        $newWidth = $this->maxWidth;
        $newHeight = (int) round($height * $this->maxWidth / $width);

        $dst = imagecreatetruecolor($newWidth, $newHeight);
        $this->keepTransparency($dst, $type);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        return $this->save($dst, $to, $type);
    }

    private function load(string $file, int $type)
    {
        switch ($type) {
            case IMAGETYPE_JPEG:
                return @imagecreatefromjpeg($file);
            case IMAGETYPE_PNG:
                return @imagecreatefrompng($file);
            case IMAGETYPE_WEBP:
                return @imagecreatefromwebp($file);
        }
        return false;
    }

    private function save($image, string $file, int $type): bool
    {
        switch ($type) {
            case IMAGETYPE_JPEG:
                return imagejpeg($image, $file, $this->quality);
            case IMAGETYPE_PNG:
                return imagepng($image, $file);   // PNG is lossless; no quality argument
            case IMAGETYPE_WEBP:
                return imagewebp($image, $file, $this->quality);
        }
        return false;
    }

    private function keepTransparency($image, int $type): void
    {
        if ($type === IMAGETYPE_PNG || $type === IMAGETYPE_WEBP) {
            imagealphablending($image, false);
            imagesavealpha($image, true);
        }
    }
}
