# Architectural Decisions

## 2026-07-25

### Decision 001

Use exactly one Markdown file per product.

Reason

The content editor is not a programmer.

Advantages

- Easier editing
- Easier version control
- Easier import
- Easier documentation

Rejected Alternatives

- Multiple Markdown files
- Word documents
- HTML editing

### Decision 002

Render the story as tabs, one tab per top-level "# " heading.

Reason

The story has clear sections (Problem, Dream, ...). Tabs let the customer
move between them instead of scrolling one long page.

How

The tab markup is self-contained Bootstrap 5 written into the product
description. OpenCart 4 already loads Bootstrap 5, so no theme or template
is modified. This keeps Markdown the only thing the editor touches.

Rejected Alternatives

- Editing the OpenCart product-page template to add native tabs
  (breaks the "never edit templates" rule)
- A separate extension module with its own controller and view
  (too much for what a block of HTML can do)

### Decision 003

Optimize images automatically during import.

Reason

The author drops full-size phone or camera photos into images/. Serving
them untouched makes the product page slow to load.

How

When copying an image into OpenCart, images wider than MAX_IMAGE_WIDTH are
scaled down and re-encoded (ImageOptimizer). The source files in products/
are never changed. Story images also get the Bootstrap "img-fluid" class so
they never overflow the page.

Rejected Alternatives

- Asking the author to resize images by hand
  (the author should only write and drop files)
- A separate build step or external tool
  (GD is already available and does the job)

### Decision 004

Add an optional "summary" field for the listing text.

Reason

OpenCart shows a tag-stripped preview of the description in category and
search listings. With a tabbed description that preview becomes the tab
labels run together ("SorunHayalIlkPrototip..."), which is meaningless.

How

The author writes a one- or two-sentence "summary" in the front matter. It
is placed as a plain paragraph at the very top of the description, so it
reads as a short intro on the product page and is what the listing preview
shows.

Rejected Alternatives

- Editing the listing template to use meta_description instead
  (breaks the "never edit templates" rule)
- Auto-generating the summary from the first paragraph
  (would duplicate that paragraph on the page; an explicit field is clearer)

### Decision 005

Create a category automatically when it does not exist.

Reason

Version 0.1 only linked to existing categories, so the author had to open
OpenCart and add a category by hand before importing. That breaks the
"only touch Markdown" workflow.

How

When a product names a category that is not found, the importer creates it
as a top-level category (findCategoryIdByName / createCategory) and prints a
short note. A misspelled category therefore creates a new one, so the note
makes that visible.

Rejected Alternatives

- Keeping the "category must already exist" rule
  (forces the author out of Markdown and into the admin)
- Guessing/fuzzy-matching near category names
  (surprising; exact names are predictable)

### Decision 006

Turn neighbouring images into a gallery automatically.

Reason

Several images in a row looked better side by side than stacked full-width,
and the author should not have to write any special markup.

How

When two or more images sit next to each other (consecutive lines, or single
image paragraphs in a row), MarkdownRenderer wraps them in a Bootstrap grid
(story-gallery). A single image is left full-width. Images split by text stay
separate.

Rejected Alternatives

- A custom gallery syntax or shortcode
  (the author should only write plain Markdown)
- A lightbox / click-to-zoom
  (extra JavaScript; can be added later if needed)

### Decision 007

Report all problems at once, and separate warnings from errors.

Reason

The parser used to stop at the first bad field, so the author fixed one
thing, re-ran, and found the next. Warnings were also printed straight to
the console, mixed in with the results.

How

ProductParser collects every metadata problem and reports them together,
pointing at the product.md file. Non-fatal issues (unknown fields, a
created category) are carried on the product/report as warnings and printed
as indented notes under each product, so errors and notes never blur
together.

Rejected Alternatives

- Stopping at the first error (slow round-trips for the author)
- A logging framework (too much for a small importer)