<?php

namespace Model\SocketServer;

use Nette;

/**
 * Description of RoomManager
 *
 * @author David Kuna
 */
class RoomManager extends Nette\Object {

	const ERROR_SYNC = '{"cmd":"err","who":"server","msg":"Synchronization failed"}';
	const ERROR_CLIENT_NOT_FOUND = '{"cmd":"err","who":"server","msg":"Client was not found"}';

	/** @var Nette\Database\Context */
	private $database;

	/**
	 * @var \Model\Rooms\Room[]
	 */
	protected $rooms;
	protected $clients;

	public function __construct(Nette\Database\Context $database) {
		$this->database = $database;
		$this->rooms = array();
		$this->clients = new \SplObjectStorage;
		$this->initDatabase();
		$this->initRooms();
	}

	/**
	 *
	 * @param \Model\Client $sender
	 * @param JSON $message
	 * @return array
	 */
	public function processMessage(Client $sender, Message $message) {

		switch ($message->getCommand()) {
			case Message::JOIN: $this->joinClient($sender, $message);
				break;
			case Message::SOURCE: $this->processMessageSource($sender, $message);
				break;
			case Message::PLAY: $this->processMessagePlay($sender, $message);
				break;
			case Message::CHAT: $this->processMessageChat($sender, $message);
				break;
		}

		$message = $message->appendToMsg("client_id", $sender->getID());

		$receivers = $this->getRoomatesArray($sender, $message);
		$this->sendToReceivers($sender, $receivers);
	}

	private function processMessageChat(Client $sender, Message $message) {
		//save messages etc
	}

	private function processMessageSource(Client $sender, Message $message) {
		$table = $this->database->table('room');
		$table->where('id', $sender->getRoomId())->update(array('source' => $message->getData()));
		$this->getRoom($sender->getRoomId())->setSetting('source', $message->getData());
	}

	private function processMessagePlay(Client $sender, Message $message) {
		$this->getRoom($sender->getRoomId())->setArraySetting(
				['status' => \Model\Rooms\Room::PLAYING, 'time' => $message->getTime()]
		);
	}

	private function processMessagePause(Client $sender, Message $message) {
		$this->getRoom($sender->getRoomId())->setArraySetting(
				['status' => \Model\Rooms\Room::PAUSED, 'time' => $message->getTime()]
		);
	}

	private function getRoomatesArray(Client $client, Message $message) {
		$receivers = array();
		$table = $this->database->table('session');
		$table->select('client')->where('room_id', $client->getRoomId());
		while ($row = $table->fetch()) {
			$receivers[$row['client']] = array(
				'id' => $row['client'],
				'msg' => $message->toString()
			);
		}
		return $receivers;
	}

	//TODO zjistit jestli room existuje a pripadne vytvorit a nastavit z databaze
	private function joinClient(Client $client, Message $message) {
		$client->setTokent($message->getData());
		if ($this->syncClient($client)) {
			echo "Client ({$client->getId()}) has been joined to room {$client->getRoomId()}\n";
			echo "Setting: " . $this->getRoom($client->getRoomId())->getSettingMessage() . "\n";
			$client->send($this->getRoom($client->getRoomId())->getSettingMessage());
		} else {
			echo "Creating client session failed! ({$client->getId()})\n";
			$client->send(self::ERROR_SYNC);
		}
	}

	private function getRoom($id) {
		if (isset($this->rooms[$id])) {
			return $this->rooms[$id];
		} elseif ($this->findAndAppendRoom($id)) {
			return $this->getRoom($id);
		} else {
			throw new \Exception("Room {$id} doesn't exists");
		}
	}

