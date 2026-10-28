<?php

declare(strict_types=1);

use App\Model\Gallery\GalleryImages;
use Nette\Utils\FileSystem;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';


$wwwDir = sys_get_temp_dir() . '/drago-gallery-' . getmypid();
$dir = $wwwDir . '/' . GalleryImages::Path;
FileSystem::delete($wwwDir);
register_shutdown_function(fn() => FileSystem::delete($wwwDir));


test('no directory means no images', function () use ($wwwDir) {
	$images = new GalleryImages($wwwDir);
	Assert::same([], $images->getNames());
	Assert::false($images->exists('a.png'));
	Assert::false($images->delete('a.png'));
});


test('the newest image is listed first, other files are left out', function () use ($wwwDir, $dir) {
	FileSystem::createDir($dir);
	FileSystem::write("$dir/old.png", '');
	FileSystem::write("$dir/new.jpg", '');
	FileSystem::write("$dir/notes.txt", '');
	touch("$dir/old.png", time() - 60);

	$images = new GalleryImages($wwwDir);
	Assert::same(['new.jpg', 'old.png'], $images->getNames());
	Assert::true($images->exists('new.jpg'));
	Assert::false($images->exists('notes.txt'));
	FileSystem::delete($dir);
});


test('only an image of the gallery can be deleted', function () use ($wwwDir, $dir) {
	FileSystem::createDir($dir);
	FileSystem::write("$dir/photo.png", '');
	FileSystem::write("$wwwDir/index.php", '');

	$images = new GalleryImages($wwwDir);
	Assert::false($images->delete('../../index.php'));
	Assert::false($images->delete('missing.png'));
	Assert::true(is_file("$wwwDir/index.php"));

	Assert::true($images->delete('photo.png'));
	Assert::false(is_file("$dir/photo.png"));
	FileSystem::delete($dir);
});

