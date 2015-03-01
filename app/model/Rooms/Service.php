<?php

namespace Model\Rooms;

/**
 * Description of Service
 *
 * @author David Kuna
 */
class Service {

	/** @var Model\Rooms\DbMapper */
	private $DbMapper;

	public function __construct(\Model\Rooms\DbMapper $DbMapper) {
		$this->DbMapper = $DbMapper;
	}

	/**
	 * Vytvoří novou místnost a uloží ji do databáze
	 * @return \Model\Rooms\Room
	 */
	public function createNewRoom(){
		return $this->DbMapper->createNewRoom();
	}
}
