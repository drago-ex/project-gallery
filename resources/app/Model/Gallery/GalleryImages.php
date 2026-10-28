<?php

declare(strict_types=1);

namespace App\Model\Gallery;

use Nette\Forms\Control;
use Nette\Http\FileUpload;
use Nette\Utils\FileSystem;
use Nette\Utils\Finder;
use Nette\Utils\Image;
use Nette\Utils\ImageException;
use Nette\Utils\Strings;


/**
 * Images of the gallery, in www/upload/gallery/ (web path upload/gallery/), used in the content of articles
 * and pages. An upload is made smaller to fit MaxSize × MaxSize, keeps its format and gets a readable name
 * made from its original one, with a number when the name is taken.
 */
class GalleryImages
{
	/** Web path of the images, relative to the base path. */
	public const string Path = 'upload/gallery';

	/** Largest upload in pixels; decoding takes about 4 bytes of memory per pixel. */
	public const int MaxPixels = 40_000_000;

	private const int MaxSize = 1920;

	/** Masks of the files listed by getNames(). */
	private const array Masks = ['*.jpg', '*.jpeg', '*.png', '*.gif', '*.webp'];


	public function __construct(
		private readonly string $wwwDir,
	) {
	}


	/**
	 * File names of the images, the newest first.
	 * @return list<string>
	 */
	public function getNames(): array
	{
		$dir = $this->getDir();
		if (!is_dir($dir)) {
			return [];
		}

		$times = [];
		foreach (Finder::findFiles(...self::Masks)->in($dir) as $file) {
			$times[$file->getFilename()] = $file->getMTime();
		}

		arsort($times);
		return array_map(strval(...), array_keys($times));
	}


	public function exists(string $name): bool
	{
		return in_array($name, $this->getNames(), true);
	}


	/**
	 * Saves an uploaded image and returns its file name.
	 * @throws ImageException
	 */
	public function save(FileUpload $upload): string
	{
		$image = $upload->toImage();
		$image->resize(self::MaxSize, self::MaxSize, Image::ShrinkOnly);

		FileSystem::createDir($this->getDir());
		$name = $this->createName($upload->getUntrustedName(), $upload->getSuggestedExtension() ?? 'jpg');
		$image->save($this->getDir() . '/' . $name);
		return $name;
	}


	/**
	 * Deletes an image of the gallery; false when there is no such image, so a name with a path or of another
	 * file can never delete anything outside the gallery.
	 */
	public function delete(string $name): bool
	{
		if (!$this->exists($name)) {
			return false;
		}

		FileSystem::delete($this->getDir() . '/' . $name);
		return true;
	}


	/** GD (only suggested by the package) resizes the uploads; without it no image can be uploaded. */
	public static function isSupported(): bool
	{
		return extension_loaded('gd');
	}


	/**
	 * Form rule: every image has at most MaxPixels, read from its header before it is decoded, so a huge image
	 * gets a form error instead of exhausting the memory.
	 */
	public static function validatePixels(Control $control): bool
	{
		$uploads = $control->getValue();
		foreach (is_array($uploads) ? $uploads : [$uploads] as $upload) {
			if (!$upload instanceof FileUpload || !$upload->isOk()) {
				continue;
			}

			$size = @getimagesize($upload->getTemporaryFile()); // @ - not an image is reported by the Image rule
			if ($size !== false && $size[0] * $size[1] > self::MaxPixels) {
				return false;
			}
		}

		return true;
	}


	/** Readable name from the original one (e.g. "Můj obrázek.JPG" → "muj-obrazek.jpg"), numbered when taken. */
	private function createName(string $untrustedName, string $extension): string
	{
		$base = pathinfo($untrustedName, PATHINFO_FILENAME);
		$base = extension_loaded('intl')
			? Strings::webalize($base)
			: trim((string) preg_replace('~[^a-z0-9]+~', '-', strtolower($base)), '-');

		$base = rtrim(substr($base, 0, 80), '-') ?: 'image';
		$name = $base . '.' . $extension;
		for ($i = 2; is_file($this->getDir() . '/' . $name); $i++) {
			$name = $base . '-' . $i . '.' . $extension;
		}

		return $name;
	}


	private function getDir(): string
	{
		return $this->wwwDir . '/' . self::Path;
	}
}
