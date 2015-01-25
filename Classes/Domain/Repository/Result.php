<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Nils Blattner <nb@cabag.ch>, cab services ag
 *  
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Pseudo repository result object.
 *
 * @package contentstage
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class Tx_Contentstage_Domain_Repository_Result {
	/**
	 * @var Tx_Contentstage_Domain_Repository_ContentRepository The parent repository.
	 */
	protected $repository = null;
	
	/**
	 * @var resource DB-resource.
	 */
	protected $resource = false;
	
	/**
	 * The internal t3lib_db.
	 *
	 * @var t3lib_db
	 */
	protected $db = null;
	
	/**
	 * The table of this result.
	 *
	 * @var string
	 */
	protected $table = null;
	
	/**
	 * The table config array for this table.
	 *
	 * @var array
	 */
	protected $tca = null;
	
	/**
	 * @var int The count of the rows retrieved.
	 */
	protected $count = 0;
	
	/**
	 * @var boolean Whether or not the end of the resource was reached.
	 */
	protected $countDone = false;
	
	/**
	 * The TCA utility object.
	 *
	 * @var Tx_Contentstage_Utility_Tca The TCA utility object.
	 */
	protected $tcaObject = null;
	
	/**
	 * Injects the TCA utility object.
	 *
	 * @param Tx_Contentstage_Utility_Tca $diff The TCA utility object.
	 */
	public function injectTca(Tx_Contentstage_Utility_Tca $tcaObject = null) {
		$this->tcaObject = $tcaObject;
	}
	
	/**
	 * Set the repository.
	 *
	 * @param Tx_Contentstage_Domain_Repository_ContentRepository $repository The repository.
	 * @return array A row.
	 */
	public function setRepository(Tx_Contentstage_Domain_Repository_ContentRepository $repository) {
		$this->repository = $repository;
		$this->db = $this->repository->_getDb();
	}
	
	/**
	 * Get the repository.
	 *
	 * @return Tx_Contentstage_Domain_Repository_ContentRepository The repository.
	 */
	public function getRepository() {
		return $this->repository;
	}
	
	/**
	 * Set the resource.
	 *
	 * @param resource $resource The resource.
	 * @return array A row.
	 */
	public function setResource($resource) {
		$this->resource = $resource;
	}
	
	/**
	 * Get the resource.
	 *
	 * @return resource The resource.
	 */
	public function getResource() {
		return $this->resource;
	}
	
	/**
	 * Set the table.
	 *
	 * @param string $table The table.
	 * @return array A row.
	 */
	public function setTable($table) {
		$this->table = $table;
		$this->tca = &$this->tcaObject->getProcessedTca($table);
	}
	
	/**
	 * Get the table.
	 *
	 * @return string The table.
	 */
	public function getTable() {
		return $this->table;
	}
	
	/**
	 * Returns the next available row or false.
	 *
	 * @return mixed The associative array or false.
	 */
	public function next() {
		$row = $this->db->sql_fetch_assoc($this->resource);
		
		if ($row === false) {
			$this->countDone = true;
		} else {
			$this->count++;
		}
		
		return $row;
	}
	
	/**
	 * Returns the next row, with the relations resolved.
	 *
	 * @see self::next()
	 */
	public function nextResolved() {
		if (($row = $this->next()) !== false) {
			foreach ($this->tca as $field => &$config) {
				if (substr($field, 0, 2) === '__') {
					continue;
				}
				$row[$field] = $this->tcaObject->resolve($this->repository, $this->table, $field, $row[$field], $row);
			}
		}
		
		return $row;
	}
	
	/**
	 * Returns the next row, with the relations stored.
	 *
	 * @see self::next()
	 */
	public function nextWithRelations() {
		if (($row = $this->next()) !== false) {
			$this->repository->setRelationSynced($this->table, $row['uid']);
			foreach ($this->tca as $field => &$config) {
				if (substr($field, 0, 2) === '__') {
					continue;
				}
				$this->repository->addRelations($this->tcaObject->resolveUids($this->repository, $this->table, $field, $row[$field], $row));
			}
		}
		
		return $row;
	}
	
	/**
	 * Returns all records.
	 *
	 * @param string $onlyField If $onlyField is set, the returned array will directly contain the values of that field.
	 * @return array Array of rows.
	 */
	public function all($onlyField = null) {
		$rows = array();
		
		while (($row = $this->next()) !== false) $rows[] = ($onlyField === null ? $row : $row[$onlyField]);
		
		return $rows;
	}
	
	/**
	 * Returns the amount of rows in the result.
	 * Important: If count() is called after all rows are read, this operation is only returning the total.
	 * If count() is called before the read is done, this has to call the database for the total count!
	 *
	 * @return int The row count.
	 */
	public function count() {
		if (!$this->countDone) {
			$this->count = $this->db->sql_num_rows($this->resource);
			$this->countDone = true;
		}
		
		return $this->count;
	}
	
	/**
	 * Return the memory for the sql query.
	 *
	 * @return void
	 */
	public function free() {
		$this->db->sql_free_result($this->resource);
	}
}
?>
