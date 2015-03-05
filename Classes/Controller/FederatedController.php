<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Lavinia Negru <ln@cabag.ch>, cab services ag
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
 *
 *
 * @package contentstage
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class Tx_Contentstage_Controller_FederatedController extends Tx_Contentstage_Controller_BaseController {
	/**
	 * The Federated utility object.
	 *
	 * @var Tx_Contentstage_Utility_Federated The Federated utility object.
	 */
	protected $federatedUtility = null;
	
	/**
	 * Injects the Federated utility object.
	 *
	 * @param Tx_Contentstage_Utility_Federated $federatedUtility The Federated utility object.
	 */
	public function injectFederated(Tx_Contentstage_Utility_Federated $federatedUtility = null) {
		$this->federatedUtility = $federatedUtility;
	}
	
	/**
	 * action initalize
	 *
	 * @return void
	 */
	public function showAction() {
	}
	
	/**
	 * action doFederated
	 *
	 * @return void
	 */
	public function doFederatedAction() {
		// federated object to convert tables
		$info = $this->extensionConfiguration['remote.']['db.'];
		try {
			foreach (t3lib_div::trimExplode(',', $this->extensionConfiguration['tables.']['toFederate'], true) as $table) {
				$result = $this->federatedUtility->convertTables($this->localRepository, $this->remoteRepository, $table, $info);
				$this->log->log($this->translate('info.' . ($result ? 'federated' : 'alreadyFederated'), array($table)), Tx_CabagExtbase_Utility_Logging::OK);
			}
		} catch (Exception $e) {
			$this->log->log($this->translate('error.' . $e->getCode(), array($e->getMessage())), Tx_CabagExtbase_Utility_Logging::ERROR);
		}
		
		$this->log->write();
		$this->redirect('show');
	}

}