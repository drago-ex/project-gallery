# Drago Project Gallery

Image gallery in the administration of Drago Project, for images in the content of articles and pages.

The package adds a backend page where images are uploaded and shown in a grid. Over an image appear three
buttons: a full-size preview, copying it to paste into the content of an article or a page, and deleting it.
A switch above the images sets what is copied: the [Texy](https://texy.info/en/syntax) markup (`[* /upload/gallery/photo.jpg *]`)
or only the path (`/upload/gallery/photo.jpg`); the browser remembers the choice.

[![PHP version](https://badge.fury.io/ph/drago-ex%2Fproject-gallery.svg)](https://badge.fury.io/ph/drago-ex%2Fproject-gallery)
[![Coding Style](https://github.com/drago-ex/project-gallery/actions/workflows/coding-style.yml/badge.svg)](https://github.com/drago-ex/project-gallery/actions/workflows/coding-style.yml)

## Requirements

- PHP >= 8.3, < 9
- PHP extensions GD (resizing the images) and intl (readable file names); the Docker image of
  [drago-ex/project-docker](https://github.com/drago-ex/project-docker) has both
- [Drago Project](https://github.com/drago-ex/project)

## Installation

```bash
composer require drago-ex/project-gallery
```

## Files copied into the project

The files are copied into the project by [drago-ex/project-tools](https://github.com/drago-ex/project-tools),
as set in `extra.drago-tools` of this package's `composer.json`.

## Assets

Register the Naja extension, which copies the path or the markup of an image, in `assets/admin.js`:

```js
import GalleryCopy from './naja/gallery.js';

registerNajaExtensions(
	// ...
	GalleryCopy
);
```

and include the styles of the buttons in `assets/admin.scss`:

```scss
@import 'naja/gallery';
```

The browser allows copying to the clipboard only over HTTPS and on localhost; elsewhere the text is offered
in a dialog to copy by hand.

## Backend

The gallery is available as:

```latte
{link :Backend:Gallery:}
```

With [drago-ex/project-backend-ui](https://github.com/drago-ex/project-backend-ui), the page gets its menu item from `Backend/Gallery/7.menu.neon`.

Images are saved in `www/upload/gallery/`. An upload is made smaller to fit 1920 × 1920 px and gets a readable name
made from its original one (`Můj obrázek.JPG` → `muj-obrazek.jpg`), with a number when the name is taken. Up to 20
images of at most 8 MB can be uploaded at once.

The module uses the permission resource `Backend:Gallery`.

## Access rules

The access rules are in `app/Presentation/Backend/Gallery/acl.neon`: the resource `Backend:Gallery`, for the administrator only.
[drago-ex/permission](https://github.com/drago-ex/permission) merges them with its base roles, in which the administrator is allowed everything.

The privileges of the administration (`Backend:Gallery`) are `default` (the page, with the preview of images),
`upload` and `delete`. The upload form and the delete buttons are shown only to those who may use them. With
[drago-ex/project-permission](https://github.com/drago-ex/project-permission), roles can be given these privileges
in its administration, e.g. a role that only views and copies the images with `default`.
