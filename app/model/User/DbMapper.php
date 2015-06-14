<?php

namespace Model\User;

/**
 * Description of DbMapper
 *
 * @author David Kuna
 */
class DbMapper extends \BaseDbMapper {

	/**
	 * Název tabulky v databázi
	 * @var string
	 */
	public function getTableName() {
		return 'users';
	}

	/**
	 * Vytvoří novou místnost a uloží ji do databáze
	 * @param string $source
	 * @return \Model\Rooms\Room
	 */
	public function updateUserName($sessid, $userName) {
		$data['sessid'] = $sessid;
		$data['user_name'] = $userName;
		$row = $this->createOrUpdate($data)->toArray();
	}

}
