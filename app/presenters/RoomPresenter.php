<?php

namespace App;

use Nette, OpenTok\OpenTok;

/**
 * Description of RoomPresenter
 *
 * @author David Kuna
 */
class RoomPresenter extends BasePresenter {

	/** @var Nette\Database\Context */
	private $database;
	private $roomId;

	const SALT = "3-L*)!dZ";

	public function __construct(Nette\Database\Context $database) {
		$this->database = $database;
	}

	public function renderDefault() {
		$this->template->rooms = $this->database->table('room')
				->order('created_at DESC')
				->limit(5);
	}

	private function getSeesionId() {
		$id = $this->context->session->getId();
		if (empty($id)) {
			$this->context->session->start();
			return $this->getSeesionId();
		} else {
			return $id;
		}
	}

	private function generateToken() {
		$sessionId = $this->getSeesionId();
		return md5(self::SALT . $sessionId . time());
	}

	private function getRoomMessages($room_id) {
		$messages = $this->database->table("message")->where("room_id = ?", $room_id);
		return $messages;
	}

	public function renderView($roomId) {

		$this->roomId = $roomId;
		if (($this->template->room = $this->database->table('room')->get($this->roomId)) === FALSE) {
			$this->redirect("Room:create");
			exit;
		}
		$this->template->OT_data = $this->getOpenTokData();

		$data['token'] = $this->generateToken();
		$data['room_id'] = $this->roomId;
		$data['phpsessid'] = $this->getSeesionId();
		//$data['owner'] = 0;

		$messages = $this->getRoomMessages($roomId);
		$this->template->messages = $messages;
		$this->template->token = $data['token'];
		$this->sessions->createOrUpdate($data);
		$this->context->httpResponse->setCookie('TOKEN', $data['token'], '1 days', null, null, null, false);
	}

	private function getOpenTokData() {
		
		$key = "45193792";
		$secret = "0fb9c2c8bb922fe0c53213448e116651c02a4e12";
		
		$apiObj = new OpenTok($key, $secret);
		$session = $apiObj->createSession();

		$data['apiKey'] = $key;
		$data['sessionId'] = $session->getSessionId();
		$data['token'] = $apiObj->generateToken($data['sessionId']);

		return $data;
	}

	public function renderCreate($source) {
		$newRoom = $this->context->ServiceRooms->createNewRoom($source);
		$this->redirect("Room:view", array('roomId' => $newRoom->getId()));
		exit;
	}

}
