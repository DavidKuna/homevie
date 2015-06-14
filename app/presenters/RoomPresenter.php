<?php

namespace App;

use Nette,
	Nette\Application\UI;

/**
 * Description of RoomPresenter
 *
 * @author David Kuna
 */
class RoomPresenter extends BasePresenter {

	/** @var Nette\Database\Context */
	private $database;
	private $roomId;
	private $roomHash;
	public $userName;

	const SALT = "3-L*)!dZ";

	public function __construct(Nette\Database\Context $database) {
		$this->database = $database;
	}

	public function renderDefault() {
		$this->template->rooms = $this->database->table('room')
				->order('created_at DESC')
				->limit(5);
	}

	private function generateToken() {
		$sessionId = $this->getSeesionId();
		return md5(self::SALT . $sessionId . time());
	}

	private function getRoomMessages($room_id) {
		$messages = $this->database->table("message")->where("room_id = ?", $room_id)->order("created_at ASC");
		return $messages;
	}

	public function renderView($roomHash) {

		$this->roomHash = $roomHash;
		$room = $this->database->table('room')->where("hash", $this->roomHash)->select("source, id")->fetch();

		if (empty($room)) {
			$this->redirect("Room:create");
			exit;
		}

		$roomId = $room->id;
		$this->roomId = $roomId;
//		Nette\Diagnostics\Debugger::barDump($room);
		$this->template->room = $room;

		$data['token'] = $this->generateToken();
		$data['room_id'] = $this->roomId;
		$data['phpsessid'] = $this->getSeesionId();
		$data['user_name'] = $this->userName;
		
		//$data['owner'] = 0;

		$messages = $this->getRoomMessages($roomId);
		$this->template->messages = $messages;
		$this->template->token = $data['token'];

		$this->sessions->createOrUpdate($data);
		$this->context->httpResponse->setCookie('TOKEN', $data['token'], '1 days', null, null, null, false);
	}

	protected function createComponentSearchForm() {
		$form = new UI\Form;
		$form->addText('query', '')
				->setAttribute('class', 'appSearchInput')
				->setAttribute('placeholder', 'Search a video or paste URL')
				->setAttribute('ng-model', 'searchQuery')
				->setAttribute('ng-submit', 'setSourceURL()')
				->setRequired('Zadejte prosím URL videa na youtube.com');
		$form->addSubmit('search', 'Go')
				->setAttribute('class', 'appSearchButt');
		return $form;
	}

	public function renderCreate($source) {
		if (isset($source)) {
			$newRoom = $this->context->ServiceRooms->createNewRoom($source);
			$this->redirect("Room:view", array('roomHash' => $newRoom->getHash()));
		} else {
			$this->flashMessage('You have to fill video source');
			$this->redirect("Homepage:default");
		}
		exit;
	}

}
