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
 * Hook to make sure review tables can be deleted.
 *
 * @package contentstage
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class tx_Contentstage_Hook_TCEMain implements t3lib_TCEmain_checkModifyAccessListHook {
	/**
	 * Hook that determines whether a user has access to modify a table.
	 *
	 * @param	boolean			&$accessAllowed: Whether the user has access to modify a table
	 * @param	string			$table: The name of the table to be modified
	 * @param	t3lib_TCEmain	$parent: The calling parent object
	 * @return	void
	 */
	public function checkModifyAccessList(&$accessAllowed, $table, t3lib_TCEmain $parent) {
		if (substr($table, 0, 29) === 'tx_contentstage_domain_model_') {
			$accessAllowed = true;
		}
	}
}
?>