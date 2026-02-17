# AGENTS.md - Tasty Recipes Lite

This file gives coding agents persistent context for working in `tasty-recipes-lite` (Lite).

## Plugin Identity

- Repository: `wp-content/plugins/tasty-recipes-lite`
- Product: Tasty Recipes Lite
- Main bootstrap file: `tasty-recipes.php`
- Main core class: `inc/class-tasty-recipes.php`
- Primary namespace/prefixes:
  - PHP classes: `Tasty_Recipes\...`
  - Hooks/options: `tasty_recipes_*`
- Main text domain in this repo: `tasty-recipes-lite`

## Relationship To Pro (Important)

Tasty Recipes Pro builds on top of Lite and relies on Lite contracts.

- Lite is the base implementation for many shared systems (shortcodes, templates, content model behavior, metadata/schema, block/editor data plumbing).
- Pro extends or overrides Lite behavior using filters/actions and Pro templates.
- Any Lite change affecting hooks, template vars, block data shape, recipe data/meta keys, taxonomy/query behavior, or output structure must be reviewed for Pro compatibility.
- Lite includes deliberate Pro boundaries (for example, Pro-only class handling and feature gating). Keep those boundaries intact.

Related plugin location:
- `wp-content/plugins/tasty-recipes` (Pro)

## High-Value Architecture Map

- `inc/` - core Lite business logic (shortcodes, frontend, metadata, content model, settings integration, etc.).
- `inc/designs/` - design/template class implementations (free + locked previews for Pro templates).
- `inc/converters/` - migration/import from other recipe plugins.
- `inc/integrations/` - integrations with SEO/page-builder/comment plugins.
- `inc/objects/` - recipe object model.
- `templates/` - shared template parts/admin views.
- `assets/` - JS/SCSS for editor/admin/frontend.
- `assets/dist/` - compiled assets.
- `inc/libs/tasty-framework/` - shared framework library.

## Guardrails For Editing

- Do not break hook names, filter signatures, or expected array/object shapes consumed by Pro.
- Preserve Pro extension points in Lite (including Pro detection/branching patterns).
- Avoid broad regex or parser changes in shortcode/content parsing without regression coverage.
- Be careful with routing/query/print behavior changes in content model logic.
- Do not modify vendored framework code unless the task explicitly targets framework updates.

## Coding Standards

- WordPress coding standards for PHP and WordPress-style hooks.
- PHP compatibility target: project minimum supported version.
- JavaScript: use ES6+ syntax.
- Keep functions focused and readable.
- Add docblocks to PHP functions/methods/hooks, including:
  - `@since x.x`

## Typical Work Areas

- Core rendering and output: `inc/class-shortcodes.php`, `inc/class-frontend.php`, `templates/`.
- Metadata/schema and SEO behavior: `inc/class-distribution-metadata.php`.
- Content/routing/print logic: `inc/class-content-model.php`.
- Block editor and admin UI: `assets/block-editor/`, `assets/settings/`, and matching PHP integration classes in `inc/`.
- Template systems/designs: `inc/designs/`, `assets/scss/`.

## Safety Checklist Before Finishing

- Validate that changed hooks/contracts still support Pro overrides/extensions.
- Run targeted lint/static checks for touched files/languages:
  - `phpcs`
  - `phpstan`
  - `phpcsfixer`
  - `eslint` (eslite)
- For shortcode/content/model changes, run relevant tests or provide manual verification steps.
- Confirm text domain usage matches Lite conventions in new strings.

## Command Notes

- You may run lint/tests as needed.
- For PHP tooling, always use Composer custom commands (for example: `composer phpcs`, `composer phpstan`, `composer phpcsfixer`).
- For JavaScript lint/format tooling, always use PNPM custom commands for eslint and prettier (for example: `pnpm lint-js`, and `pnpm prettier`/equivalent script when present). Do not run `eslint` or `prettier` directly.
- Ask before running build commands (for example, asset production builds).
- Avoid destructive git commands unless explicitly requested.
