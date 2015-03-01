<?php

namespace Helpers;

/**
 * Description of RoomManager
 *
 * @author David Kuna
 */
class StringHelper {

	/**
	 * Vygeneruje náhodný string o zadané délce
	 * @param type $length
	 * @return string
	 */
	public static function generateRandomString($length = 10) {
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		}

		return $randomString;
	}
}
