# Drago Project Gallery

Galerie obrázků v administraci Drago Project pro obrázky v obsahu článků a stránek.

Balíček přidává stránku administrace, kde se obrázky nahrávají a zobrazují v mřížce. Nad obrázkem se objeví tři
tlačítka: náhled v plné velikosti, zkopírování pro vložení do obsahu článku nebo stránky a smazání. Přepínač
nad obrázky určuje, co se zkopíruje: zápis v [Texy](https://texy.info/cs/syntax) (`[* /upload/gallery/photo.jpg *]`)
nebo jen cesta (`/upload/gallery/photo.jpg`); prohlížeč si volbu pamatuje.

[![PHP version](https://badge.fury.io/ph/drago-ex%2Fproject-gallery.svg)](https://badge.fury.io/ph/drago-ex%2Fproject-gallery)
[![Coding Style](https://github.com/drago-ex/project-gallery/actions/workflows/coding-style.yml/badge.svg)](https://github.com/drago-ex/project-gallery/actions/workflows/coding-style.yml)

## Požadavky

- PHP >= 8.3, < 9
- [Drago Project](https://github.com/drago-ex/project)

## Instalace

```bash
composer require drago-ex/project-gallery
```

## Soubory kopírované do projektu

Soubory do projektu kopíruje [drago-ex/project-tools](https://github.com/drago-ex/project-tools)
podle nastavení v `extra.drago-tools` v `composer.json` tohoto balíčku.

## Assety

Zaregistrujte rozšíření Naja, které kopíruje cestu nebo zápis obrázku, a vložte styly tlačítek do `assets/admin.js`:

```js
import GalleryCopy from './naja/gallery.js';
import './naja/gallery.scss';

registerNajaExtensions(
	// ...
	GalleryCopy
);
```

Prohlížeč povoluje kopírování do schránky jen přes HTTPS a na localhostu; jinde se text nabídne v dialogu
ke zkopírování ručně.

## Administrace

Galerie je dostupná jako:

```latte
{link :Backend:Gallery:}
```

S [drago-ex/project-backend-ui](https://github.com/drago-ex/project-backend-ui) přidejte odkaz do jeho menu (viz jeho README).

Obrázky se ukládají do `www/upload/gallery/`. Nahraný obrázek se zmenší, aby se vešel do 1920 × 1920 px, a dostane
čitelný název odvozený z původního (`Můj obrázek.JPG` → `muj-obrazek.jpg`), s číslem, když je název obsazený. Najednou
lze nahrát až 20 obrázků po nejvýše 8 MB. Zmenšování potřebuje rozšíření PHP GD (Docker image
drago-ex/project-docker ho má); bez něj galerie nahrávání nenabízí.

Modul používá zdroj oprávnění `Backend:Gallery`. Backend provider povoluje roli administrátora.

---

## Volitelné doplňky

Balíček funguje i bez nich. Nastavte je ručně podle popisu níže, když je projekt potřebuje.

### Oprávnění

Galerie funguje bez oprávnění i bez [drago-ex/permission](https://github.com/drago-ex/permission):
administrace zobrazí všechna svá tlačítka.

Pro statická oprávnění jsou pravidla přístupu připravená v [`bin/create-gallery-permission`](bin/create-gallery-permission);
drago-ex/permission z nich vygeneruje provider (nastavení podle jeho README):

```bash
php vendor/bin/drago-commands
```

Aby formulář pro nahrávání a tlačítka pro mazání viděly jen role s privilegiem, přidejte trait `WriteAccess`
z drago-ex/permission; jeho `isWriteAllowed()` se ptá na privilegium `gallery-write`. V
`app/Presentation/Backend/Gallery/Component/GalleryControl.php` trait použijte a výsledek předejte šabloně
(s `public bool $writeAllowed = false;` v `GalleryTemplate`):

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

potom formulář a tlačítko pro smazání obrázku zobrazte jen s povolením:

```latte
{if $writeAllowed && $uploadSupported}
```

```latte
<a n:if="$writeAllowed" n:href="delete!, name: $name" …>
```

Každý signál komponenty se kontroluje jako `gallery-write`, i náhled. Aby náhled otevřela i role, která galerii smí
jen prohlížet, označte ho v `GalleryPresenter` jako jen pro čtení (kontroluje se pak `gallery-read`):

```php
/** @return list<string> */
protected function readOnlySignals(): array
{
	return ['preview'];
}
```

Pro dynamická oprávnění nastavovaná v administraci nainstalujte
[drago-ex/project-permission](https://github.com/drago-ex/project-permission) a ručně naimportujte oprávnění galerie
do jeho tabulky `resources`, aby je bylo možné přidělit rolím:

```bash
php vendor/bin/migration db:migrate vendor/drago-ex/project-gallery/migrations-permission
```
