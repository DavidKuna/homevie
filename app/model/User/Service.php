<?php

namespace Model\User;

/**
 * Description of Service
 *
 * @author David Kuna
 */
class Service {

	/** @var Model\Rooms\DbMapper */
	private $DbMapper;

	public function __construct(\Model\User\DbMapper $DbMapper) {
		$this->DbMapper = $DbMapper;
	}

	/**
	 * Vytvoří novou místnost a uloží ji do databáze
	 * @param string $source
	 * @return \Model\Rooms\Room
	 */
	public function generateUserName() {
		$names = array("Apple", "Orange", "Pineapple", "Melon", "Lemon", "Peach", "Strawberry", "Blueberry");
		$userName = $names[array_rand($names, 1)];
		return $userName;
	}
	
	public function updateUserName($sessid, $userName) {
		$this->DbMapper->updateUserName($sessid, $userName);
	}

}
