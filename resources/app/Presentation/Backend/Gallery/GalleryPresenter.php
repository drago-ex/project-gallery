<?php declare(strict_types=1);

namespace App\Presentation\Backend\Gallery;

use App\Presentation\Backend\BackendPresenter;
use App\Presentation\Backend\Gallery\Component\GalleryControl;
use Throwable;


class GalleryPresenter extends BackendPresenter
{
	public function __construct(
		private readonly GalleryControl $galleryControl,
	) {
		parent::__construct();
	}


	/** @throws Throwable */
	protected function createComponentGallery(): GalleryControl
	{
		$control = $this->galleryControl;
		$control->translator = $this->getTranslator();
		return $control;
	}
}
