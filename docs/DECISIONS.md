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