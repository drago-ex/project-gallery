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
  images, 8 MB, `GalleryImages::MaxPixels`, AJAX), the grid (snippet `images`), `delete!` opens the confirmation
  modal whose form posts the name. The preview is the lightbox of compostrap (links with `data-lightbox`, a script,
  no signal); its translated labels are data attributes of the list.
- The upload is a dropzone of compostrap (`label.dropzone`, the `images` input as `dropzone-input` with
  `data-dropzone-submit`): the file input covers the area and stays the field of the Nette form, and the form is
  sent as soon as files are chosen or dropped, so its submit button is not rendered (the control keeps it). The
  snippet `upload` is redrawn after every upload, so `DropzoneInput.init()` runs again after a snippet update
  (`admin.js`). A validation message shows under the area.
- The images are the tiles of the compostrap module `gallery` (`gallery-item`, `gallery-caption` with the name and
  the extension, `gallery-actions` with `btn-icon` buttons and CSS tooltips); the package has no styles of its own,
  `naja/gallery.scss` only imports `gallery` and `lightbox`. A constant in the template needs a leading backslash
  (`PATHINFO_FILENAME`), Latte warns otherwise.
- `GalleryCopy` has one click listener on the document (snippets need no re-init) and copies `data-gallery-copy`
  or `[* path *]` by the checked `gallery-copy-mode` radio (stored in localStorage in try/catch), with a
  `prompt()` fallback outside HTTPS/localhost.
- Flash messages have gallery-specific texts so they do not clash with the keys of other packages.

## Permissions
`GalleryControl` uses `AccessCheck`. Privileges of `Backend:Gallery`: `default`; `upload` (the `upload` form,
snippet `upload` rendered only with `isAllowed('upload')`); `delete` (`handleDelete` and the `delete` form).
`openModal` needs only the page (`#[Access]`). project-permission reads the privileges
from the code.

## Commands
```bash
composer phpstan
composer tester     # GalleryImages in a temp directory; the upload test needs GD
```
