<?php

namespace Model;

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
	
	private $command;
	private $who;
	private $data;
	private $string;
	
	/**
	 * Entity of socket data 
	 * @param string $data
	 */
	public function __construct($data){
		// TODO dodelat osetreni
		$json = json_decode($data, true);
		$this->string = $data;
		$this->command = $json['cmd'] ?: '';
		$this->data = $json['data'] ?: '';
		$this->who = isset($json['who']) ? $json['who'] : null;
	}
	
	public function getCommand(){
		return $this->command;
	}
	
	public function getSender(){
		return $this->who;
	}
	
	public function getData(){
		return $this->data;
	}
	
	public function getTime(){
		if(isset($this->data['time'])){
			return $this->data['time'];
		}else{
			return 0;
		}
	}
	
	public function toString(){
		return $this->string;
	}
}