	/**
	 *
	 * @param \Model\Client $client
	 * @return \Model\Client
	 */
	public function syncClient(Client $client) {
		$data = array(
			'client' => $client->getId()
		);
		$session = $this->database->table('session')->where('token', $client->getToken());
		$session->update($data);
		$session->select('phpsessid, room_id');
		$row = $session->fetch();
		if (isset($row->room_id)) {
			$client->setRoomId($row->room_id);
			$this->assignOwner($client->getRoomId());
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Přidá klienta do vnitřního seznamu
	 * @param \Model\SocketServer\Client $client
	 */
	public function connectClient(Client $client) {
		$this->clients->attach($client);
	}

	/**
	 *
	 * @param \Model\Client $client
	 * @return int
	 */
	public function disconnectClient(Client $client) {
		$data = array(
			'owner' => 0,
			'room_id' => 0
		);
		$this->database->table('session')
				->where('client', $client->getId())->update($data);
		$this->assignOwner($client->getRoomId(), $client->getId());
		$this->clients->detach($client);
		$this->clearRooms();
		return $this->database->table('session')->where('token', $client->getToken())->delete();
	}

	/**
	 *
	 * @param int $roomId
	 * @param int $instead
	 * @return boolean
	 */
	private function assignOwner($roomId, $instead = 0) {
		$owner = $this->database->table('session')->where('room_id', $roomId)->where('owner', '1')->select('client');
		$ownerId = $owner->fetch();
		if ($ownerId === false) {
			$table = $this->database->table('session');
			$table->where('client > 0')->where('client != ?', $instead)->where('room_id', $roomId)
					->order('created_at')->limit(1)->select('client');
			$newowner = $table->fetch();
			if ($newowner !== false) {
				$table = $this->database->table('session');
				$table->where('client', $newowner->client)->update(array('owner' => 1));
				$this->getRoom($roomId)->setOwner($newowner->client);
			} else {
				/* Mazání se musí domyslet. Tady to nejde protože při refreshi posledního
				 * diváka se nejdříve na FE zjistí, že místnost existuje
				 * ale po odpojení z WS se tady místnost smaže.
				 *
				  $this->database->table('room')->where('id', $roomId)->delete();
				 */
				unset($this->rooms[$roomId]);
				echo "DELETE ROOM\n";
			}
		} else {
			$this->getRoom($roomId)->setOwner($ownerId);
		}
	}

	/**
	 * Projde všechny přijemce v poli a odešle na jich příslušné zprávy
	 * @param Client $sender
	 * @param array $receivers Pole ID klientů a zpráv, které se jim mají odeslat
	 */
	private function sendToReceivers($sender, array $receivers) {
		foreach ($this->clients as $client) {
			if (\array_key_exists($client->getId(), $receivers)) {
				if ($client->getId() !== $sender->getId()) {
					$client->send($receivers[$client->getId()]['msg']);
				}
			}
		}
	}

	/**
	 * @param int $clientId
	 * @return Client
	 */
	public function findClientById($clientId) {
		foreach ($this->clients as $client) {
			if ($clientId === $client->getId()) {
				return $client;
			}
		}
		return null;
	}

	/**
	 * Počáteční nastavení tabulek databáze po spuštění socket serveru
	 * @return boolean
	 */
	private function initDatabase() {
		$this->database->query("TRUNCATE TABLE `session`");
		return true;
	}

	/**
	 * Načte místnosti z databáze do vnitřního pole
	 */
	private function initRooms() {
		$rooms = $this->database->table('room');
		while ($room = $rooms->fetch()) {
			$this->rooms[$room->id] = new \Model\Rooms\Room($room->id);
		}
	}

	/**
	 * Pokusí se najít místnost v databázi a přidat ji do vnitřního pole
	 * @param int $roomId
	 */
	private function findAndAppendRoom($roomId) {
		$rooms = $this->database->table('room')->where(array('id' => $roomId));
		if ($rooms->count() > 0) {
			$room = $rooms->fetch();
			$this->rooms[$room->id] = new \Model\Rooms\Room($room->id);
			return true;
		}

		return false;
	}

	// TODO - nejde brát podle created_at ale je třeba přidat atribut last action nebo tak
	private function clearRooms() {

		$this->database->table('room')
			->where("(created_at + INTERVAL 5 MINUTE <= NOW()) AND id NOT IN (" . implode(',', array_keys($this->rooms)) . ")")
			->delete();
	}

}
