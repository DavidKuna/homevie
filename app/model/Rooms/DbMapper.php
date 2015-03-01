<?php

namespace Model\Rooms;


/**
 * Description of DbMapper
 *
 * @author David Kuna
 */
class DbMapper extends \BaseDbMapper{

	/**
	 * Název tabulky v databázi
	 * @var string
	 */
	public function getTableName() {
		return 'room';
	}

	/**
	 * Vytvoří novou místnost a uloží ji do databáze
	 * @return \Model\Rooms\Room
	 */
	public function createNewRoom() {
		$data['name'] = time();
		$data['source'] = 'http://www.youtube.com/';
		$row = $this->createOrUpdate($data)->toArray();

		$settings['source'] =  $row['source'];
		return new Room($row['id'], $settings);
	}

}
