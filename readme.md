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
- [Drago Project](https://github.com/drago-ex/project)

## Installation

```bash
composer require drago-ex/project-gallery
```

## Files copied into the project

The files are copied into the project by [drago-ex/project-tools](https://github.com/drago-ex/project-tools),
as set in `extra.drago-tools` of this package's `composer.json`.

## Assets

Register the Naja extension, which copies the path or the markup of an image, and include the styles of the buttons in
`assets/admin.js`:

```js
import GalleryCopy from './naja/gallery.js';
import './naja/gallery.scss';

registerNajaExtensions(
	// ...
	GalleryCopy
);
```

The browser allows copying to the clipboard only over HTTPS and on localhost; elsewhere the text is offered
in a dialog to copy by hand.

## Backend

The gallery is available as:

```latte
{link :Backend:Gallery:}
```

With [drago-ex/project-backend-ui](https://github.com/drago-ex/project-backend-ui), add the link to its menu (see its README).

Images are saved in `www/upload/gallery/`. An upload is made smaller to fit 1920 × 1920 px and gets a readable name
made from its original one (`Můj obrázek.JPG` → `muj-obrazek.jpg`), with a number when the name is taken. Up to 20
images of at most 8 MB can be uploaded at once. Resizing needs the PHP extension GD (the Docker image of
drago-ex/project-docker has it); without it the gallery offers no upload.

The module uses the permission resource `Backend:Gallery`. The backend provider allows the administrator role.

---

## Optional add-ons

The package works without them. Set them up by hand as described below when the project needs them.

### Permissions

The gallery works without permissions and without [drago-ex/permission](https://github.com/drago-ex/permission):
the administration shows all its buttons.

For static permissions, the access rules are prepared in [`bin/create-gallery-permission`](bin/create-gallery-permission);
drago-ex/permission generates a provider from it (set up as its README shows):

```bash
php vendor/bin/drago-commands
```

To show the upload form and the delete buttons only to roles with the privilege, add the `WriteAccess` trait of
drago-ex/permission; its `isWriteAllowed()` asks for the `gallery-write` privilege. In
`app/Presentation/Backend/Gallery/Component/GalleryControl.php` use the trait and pass the result to the template
(with `public bool $writeAllowed = false;` in `GalleryTemplate`):

```php
use Drago\Permission\WriteAccess;

class GalleryControl extends ExtraControl implements OffcanvasHandle, ModalHandle
{
	use Component;
	use WriteAccess;

	public function render(): void
	{
		// ...
		$template->writeAllowed = $this->isWriteAllowed();
```

then show the form and the delete button of an image only when allowed:

```latte
{if $writeAllowed && $uploadSupported}
```

```latte
<a n:if="$writeAllowed" n:href="delete!, name: $name" …>
```

Every signal of a component is checked as `gallery-write`, the preview too. To let a role that may only view the
gallery open the preview, mark it read-only in `GalleryPresenter` (checked as `gallery-read`):

```php
/** @return list<string> */
protected function readOnlySignals(): array
{
	return ['preview'];
}
```

For dynamic permissions set in the administration, install
[drago-ex/project-permission](https://github.com/drago-ex/project-permission) and import the privileges of the gallery
into its `resources` table by hand, so roles can be given them:

```bash
php vendor/bin/migration db:migrate vendor/drago-ex/project-gallery/migrations-permission
```
