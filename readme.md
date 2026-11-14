# Drago Project Gallery

Image gallery in the administration of Drago Project, for images in the content of articles and pages.

Images are uploaded on one page and shown in a grid. Over an image appear three buttons: a full-size preview,
copying it to paste into the content, and deleting it. A switch above the images sets what is copied: the
[Texy](https://texy.info/en/syntax) markup (`[* /upload/gallery/photo.jpg *]`) or only the path
(`/upload/gallery/photo.jpg`); the browser remembers the choice.

[![PHP version](https://badge.fury.io/ph/drago-ex%2Fproject-gallery.svg)](https://badge.fury.io/ph/drago-ex%2Fproject-gallery)
[![Coding Style](https://github.com/drago-ex/project-gallery/actions/workflows/coding-style.yml/badge.svg)](https://github.com/drago-ex/project-gallery/actions/workflows/coding-style.yml)

## Requirements

- PHP >= 8.3, < 9
- PHP extensions GD and intl
- [Drago Project](https://github.com/drago-ex/project)

## Installation

```bash
composer require drago-ex/project-gallery
```

[Project Tools](https://github.com/drago-ex/project-tools) copies the files of the package into the project,
where you adapt them. The gallery needs no database.

## Assets

The page uses three modules of [compostrap](https://github.com/compostrap/compostrap): the tiles of the images
(`gallery`), their preview (`lightbox`) and the area to drop images on (`dropzone`).

Register the Naja extension that copies the path or the markup of an image and opens the preview, and bind the
dropzone, in `assets/admin.js`; the dropzone is bound again after a redraw of a snippet (once in a project, also
for other packages with a dropzone):

```js
import GalleryCopy from './naja/gallery.js';
import { DropzoneInput } from 'compostrap/dropzone';

DropzoneInput.init();
naja.snippetHandler.addEventListener('afterUpdate', (event) => DropzoneInput.init(event.detail.snippet));

registerNajaExtensions(
	// ...
	GalleryCopy
);
```

and the styles in `assets/admin.scss`:

```scss
@import 'naja/gallery';
@import 'compostrap/src/scss/dropzone';
```

The browser copies to the clipboard only over HTTPS and on localhost; elsewhere the text is offered in a dialog.

## Administration

```latte
{link :Backend:Gallery:}
```

The menu item comes from `Backend/Gallery/7.menu.neon` with
[Backend UI](https://github.com/drago-ex/project-backend-ui).

Images are saved in `www/upload/gallery/`. An upload is resized to fit 1920 × 1920 px and gets a readable name
made from the original one (`Můj obrázek.JPG` → `muj-obrazek.jpg`), numbered when the name is taken. Up to 20
images of at most 8 MB can be uploaded at once. Add `/www/upload/` to `.gitignore`.

## Permissions

The rules are in `Backend/Gallery/acl.neon` (resource `Backend:Gallery`, the administrator only). The privileges
are `default` (the page with previews), `upload` and `delete`; the upload form and the delete buttons are shown
only to those who may use them. [Project Permission](https://github.com/drago-ex/project-permission) gives them to
other roles, e.g. a role that only views and copies images with `default`.

The same file describes the privileges (`permissionLabels`) for the permissions page of [Project Permission](https://github.com/drago-ex/project-permission).
