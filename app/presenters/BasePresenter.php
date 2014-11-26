<?php

namespace App;

use Nette,
	Model;


/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{
	/**
	 * @var Model\Sessions
	 * @inject
	 */
	public $sessions;
	
	public function beforeRender() {
		$this->template->domain = $this->context->parameters['domain'];
	}
}
