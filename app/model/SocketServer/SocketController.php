<?php

namespace Model\SocketServer;

use Nette,
	Ratchet\MessageComponentInterface,
	Ratchet\ConnectionInterface;
/**
 * Description of SocketController
 *
 * @author David Kuna
 */
class SocketController extends Nette\Object implements MessageComponentInterface {

	/** @var Nette\Database\Context */
	private $database;

	/**
	 * Private instance of RoomManager
	 * @var RoomManager
	 */
	private $roomManager;

	const TOKENCOOKIE = 'TOKEN';

	public function __construct(Nette\Database\Context $database)	{
		$this->database = $database;
		$this->roomManager = new RoomManager($this->database);
	}

	public function onClose(ConnectionInterface $conn) {
		$client = $this->roomManager->findClientById($conn->resourceId);
		$this->roomManager->disconnectClient($client);

        echo "Connection {$client->getId()} has disconnected\n";
	}

	public function onError(ConnectionInterface $conn, \Exception $e) {
		echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
	}

	public function onMessage(ConnectionInterface $from, $msg) {
        echo sprintf('Connection %d sending message "%s"' . "\n"
            , $from->resourceId, $msg);

		$sender = $this->roomManager->findClientById($from->resourceId);
		if($sender !== null){
			$message = new Message($msg);
			$this->roomManager->processMessage($sender, $message);
		}else{
			$from->send(RoomManager::ERROR_CLIENT_NOT_FOUND);
		}
	}

	public function onOpen(ConnectionInterface $conn) {
		$client = new Client($conn);
		$this->roomManager->connectClient($client);
		echo "New connection! ({$client->getId()})\n";
	}
}
