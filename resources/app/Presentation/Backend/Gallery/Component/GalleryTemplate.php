<?php declare(strict_types=1);

namespace App\Presentation\Backend\Gallery\Component;

use Drago\Application\UI\ExtraTemplate;


class GalleryTemplate extends ExtraTemplate
{
	public string $offcanvasId;
	public string $modalId;

	/** Web path of the images, with the base path. */
	public string $path;

	/** @var list<string> */
	public array $images = [];
	public bool $uploadSupported;
	public ?string $preview = null;
	public ?string $deleteName = null;
}
