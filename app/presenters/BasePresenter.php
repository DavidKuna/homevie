<?php

namespace App;

use Nette,
	Model;

/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter {

	/**
	 * @var Model\Sessions
	 * @inject
	 */
	public $sessions;
	public $userName;

	private function generateUserName() {
		$userName = $this->context->ServiceUser->generateUserName();
		return $userName;
	}

	public function getSeesionId() {
		$id = $this->context->session->getId();
		if (empty($id)) {
			$this->context->session->start();
			return $this->getSeesionId();
		} else {
			return $id;
		}
	}

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

		$userSection = $this->getSession('user');
		$user = $userSection->userName;
		if ($user) {
			$userName = $user;
		} else {
			$userName = $this->generateUserName();
			$userSection->userName = $userName;

			$sessid = $this->getSeesionId();
			$this->context->ServiceUser->updateUserName($sessid, $userName);
		}

		$this->template->userName = $userName;
		$this->userName = $userName;
	}

}
