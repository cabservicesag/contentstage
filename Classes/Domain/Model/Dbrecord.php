<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 
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
 *
 *
 * @package contentstage
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class Tx_Contentstage_Domain_Model_Dbrecord extends Tx_Extbase_DomainObject_AbstractEntity {

	/**
	 * The table the record comes from
	 *
	 * @var string
	 */
	protected $tablename;

	/**
	 * Teh record uid
	 *
	 * @var int
	 */
	protected $recorduid = FALSE;

	/**
	 * The review.
	 *
	 * @var Tx_Contentstage_Domain_Model_Review
	 */
	protected $review;

	/**
	 * Returns the tablename
	 *
	 * @return string $tablename
	 */
	public function getTablename() {
		return $this->tablename;
	}

	/**
	 * Sets the tablename
	 *
	 * @param string $tablename
	 * @return void
	 */
	public function setTablename($tablename) {
		$this->tablename = $tablename;
	}

	/**
	 * Returns the recorduid
	 *
	 * @return int $recorduid
	 */
	public function getRecorduid() {
		return $this->recorduid;
	}

	/**
	 * Sets the recorduid
	 *
	 * @param int $recorduid
	 * @return void
	 */
	public function setRecorduid($recorduid) {
		$this->recorduid = $recorduid;
	}
}