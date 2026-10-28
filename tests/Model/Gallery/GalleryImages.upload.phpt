<?php

declare(strict_types=1);

use App\Model\Gallery\GalleryImages;
use Nette\Http\FileUpload;
use Nette\Utils\FileSystem;
use Nette\Utils\Image;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

if (!GalleryImages::isSupported()) {
	Tester\Environment::skip('GD is not loaded.');
}


$wwwDir = sys_get_temp_dir() . '/drago-gallery-' . getmypid();
$dir = $wwwDir . '/' . GalleryImages::Path;
FileSystem::delete($wwwDir);
register_shutdown_function(fn() => FileSystem::delete($wwwDir));


function upload(string $name, int $width = 10, int $height = 10): FileUpload
{
	$file = tempnam(sys_get_temp_dir(), 'gallery');
	Image::fromBlank($width, $height)->save($file, type: Image::PNG);
	return new FileUpload(['name' => $name, 'size' => filesize($file), 'tmp_name' => $file, 'error' => UPLOAD_ERR_OK]);
}


test('an upload gets a readable name, numbered when taken, and is made smaller', function () use ($wwwDir, $dir) {
	$images = new GalleryImages($wwwDir);
	$expected = extension_loaded('intl') ? 'muj-obrazek.png' : 'm-j-obr-zek.png';
	Assert::same($expected, $images->save(upload('Můj obrázek.PNG')));
	Assert::same(str_replace('.png', '-2.png', $expected), $images->save(upload('Můj obrázek.PNG')));
	Assert::same('image.png', $images->save(upload('.png')));

	$name = $images->save(upload('big.png', 3000, 1000));
	Assert::same([1920, 640], array_slice(getimagesize("$dir/$name") ?: [], 0, 2));
	FileSystem::delete($dir);
});
