# Story Publisher Specification

Version: 0.1

---

# 1. Purpose

Story Publisher is an OpenCart 4 extension for publishing engineering and maker projects.

Unlike a traditional e-commerce system, every product represents a design journey.

The goal is not only to sell digital files but also to explain how and why the design evolved.

The story is the main content.

The shop is only the publishing platform.

---

# 2. Design Philosophy

This project is intentionally simple.

The content creator should only write Markdown and add files to a folder.

Everything else should happen automatically.

The complete workflow should eventually become:

Write Markdown

↓

Copy images

↓

Copy downloadable files

↓

Press Import

↓

Product appears in OpenCart

---

# 3. Project Goals

The project must

- require minimal technical knowledge
- avoid manual HTML editing
- avoid repetitive work
- make publishing enjoyable
- encourage storytelling

---

# 4. Product Structure

Each product is stored in one directory.

Example

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

No other files should be required.

---

# 5. product.md

Each product contains exactly one Markdown document.

It consists of two parts.

1.

Metadata

2.

Markdown body

Example

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

...

# First Prototype

...

# Development

...

# Sharing

...

# Inspiration

...

---

# 6. Metadata

Initially supported fields

name

model

price

category

image

status

Additional fields may be added later.

---

# 7. Story Sections

The recommended section order is

Problem

Dream

First Prototype

Development

Sharing

Inspiration

Authors may omit sections if necessary.

The importer should never require a fixed set of headings.

---

# 8. Images

Images are stored inside

images/

Markdown references them normally.

Example

![](prototype.jpg)

The importer automatically copies the images into OpenCart.

Image paths should never contain OpenCart directories.

---

# 9. Downloads

Downloads are stored inside

downloads/

Initially supported formats

STL

STEP

ZIP

PDF

Later versions may support additional formats.

---

# 10. Import Process

The importer performs the following steps.

1.

Find product folders.

2.

Read product.md.

3.

Validate metadata.

4.

Convert Markdown to HTML.

5.

Copy images.

6.

Copy downloadable files.

7.

Create or update the OpenCart product.

8.

Report success or failure.

---

# 11. Updates

Importing the same product again should update the existing product.

The importer should avoid creating duplicates.

The product model is the preferred unique identifier.

---

# 12. Error Reporting

Errors must always explain

which product failed

what failed

how to fix it

Example

Missing field: price

instead of

Database error.

---

# 13. Performance

Correctness is more important than speed.

The project is expected to manage hundreds of products rather than tens of thousands.

Readable code is preferred over optimization.

---

# 14. Future Features

Possible future improvements

Automatic galleries

Tabbed product pages

Version history

Multi-language support

Automatic image optimization

Static site export

These are not part of Version 0.1.

---

# 15. Version 0.1 Scope

Version 0.1 only includes

Scanner

Product Parser

Markdown Renderer

Product Importer

Image Import

Download Import

Basic OpenCart integration

Nothing else.

The project should remain as small as possible.