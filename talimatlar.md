# AI Instructions

This document defines the design philosophy, scope and development rules for this project.

If there is any conflict between this document and your own assumptions, follow this document.

---

# Project

Project name:

Story Publisher

This project is an OpenCart 4 extension.

Its purpose is to publish products from Markdown files.

This is NOT a generic OpenCart extension.

This is NOT a CMS.

This is NOT a blog engine.

The purpose is to create a very simple publishing workflow for engineering and 3D printing projects.

---

# Main Idea

Every product is a story.

Customers should understand

- the original problem
- the design process
- the iterations
- the final solution
- why the design exists

The story is the primary content.

The sale is secondary.

---

# Target User

The primary content editor is NOT a programmer.

The editor should never need to

- edit HTML
- edit PHP
- edit SQL
- edit CSS
- edit OpenCart templates

The only thing the editor should modify is

product.md

and optionally add images or downloadable files.

---

# Philosophy

The workflow should feel like writing an article.

Not like entering records into an ERP system.

The author writes.

The software publishes.

---

# Source of Truth

Markdown is always the source of truth.

Never edit generated HTML.

Never ask the user to edit HTML.

Never require manual database modifications.

Everything should come from Markdown.

---

# Product Structure

Each product lives inside its own directory.

Example

products/

    servo-mount/

        product.md

        images/

        downloads/

Nothing outside this directory should be necessary to describe the product.

---

# product.md

Every product contains exactly one Markdown document.

It contains

- metadata
- story
- images
- technical information

The goal is to edit only one file.

Never split the story into multiple Markdown files unless explicitly requested.

---

# Images

Images belong inside

images/

Markdown references images normally.

Example

![](prototype.jpg)

The importer automatically resolves image locations.

The user should never write OpenCart paths.

---

# Downloads

Downloads belong inside

downloads/

Examples

STL

STEP

PDF

ZIP

The importer automatically publishes them.

---

# OpenCart Responsibilities

OpenCart is responsible only for

- customers
- checkout
- orders
- payment
- downloads
- inventory

Everything related to content comes from Markdown.

---

# Keep It Simple

Always choose the simplest possible implementation.

Avoid

- unnecessary abstraction
- complicated architecture
- unnecessary interfaces
- dependency injection unless needed
- factory patterns unless necessary
- premature optimization

Readable code is preferred over clever code.

---

# Do Not Over Engineer

Future ideas are NOT current requirements.

Do not implement speculative features.

Do not create extension points for features that do not yet exist.

Implement only today's requirements.

---

# Folder Layout

The project should remain easy to understand.

Someone unfamiliar with OpenCart should still understand the project after reading the directory tree.

---

# Development Rules

Small commits.

Small pull requests.

Working software after every milestone.

Avoid large rewrites.

Refactor only when necessary.

---

# Error Handling

All import errors should produce human-readable messages.

Never expose raw PHP errors to end users.

Always explain

- which product failed
- why
- how to fix it

---

# Performance

Correctness is more important than speed.

The number of products is expected to remain relatively small.

Readability is preferred over micro-optimizations.

---

# External Libraries

Only introduce external libraries when they solve a significant problem.

Do not introduce dependencies merely to reduce a few lines of code.

Every dependency should have a clear justification.

---

# Documentation

Whenever the architecture changes,

update the documentation first.

Documentation is part of the project.

Outdated documentation is considered a bug.

---

# AI Behaviour

When modifying the code,

first understand the current architecture.

Preserve the project's simplicity.

Never redesign the project without explicit instructions.

If there are multiple valid solutions,

choose the one that a beginner PHP developer can understand.

---

# Long-Term Goal

The final workflow should be:

Write a story in Obsidian

↓

Save product.md

↓

Copy images

↓

Copy STL files

↓

Press Import

↓

OpenCart is updated automatically

Nothing more.

This is the guiding principle of the project.

Whenever you are uncertain,

choose the solution that moves the project closer to this workflow.
