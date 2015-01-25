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
 * Allows to get table/field names.
 *
 * @version $Id:$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 * @scope prototype
 */
class Tx_Contentstage_ViewHelpers_TcaLabelViewHelper extends Tx_Fluid_Core_ViewHelper_AbstractViewHelper {
	
	/**
	 * The TCA utility object.
	 *
	 * @var Tx_Contentstage_Utility_Tca The TCA utility object.
	 */
	protected $tca = null;
	
	/**
	 * Injects the TCA utility object.
	 *
	 * @param Tx_Contentstage_Utility_Tca $diff The TCA utility object.
	 */
	public function injectTca(Tx_Contentstage_Utility_Tca $tca = null) {
		$this->tca = $tca;
	}
	
	/**
	 * constructor
	 *
	 * @return void
	 */
	public function __construct() {
		
	}

	/**
	 * Returns the name of the given table/field.
	 *
	 * @param string $table The table to get the name for.
	 * @param string $field The field to get the name for.
	 * @return string The field name.
	 * @author Nils Blattner <nb@cabag.ch>
	 */
	public function render($table, $field = null) {
		if ($field === null) {
			return $this->tca->getTableName($table);
		} else {
			return $this->tca->getFieldName($table, $field);
		}
	}
}


?>
