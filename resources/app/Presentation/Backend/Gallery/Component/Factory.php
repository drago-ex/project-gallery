<?php declare(strict_types=1);

namespace App\Presentation\Backend\Gallery\Component;

use Drago\Form\Forms;


/** @extends \Drago\Application\UI\Factory<Forms> */
readonly class Factory extends \Drago\Application\UI\Factory
{
	protected function createForm(): Forms
	{
		return new Forms;
	}
}
