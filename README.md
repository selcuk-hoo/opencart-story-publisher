# Story Publisher

Markdown-based product publisher for OpenCart 4.

Every product is a story. You write the story in one Markdown file, drop in
your images and downloadable files, and run the importer. OpenCart is updated
for you. You never edit HTML, PHP, SQL, or OpenCart templates.

See `docs/SPECIFICATION.md` for the full Version 0.1 specification and
`talimatlar.md` for the design philosophy.

## How a product looks

Each product is a single folder:

```
products/
    servo-mount/
        product.md
        images/
            hero.jpg
            prototype.jpg
            final.jpg
        downloads/
            servo_mount.stl
            source.step
```

`product.md` has metadata at the top and the story below:

```markdown
---
name: Servo Mount Pro
model: SMP001
price: 149
category: 3D Models
image: hero.jpg
status: enabled
---

# Problem

...

# Dream

![](prototype.jpg)
```

- `name`, `model`, and `price` are required. `model` also identifies the
  product: importing the same model again **updates** it instead of creating
  a duplicate.
- `category` must already exist in OpenCart (Version 0.1 does not create
  categories).
- `image` is the main product image and must be a file in `images/`.
- `status` is `enabled` or `disabled` (default: `enabled`).
- Reference images with plain names, e.g. `![](prototype.jpg)`. The importer
  copies them into OpenCart and fixes the paths. Never write OpenCart paths.

## Setup

1. Copy the configuration file and fill it in:

   ```
   cp config.example.php config.php
   ```

   Put your OpenCart database details and folder paths in `config.php`.
   (`config.php` is ignored by git because it holds your password.)

2. Requirements: PHP 8 or newer with the `mysqli` extension, and an
   OpenCart 4 installation.

## Importing

Import every product:

```
php import.php
```

Import a single product folder:

```
php import.php servo-mount
```

The importer prints one line per product and a short summary. If a product
fails, it says which product, what went wrong, and how to fix it, and then
continues with the rest.

## Project layout

```
import.php            Command line entry point
config.example.php    Copy to config.php and fill in
src/                  The pipeline
    Scanner.php           Finds product folders
    ProductParser.php     Reads product.md and validates it
    MarkdownRenderer.php  Turns the story into HTML
    Publisher.php         Runs the import, copies files, reports
    OpenCartApi.php       The only class that touches OpenCart's database
    Product.php           Plain data holder
    ImportException.php   Human-readable import errors
lib/Parsedown.php     Markdown library (single file, MIT)
products/             Your product folders
tests/run.php         Tests for the non-database parts
docs/                 Specification, architecture, roadmap, decisions
```

## Running the tests

```
php tests/run.php
```

These cover the scanner, the parser, and the Markdown renderer. The database
side needs a real OpenCart installation and is not part of this test run.
