<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2015 Nils Blattner <nb@cabag.ch>, cab services ag
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
 * Empty repository result object.
 *
 * @package contentstage
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class Tx_Contentstage_Domain_Repository_EmptyResult extends Tx_Contentstage_Domain_Repository_Result {
	
	/**
	 * Get the resource.
	 *
	 * @return resource The resource.
	 */
	public function getResource() {
		return 0;
	}
	
	/**
	 * Get the query.
	 *
	 * @return string The query.
	 */
	public function getQuery() {
		return '';
	}
	
	/**
	 * Get the late binding fields.
	 *
	 * @return false|array The late binding fields.
	 */
	public function getLateBindingFields() {
		return false;
	}
	
	/**
	 * Returns the next available row or false.
	 *
	 * @return mixed The associative array or false.
	 */
	public function next() {
		return false;
	}
	
	/**
	 * Returns the current row or false.
	 *
	 * @return mixed The associative array or false.
	 */
	public function current() {
		return false;
	}
	
	/**
	 * Returns the amount of rows in the result.
	 * Important: If count() is called after all rows are read, this operation is only returning the total.
	 * If count() is called before the read is done, this has to call the database for the total count!
	 *
	 * @return int The row count.
	 */
	public function count() {
		return 0;
	}
	
	/**
	 * Return the memory for the sql query.
	 *
	 * @return void
	 */
	public function free() {
		return;
	}
}
