<?php

namespace Model;

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
	
	protected $rooms;
	protected $clients;

	public function __construct(Nette\Database\Context $database)	{
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
	public function processMessage(Client $sender, Message $message){
		
		switch ($message->getCommand()){
			case Message::JOIN: $this->joinClient($sender, $message); break;
			case Message::SOURCE: $this->processMessageSource($sender, $message); break;
			case Message::PLAY: $this->processMessagePlay($sender, $message); break;
			case Message::PAUSE: $this->processMessagePause($sender, $message);	break;
		}
		
		$receivers = $this->sendToRoommates($sender, $message);
		$this->sendToReceivers($sender, $receivers);
	}
	
	private function processMessageSource(Client $sender, Message $message){
		$table = $this->database->table('room');
		$table->where('id',$sender->getRoomId())->update(array('source' => $message->getData()));
		$this->getRoom($sender->getRoomId())->setSetting('source', $message->getData());
	}
	
	private function processMessagePlay(Client $sender, Message $message){
		$this->getRoom($sender->getRoomId())->setArraySetting(
			['status' => Room::PLAYING, 'time' => $message->getTime()]
		); 
	}
	
	private function processMessagePause(Client $sender, Message $message){
		$this->getRoom($sender->getRoomId())->setArraySetting(
			['status' => Room::PAUSED, 'time' => $message->getTime()]
		); 
	}
	
	private function sendToRoommates(Client $client, Message $message){
		$receivers = array();
		$table = $this->database->table('session');
		$table->select('client')->where('room_id', $client->getRoomId());
		while($row = $table->fetch()){
			$receivers[$row['client']] = array(
				'id' => $row['client'],
				'msg' => $message->toString()
			);
		}
		return $receivers;
	}
	
	//TODO zjistit jestli room existuje a pripadne vytvorit a nastavit z databaze
	private function joinClient(Client $client, Message $message){
		$client->setTokent($message->getData());
		if($this->syncClient($client)){
			echo "Client ({$client->getId()}) has been joined to room {$client->getRoomId()}\n";
			echo "Setting: " . $this->getRoom($client->getRoomId())->getSettingMessage() ."\n";
			$client->send($this->getRoom($client->getRoomId())->getSettingMessage());
		}else{
			echo "Creating client session failed! ({$client->getId()})\n";
			$client->send(self::ERROR_SYNC);
		}	
	}
	
	private function getRoom($id){
		if(isset($this->rooms[$id])){
			return $this->rooms[$id];
		}else{
			throw new \Exception("Room {$id} doesn't exists");
		}
	}
	
	/**
	 * 
	 * @param \Model\Client $client
	 * @return \Model\Client
	 */
	public function syncClient(Client $client){
		$data = array(
			'client' => $client->getId()
		);
		$session = $this->database->table('session')->where('token', $client->getToken());
		$session->update($data);
		$session->select('phpsessid, room_id');
		$row = $session->fetch();
		if(isset($row->room_id)){
			$client->setRoomId($row->room_id);
			$this->assignOwner($client->getRoomId());
			return true;
		}else{
			return false;
		}
	}
	
	public function connectClient(Client $client){
		$this->clients->attach($client);
	}
	
	/**
	 * 
	 * @param \Model\Client $client
	 * @return int
	 */
	public function disconnectClient(Client $client){
		$data = array(
			'owner' => 0,
			'room_id' => 0
			);
		$this->database->table('session')
			->where('client',$client->getId())->update($data);
		$this->assignOwner($client->getRoomId(), $client->getId());
		$this->clients->detach($client);
		return $this->database->table('session')->where('token', $client->getToken())->delete();
	}
	
	/**
	 * 
	 * @param int $room
	 * @param int $instead
	 * @return boolean
	 */
	private function assignOwner($room, $instead = 0){
		$owner = $this->database->table('session')->where('room_id', $room)->where('owner','1')->select('client');
		$ownerId = $owner->fetch();
		if($ownerId === false){
			$table = $this->database->table('session');
			$table->where('client > 0')->where('client != ?', $instead)->where('room_id',$room)
					->order('created_at')->limit(1)->select('client');
			$newowner = $table->fetch();
			if($newowner !== false){
				$table = $this->database->table('session');
				$table->where('client',$newowner->client)->update(array('owner' => 1));
				$this->getRoom($room)->setOwner($newowner->client);
			}else{
				//TODO DELETE ROOM
				echo "DELETE ROOM\n";
			}
		}else{
			$this->getRoom($room)->setOwner($ownerId);
		}
	}
		
	private function sendToReceivers($sender, $receivers){
		 foreach ($this->clients as $client) {
			if(\array_key_exists($client->getId(), $receivers)){
				if ($client->getId() !== $sender->getId()) {
					$client->send($receivers[$client->getId()]['msg']);
				}
			}
        }
	}
	
	/**
	 * 
	 * @param int $clientId
	 * @return Client
	 */
	public function findClientById($clientId){
		foreach ($this->clients as $client) {
            if ($clientId === $client->getId()) {
                return $client;
            }
        }
		return null;
	}
	
	private function initDatabase(){
		$this->database->query("TRUNCATE TABLE `session`");
		return true;
	}
	
	private function initRooms(){
		$rooms = $this->database->table('room');
		while($room = $rooms->fetch()){
			$this->rooms[$room->id] = new Room($room->id);
		}
	}
}
