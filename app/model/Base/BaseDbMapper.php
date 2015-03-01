<?php

/**
 * Description of Table
 *
 * @author David Kuna
 */


/**
 * Represents repository for database table
 */
abstract class BaseDbMapper extends Nette\Object
{
	/** @var Nette\Database\Connection */
	protected $Database;

	/**
	 * Název tabulky v databázi
	 * @var string
	 */
	abstract function getTableName();

	/**
	 * @param  \Nette\Database\Context
	 * @throws \NetteAddons\InvalidStateException
	 */
	public function __construct(Nette\Database\Context $db)
	{
		$this->Database = $db;
	}



	/**
	 * @return \Nette\Database\Table\Selection
	 */
	protected function getTable()
	{
		return $this->Database->table($this->getTableName());
	}



	/**
	 * @return \Nette\Database\Table\Selection
	 */
	public function findAll()
	{
		return $this->getTable();
	}



	/**
	 * @param  array
	 * @return \Nette\Database\Table\Selection
	 */
	public function findBy(array $by)
	{
		return $this->getTable()->where($by);
	}



	/**
	 * @param  array
	 * @return \Nette\Database\Table\ActiveRow|FALSE
	 */
	public function findOneBy(array $by)
	{
		return $this->findBy($by)->limit(1)->fetch();
	}



	/**
	 * @param  int
	 * @return \Nette\Database\Table\ActiveRow|FALSE
	 */
	public function find($id)
	{
		return $this->getTable()->wherePrimary($id)->fetch();
	}

		/**
	 * Namappuje data z databáze na předanou Entitu (objekt), nejprve hledá setter, pak přímo property
	 * @param IBaseEntity $Entity
	 * @param array $data
	 * @return \IBaseEntity
	 */
	protected function mapEntity(IBaseEntity $Entity, array $data) {
		foreach ($data as $var => $value) {
			if (method_exists($Entity, $this->getSetterName($var))) {
				$Entity->{$this->getSetterName($var)}($value);
			} else if (property_exists($Entity, $var)) {
				$Entity->$var = $value;
			}
		}

		return $Entity;
	}


	/**
	 * Creates and inserts new row to database.
	 *
	 * @param  array row values
	 * @return \Nette\Database\Table\ActiveRow created row
	 * @throws \NetteAddons\DuplicateEntryException
	 * @throws \PDOException in case of SQL / database error
	 */
	protected function createRow(array $values)
	{
		try {
			return $this->getTable()->insert($values);

		} catch (\PDOException $e) {
			if (is_array($e->errorInfo) && $e->errorInfo[1] == 1062) {
				throw new \NetteAddons\DuplicateEntryException($e->getMessage(), $e->errorInfo[1], $e);
			} else {
				throw $e;
			}
		}
	}



	/**
	 * Insert row in database or update existing one.
	 *
	 * @param  array
	 * @return \Nette\Database\Table\ActiveRow automatically found based on first "column => value" pair in $values
	 */
	public function createOrUpdate(array $values)
	{
		$pairs = array();
		foreach ($values as $key => $value) {
			$pairs[] = "`$key` = ?"; // warning: SQL injection possible if $values infected!
		}

		$pairs = implode(', ', $pairs);
		$values = array_values($values);

		$this->Database->queryArgs(
			'INSERT INTO `' . $this->getTableName() . '` SET ' . $pairs .
			' ON DUPLICATE KEY UPDATE ' . $pairs, array_merge($values, $values)
		);

		return $this->findOneBy(func_get_arg(0));
	}
}