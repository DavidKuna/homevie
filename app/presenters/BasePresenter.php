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
		$this->template->youtubePlayUrl = 'https://www.youtube.com/watch?v=';
		$this->template->randomVideoHashes = [
			'9MWbm9bQz5Y',
			'ZwzY1o_hB5Y',
			'NFfTHoJ9khs',
			'AIO2MEJCD9k',
			'q4AQDDKglEE',
			'OUi42PsV2hQ'
		];
	}
}
