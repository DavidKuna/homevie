<?php

namespace Model\SocketServer;

use Nette,
	Ratchet\ConnectionInterface;

/**
 * Description of Client
 *
 * @author David Kuna
 */
class Client extends Nette\Object {

	private $id;
	private $connection;
	private $token;
	private $roomId;
	public $userName;

	/**
	 * Entity of connection
	 * @param \Ratchet\ConnectionInterface $connection
	 * @param string $token
	 */
	public function __construct(ConnectionInterface $connection, $token = null){
		$this->connection = $connection;
		$this->id = $connection->resourceId;
		$this->token = $token;
	}

	public function getId(){
		return $this->id;
	}

	public function getToken(){
		return $this->token;
	}

	public function getRoomId(){
		return $this->roomId;
	}

	/**
	 * Send message to client
	 * @param json $message
	 */
	public function send($message){
		$this->connection->send($message);
	}

	/**
	 * @param int $id
	 */
	public function setRoomId($id){
		$this->roomId = $id;
	}

	public function setData($key, $value){
		$this->$key = $value;
	}
	
	public function getData($key) {
		return $this->$key;
	}

	public function setTokent($token){
		if(!empty($token)){
			$this->token = $token;
		}
	}

	public function getConnection(){
		return $this->connection;
	}

	private function init(){
		$this->sync();
	}

	private function sync(){

	}
}
