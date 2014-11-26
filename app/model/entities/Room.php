<?php

namespace Model;

use Nette;

/**
 * Description of Room
 *
 * @author David Kuna
 */
class Room extends Nette\Object {
	const PAUSED = 0;
	const PLAYING = 1;
	
	private $id;
	private $timeStamp;
	private $owner;
	private $setting = array(
		'source' => null,
		'status' => self::PAUSED,
		'time' => 0
	);
	
	public function __construct($id, $setting = null){
		$this->id = $id;
		if(is_array($setting)){
			$this->setArraySetting($setting);
		}
	}
	
	public function getId(){
		return $this->id;
	}
	
	public function setOwner($clientId){
		$this->owner = $clientId;
	}
	
	public function getOwner(){
		return $this->owner;
	}
		
	public function getSetting($key){
		return $this->setting[$key] ?: null;
	}
	
	public function setSetting($key, $value){
		if($key === 'time'){
			$this->timeStamp = microtime(true);
		}
		$this->setting[$key] = $value;
	}
	
	public function setArraySetting($array){
		foreach ($array as $key => $value){
			$this->setSetting($key, $value);
		}
	}
	
	public function getCurrentTime(){
		$increment = 0;
		if($this->setting['status'] === self::PLAYING){
			$increment = microtime(true) - $this->timeStamp;
		}
		
		return floatval($this->setting['time']) + $increment;
	}
		
	public function getSettingMessage(){
		$setting = $this->setting;
		$setting['time'] = $this->getCurrentTime();
		$message = array(
			'cmd' => 'setting',
			'data' => $setting,
			'who' => 'server'
		);

		return json_encode($message);
	}
}
