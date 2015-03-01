<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Model;

/**
 * Description of Session
 *
 * @author David Kuna
 */
class Sessions extends \BaseDbMapper{

	/**
	 * @return string
	 */
	public function getTableName() {
		return 'session';
	}

	/**
	 * @param string
	 * @return \Nette\Database\Table\ActiveRow|FALSE
	 */
	public function findOneByToken($slug)
	{
		return $this->getTable()->where('token = ?', $slug)->order('id DESC')->limit(1)->fetch();
	}



	/**
	 * @return \Nette\Database\Table\Selection
	 */
	public function findAll()
	{
		return $this->getTable()->group('token')->order('revision DESC');
	}


// TODO
	/**
	 * @param int
	 * @param string
	 * @param string
	 * @return \Nette\Database\Table\ActiveRow
	 * @throws \InvalidArgumentException
	 */
	public function saveSession($sessionId, $roomId, $content)
	{
		return $this->createRow(array(
			'authorId'  => $authorId,
			'name'      => $name,
			'slug'      => Strings::webalize($name),
			'content'   => $content,
			'createdAt' => new \DateTime,
			'revision'  => 1,
		));
	}


// TODO
	/**
	 * @param int
	 * @param int
	 * @param string
	 * @param string
	 * @param int
	 * @return int|NULL
	 */
	public function updateSession($id, $authorId, $name, $content)
	{
		$row = $this->find($id);

		if (!$row) {
			return NULL;
		}

		return $row->update(array(
			'authorId'  => $authorId,
			'name'      => $name,
			'content'   => $content,
			'createdAt' => new \DateTime,
			'revision'  => $row->revision + 1,
		));
	}

}
