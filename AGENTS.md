# Agent Guide

Use this file as package-specific guidance when working in this repository. Keep changes aligned with the existing public API, project conventions, and supported PHP range.

## Package Overview
- **Package**: `drago-ex/project-gallery`
- **Type**: drago-tools-resource
- **Purpose**: Image gallery in the administration of Drago Project, for images in the content of articles and pages.
- **PHP**: >=8.3 <9
- **Runtime dependencies**: nette/application (^3.2), nette/di (^3.2), nette/http (^3.3), nette/utils (^4.0), drago-ex/application (^4.0), drago-ex/component (^3.0), drago-ex/form (^3.0), drago-ex/project-auth (^1.0), drago-ex/project-backend (^1.0), drago-ex/translator (^4.0)
- **License**: MIT

## Repository Layout
- `bin/create-gallery-permission`: generates the basic access rules (permission provider) with `create-permission` of `drago-ex/permission`; listed in `extra.drago-tools.commands` and run by `drago-commands` of drago-ex/project-tools. An existing provider is kept; without drago-ex/permission it does nothing.
- `migrations-permission/`: privileges of the gallery for the `resources` table of drago-ex/project-permission.
- `resources/`: application resources contributed to the consuming Drago project (`app/Model`, `app/Presentation/Backend`, `assets/naja`).
- `.github/`: CI workflows; keep their PHP versions and commands consistent with Composer and the package scripts.
- `composer.json`: package metadata, dependency constraints, autoloading, and available Composer scripts.

## Key Parts
- Purpose (user, 2026-10-28): images for the content of articles (project-blog) and static pages, inserted as Texy markup. The cover image of an article is not part of it: project-blog keeps its own `ArticleImages` (`www/upload/blog/`).
- Files only, no database table and no migration (the user's choice): `Model/Gallery/GalleryImages` lists `www/upload/gallery/` (jpg, jpeg, png, gif, webp; newest first by mtime), saves an upload resized to fit 1920 × 1920 with Nette Image (format kept, readable name by `Strings::webalize()` with ext-intl, a plain ASCII fallback without it, `-2`, `-3`… when taken) and deletes only a name that `getNames()` lists, so a path in the name can never reach another file. It is registered in `Backend/Gallery/conf.neon` with `%appDir%/../www`, like `ArticleImages` of project-blog. GD is only suggested: without it the form has no upload field (`GalleryImages::isSupported()`) and the template shows a warning, because the Image rule calls GD when the form is built. Resaving through GD drops the animation of a GIF.
- `TexyFormatter` of drago-ex/texy-toolbar filters only URLs with a scheme (`https?:`), so the relative `[* /upload/gallery/x.jpg *]` (with the base path) passes. The copy button copies that markup or only the path, as set by the Texy/Path switch in the heading (the user, 2026-10-28; Texy by default, remembered in localStorage `gallery-copy-mode`, read and written in try/catch).
- `Backend/Gallery/Component/GalleryControl` (one control, no datagrid, so no `BaseControl` like static-page): upload form (`addMultiUpload`, at most 20 images, 8 MB, `GalleryImages::MaxPixels`, sent by Naja as AJAX), the grid of images (snippet `images`), `preview!` shows the image in the full-screen offcanvas, `delete!` opens the confirmation modal whose form posts the name. The three buttons over an image appear on hover or focus (`assets/naja/gallery.scss`; always visible on touch screens); `assets/naja/gallery.js` (`GalleryCopy`, one click listener on the document, so snippets need no re-init) copies `data-gallery-copy` (the path) or `[* path *]` to the clipboard by the checked `gallery-copy-mode` radio, sets the stored mode on load and after a snippet update, with a `prompt()` fallback outside HTTPS/localhost. Both are registered by hand in the project's `admin.js` (README "Assets").
- Texts are translated in `Backend/Gallery/Translate/cs.neon` (the package owns its translations); flash keys are gallery-specific ("The image was deleted.") so they do not clash with the same keys of other packages.
- No menu file: project-preset adds the item (as for blog and static pages); the README lists the link.

## Buttons and permissions
- The package has no permission code (no `WriteAccess`, no `$writeAllowed`, no `readOnlySignals()`): all buttons are shown. The README "Permissions" section lists the optional steps: `WriteAccess` in `GalleryControl` with `$writeAllowed` hiding the upload form, its GD warning and the delete buttons, and `readOnlySignals()` returning `preview` in `GalleryPresenter`. `Backend:Gallery` privileges (`migrations-permission/`): `default-read` (the page), `gallery-read` (the preview, once marked read-only) and `gallery-write` (upload, delete; the `Authorization` trait treats every component signal as write unless the presenter marks it read-only). project-preset-permission ships these steps done, made by `php netis/tools/preset-permission.php`, whose replacements match the text of `GalleryControl.php`, `GalleryTemplate.php`, `Gallery.latte` and `GalleryPresenter.php`: run it after changing them, and update its steps when it reports a replacement that no longer matches.

## Dependencies

Composer constraints describe the package contract. Before changing integration behavior, inspect the source and the actual call sites in this package; do not infer runtime usage solely from dependency declarations.
Development dependencies include nette/tester (^2.5), phpstan/phpstan-nette (^2.0), drago-ex/project-tools (^1.0).

## Essential Commands

```bash
composer tester                 # tester tests -s -C
composer phpstan                # phpstan analyse
```

## Tests

Nette Tester without a database: `GalleryImages` in a directory under the system temp (listing, deleting only gallery images); `GalleryImages.upload.phpt` (names, numbering, resizing) needs GD and is skipped without it (locally: `php -d extension=gd vendor/bin/tester tests -s -C -d extension=gd`).

## Installation Hooks

Composer install and update hooks invoke Drago project setup/cleanup commands. Inspect those scripts and their effects before invoking Composer operations that trigger them; they may modify generated project files.

## Conventions & Rules

- Preserve backward compatibility unless the task explicitly permits a breaking change. Check callers and public signatures before changing behavior.
- Follow the existing PHP style: strict types, explicit parameter and return types, tabs for indentation, and single-quoted strings where practical. Match nearby code when a convention is unclear.
- Keep package-specific configuration, resources, and migrations in their established directories. Do not edit generated or consuming-project files without confirming they are part of this repository’s intended output.
- Add or update focused tests for behavioral changes when this package has a runnable test setup. Do not invent test commands; use only the scripts and configuration present here.
- Update README examples and Composer metadata when a public API or supported requirement changes.
