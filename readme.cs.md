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
- Rozšíření PHP GD (zmenšování obrázků) a intl (čitelné názvy souborů); Docker image z
  [drago-ex/project-docker](https://github.com/drago-ex/project-docker) má obě
- [Drago Project](https://github.com/drago-ex/project)

## Instalace

```bash
composer require drago-ex/project-gallery
```

## Soubory kopírované do projektu

Soubory do projektu kopíruje [drago-ex/project-tools](https://github.com/drago-ex/project-tools)
podle nastavení v `extra.drago-tools` v `composer.json` tohoto balíčku.

## Assety

Zaregistrujte rozšíření Naja, které kopíruje cestu nebo zápis obrázku, v `assets/admin.js`:

```js
import GalleryCopy from './naja/gallery.js';

registerNajaExtensions(
	// ...
	GalleryCopy
);
```

a vložte styly tlačítek do `assets/admin.scss`:

```scss
@import 'naja/gallery';
```

Prohlížeč povoluje kopírování do schránky jen přes HTTPS a na localhostu; jinde se text nabídne v dialogu
ke zkopírování ručně.

## Administrace

Galerie je dostupná jako:

```latte
{link :Backend:Gallery:}
```

S [drago-ex/project-backend-ui](https://github.com/drago-ex/project-backend-ui) dostane stránka položku menu z `Backend/Gallery/7.menu.neon`.

Obrázky se ukládají do `www/upload/gallery/`. Nahraný obrázek se zmenší, aby se vešel do 1920 × 1920 px, a dostane
čitelný název odvozený z původního (`Můj obrázek.JPG` → `muj-obrazek.jpg`), s číslem, když je název obsazený. Najednou
lze nahrát až 20 obrázků po nejvýše 8 MB.

Modul používá zdroj oprávnění `Backend:Gallery`.

## Pravidla přístupu

Pravidla přístupu jsou v `app/Presentation/Backend/Gallery/acl.neon`: zdroj `Backend:Gallery` jen pro administrátora.
[drago-ex/permission](https://github.com/drago-ex/permission) je sloučí se svými výchozími rolemi, ve kterých administrátor smí všechno.

Privilegia administrace (`Backend:Gallery`) jsou `default` (stránka i s náhledem obrázků), `upload` a `delete`.
Formulář pro nahrání a tlačítka pro mazání se zobrazí jen těm, kdo je smí použít. S
[drago-ex/project-permission](https://github.com/drago-ex/project-permission) lze tato privilegia přidělit rolím
v jeho administraci, např. roli, která obrázky jen prohlíží a kopíruje, jen `default`.
