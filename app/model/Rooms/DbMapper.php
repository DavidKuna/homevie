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
	 * @param string $source
	 * @return \Model\Rooms\Room
	 */
	public function createNewRoom($source) {
		$data['name'] = time();
		$data['source'] = $source;
		$data['hash'] = $uniq_id = \Helpers\StringHelper::generateRandomString(30);
		$row = $this->createOrUpdate($data)->toArray();
		
		$settings['source'] =  $row['source'];
		$settings['hash'] =  $row['hash'];
		return new Room($row['id'], $settings);
	}

}
