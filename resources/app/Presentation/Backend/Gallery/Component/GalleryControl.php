<?php declare(strict_types=1);

namespace App\Presentation\Backend\Gallery\Component;

use App\Model\Gallery\GalleryImages;
use Drago\Application\UI\Alert;
use Drago\Application\UI\ExtraControl;
use Drago\Component\Component;
use Drago\Component\ModalHandle;
use Drago\Component\OffcanvasHandle;
use Nette\Application\Attributes\Requires;
use Nette\Application\UI\Form;
use Nette\Http\FileUpload;
use Tracy\Debugger;
use Tracy\ILogger;


/**
 * Images of the gallery: upload, a full-size preview in the offcanvas, deleting after a confirmation in the modal
 * and copying the Texy markup of an image (assets/naja/gallery.js) for the content of articles and pages.
 * @property-read GalleryTemplate $template
 */
class GalleryControl extends ExtraControl implements OffcanvasHandle, ModalHandle
{
	use Component;

	/** Largest uploaded image (8 MB); it is made smaller by GalleryImages. */
	private const int MaxImageSize = 8 * 1024 * 1024;

	/** Most images uploaded at once. */
	private const int MaxImages = 20;

	private ?string $preview = null;
	private ?string $deleteName = null;
	private string $snippetMessage = 'message';


	public function __construct(
		private readonly Factory $factory,
		private readonly GalleryImages $galleryImages,
	) {
	}


	public function render(): void
	{
		$template = $this->template;
		$template->setTranslator($this->translator);
		$template->offcanvasId = $this->getUniqueIdComponent(self::Offcanvas);
		$template->modalId = $this->getUniqueIdComponent(self::Modal);
		$template->path = $this->getPresenter()->getHttpRequest()->getUrl()->getBasePath() . GalleryImages::Path;
		$template->images = $this->galleryImages->getNames();
		$template->uploadSupported = GalleryImages::isSupported();
		$template->preview = $this->preview;
		$template->deleteName = $this->deleteName;
		$template->setFile(__DIR__ . '/Gallery.latte');
		$template->render();
	}


	#[Requires(ajax: true)]
	public function handleOpenModal(): void
	{
		$this->modalComponent(self::Modal);
		$this->redrawControl('content');
	}


	#[Requires(ajax: true)]
	public function handleOpenOffcanvas(): void
	{
		$this->offCanvasComponent(self::Offcanvas);
	}


	/** Shows the image in its full size in the offcanvas. */
	#[Requires(ajax: true)]
	public function handlePreview(string $name): void
	{
		if (!$this->galleryImages->exists($name)) {
			$this->error();
		}

		$this->preview = $name;
		$this->handleOpenOffcanvas();
	}


	/** Opens the confirmation dialog; its form posts the name to delete(). */
	#[Requires(ajax: true)]
	public function handleDelete(string $name): void
	{
		if (!$this->galleryImages->exists($name)) {
			$this->error();
		}

		$this->deleteName = $name;
		$this['delete']->setDefaults(['name' => $name]);
		$this->handleOpenModal();
	}


	protected function createComponentUpload(): Form
	{
		$form = $this->factory->create();

		// the Image rule and the resizing need GD (only suggested); without it the template shows a warning instead
		if (GalleryImages::isSupported()) {
			$form->addMultiUpload('images', 'Upload images')
				->setRequired('Please select the images.')
				->addRule($form::MaxLength, 'At most %d images can be uploaded at once.', self::MaxImages)
				->addRule($form::Image, 'The image must be JPEG, PNG, GIF or WebP.')
				->addRule($form::MaxFileSize, 'The image can have at most 8 MB.', self::MaxImageSize)
				->addRule(
					GalleryImages::validatePixels(...),
					'The image can have at most %d megapixels.',
					GalleryImages::MaxPixels / 1_000_000,
				);
		}

		$form->addSubmit('upload', 'Upload');
		$form->onSuccess[] = $this->upload(...);
		$form->onError[] = fn() => $this->redrawControl('upload');
		return $form;
	}


	protected function createComponentDelete(): Form
	{
		$form = $this->factory->create();
		$form->addHidden('name')
			->setRequired();

		$form->addSubmit('confirm', 'Confirm');
		$form->onSuccess[] = $this->delete(...);

		// an invalid name (e.g. a tampered hidden field) cannot be fixed in the dialog: close it with a warning
		$form->onError[] = $this->deleteFailed(...);
		return $form;
	}


	/** @param array{images: list<FileUpload>} $values */
	private function upload(Form $form, array $values): void
	{
		try {
			foreach ($values['images'] as $upload) {
				$this->galleryImages->save($upload);
			}

			$this->addFlashMessage('The images were uploaded.', Alert::Success);
			$form->reset();

		} catch (\Throwable $e) {
			Debugger::log($e, ILogger::EXCEPTION);
			$this->addFlashMessage('Unknown status code.', Alert::Warning);
		}

		// the images saved before a failure are shown too
		$this->addRedraw($this->snippetMessage);
		$this->redrawControl('upload');
		$this->redrawControl('images');
	}


	/** @param array{name: string} $values */
	private function delete(Form $form, array $values): void
	{
		try {
			if (!$this->galleryImages->delete($values['name'])) {
				$this->deleteFailed();
				return;
			}

			$this->addFlashMessage('The image was deleted.', Alert::Success);
			$this->redrawControl('images');

		} catch (\Throwable $e) {
			// e.g. a locked file or missing permissions
			Debugger::log($e, ILogger::EXCEPTION);
			$this->addFlashMessage('The image could not be deleted.', Alert::Warning);
		}

		$this->addRedraw($this->snippetMessage);
		$this->closeComponent();
	}


	private function deleteFailed(): void
	{
		$this->addFlashMessage('The image could not be deleted.', Alert::Warning);
		$this->addRedraw($this->snippetMessage);
		$this->closeComponent();
	}
}
