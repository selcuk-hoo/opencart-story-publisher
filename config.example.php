<?php

/**
 * Story Publisher configuration.
 *
 * Copy this file to config.php and fill in the values for your own
 * OpenCart 4 installation. config.php is ignored by git because it
 * contains your database password.
 */

// --- OpenCart database -------------------------------------------------
// Copy these straight from your OpenCart config.php.
define('DB_HOSTNAME', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_DATABASE', 'opencart');
define('DB_PORT', '3306');
define('DB_PREFIX', 'oc_');

// --- Where your products live ------------------------------------------
define('PRODUCTS_DIR', __DIR__ . '/products');

// --- OpenCart file locations -------------------------------------------
// The OpenCart "image" folder (the one that contains the "catalog" folder).
define('OPENCART_IMAGE_DIR', '/var/www/opencart/image');

// The public URL prefix OpenCart serves images from. Usually "image/".
define('OPENCART_IMAGE_URL', 'image/');

// The OpenCart download storage folder.
define('OPENCART_DOWNLOAD_DIR', '/var/www/opencart/system/storage/download');

// --- Store and language ------------------------------------------------
// Which language and store new products are created for.
define('OPENCART_LANGUAGE_ID', 1);
define('OPENCART_STORE_ID', 0);

// --- Image optimization ------------------------------------------------
// Images wider than this (in pixels) are scaled down when imported, so big
// camera photos do not slow the product page. The source files are never
// changed. Both lines are optional; these are the defaults.
define('MAX_IMAGE_WIDTH', 1600);
define('IMAGE_QUALITY', 82); // JPEG/WebP quality, 0-100
