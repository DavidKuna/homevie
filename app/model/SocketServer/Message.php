<?php

namespace Model\SocketServer;

use Nette;

/**
 * Description of Client
 *
 * @author David Kuna
 */
class Message extends Nette\Object {

	const JOIN = 'join';
	const SOURCE = 'source';
	const PLAY = 'play';
	const PAUSE = 'pause';
	const CHAT = 'chat';
	const DISCONNECT = 'disconnect';
	
	private $command;
	private $who;
	private $data;
	private $string;

	/**
	 * Entity of socket data
	 * @param string $data
	 */
	public function __construct($data, $isArray = false) {

		if ($isArray) {
			$json = $data;
			$data = json_encode($json);
		} else {
			$json = json_decode($data, true);
		}

		$this->string = $data;
		$this->command = $json['cmd'] ? : '';
		$this->data = $json['data'] ? : '';
		$this->who = isset($json['who']) ? $json['who'] : null;
	}

	public function getCommand() {
		return $this->command;
	}

	public function getSender() {
		return $this->who;
	}

	public function getData() {
		return $this->data;
	}

	public function getTime() {
		if (isset($this->data['time'])) {
			return $this->data['time'];
		} else {
			return 0;
		}
	}

	public function getMessage() {
		if (isset($this->data['text'])) {
			return $this->data['text'];
		} else {
			return false;
		}
	}
	
	public function getUserName() {
		if (isset($this->data['user_name'])) {
			return $this->data['user_name'];
		} else {
			return "";
		}
	}

	public function toString() {
		$json['cmd'] = @$this->command;
		$json['data'] = @$this->data;
		$json['who'] = @$this->who;
		$json = json_encode($json);
		$this->string = $json;
		return $json;
	}

	public function convertToArray() {
		$this->data = (array) $this->data;
		return $this;
	}

	public function appendToMsg($key, $value) {
		$this->data[$key] = $value;
		return $this;
	}

}
