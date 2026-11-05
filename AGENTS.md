# Agent Guide

`drago-ex/project-gallery` (type drago-tools-resource, PHP >=8.3 <9, requires ext-gd and ext-intl): an image
gallery in the administration for images in the content of articles (project-blog) and static pages, inserted as
Texy markup. The workspace rules are in `../AGENTS.md`; dependencies are in `composer.json`.

## Layout
- `resources/app/Model/Gallery/GalleryImages.php`: the images on disk.
- `resources/app/Presentation/Backend/Gallery`: `GalleryPresenter`, `Component/GalleryControl`, `conf.neon`,
  `7.menu.neon`, `acl.neon`, `Translate/cs.neon`.
- `resources/assets/naja/gallery.js` + `gallery.scss`: `GalleryCopy` and the button styles, copied to `assets/naja/`.
- `tests/`: Nette Tester without a database.

## How it works
- Files only, no table and no migration. `GalleryImages` lists `www/upload/gallery/` (jpg, jpeg, png, gif, webp;
  newest first), saves an upload resized to fit 1920 × 1920 (format kept, name by `Strings::webalize()`,
  `-2`, `-3`… when taken) and deletes only a name `getNames()` lists, so a path in the name never reaches another
  file. Registered in `conf.neon` with `%appDir%/../www`. Resaving through GD drops GIF animation.
- The cover image of an article is not part of the gallery: project-blog has its own `ArticleImages`.
- `TexyFormatter` of drago-ex/texy-toolbar filters only URLs with a scheme, so the relative
  `[* /upload/gallery/x.jpg *]` passes.
- `GalleryControl` (one control, no datagrid, no `BaseControl`): the upload form (`addMultiUpload`, at most 20
  images, 8 MB, `GalleryImages::MaxPixels`, AJAX), the grid (snippet `images`), `preview!` in the full-screen
  offcanvas, `delete!` opens the confirmation modal whose form posts the name. The buttons over an image show on
  hover or focus, always on touch screens.
- `GalleryCopy` has one click listener on the document (snippets need no re-init) and copies `data-gallery-copy`
  or `[* path *]` by the checked `gallery-copy-mode` radio (stored in localStorage in try/catch), with a
  `prompt()` fallback outside HTTPS/localhost.
- Flash messages have gallery-specific texts so they do not clash with the keys of other packages.

## Permissions
`GalleryControl` uses `AccessCheck`. Privileges of `Backend:Gallery`: `default`; `upload` (the `upload` form,
snippet `upload` rendered only with `isAllowed('upload')`); `delete` (`handleDelete` and the `delete` form).
`openModal`, `openOffcanvas`, `preview` need only the page (`#[Access]`). project-permission reads the privileges
from the code.

## Commands
```bash
composer phpstan
composer tester     # GalleryImages in a temp directory; the upload test needs GD
```
