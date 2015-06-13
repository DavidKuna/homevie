<?php

namespace App;

use Nette;

/**
 * Description of RoomPresenter
 *
 * @author David Kuna
 */
class MessagePresenter extends BasePresenter {

	/** @var Nette\Database\Context */
	private $database;
	private $roomId;

	public function __construct(Nette\Database\Context $database) {
		$this->database = $database;
	}



}
