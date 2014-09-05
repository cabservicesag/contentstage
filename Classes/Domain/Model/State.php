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
class Tx_Contentstage_Domain_Model_State extends Tx_Extbase_DomainObject_AbstractEntity {
	
	/**
	 * New review record.
	 */
	const FRESH = 'fresh';
	
	/**
	 * Review record reinitialized.
	 */
	const REINITIALIZED = 'reinitialized';
	
	/**
	 * Patrially reviewed (+1).
	 */
	const PARTIAL = 'partial';
	
	/**
	 * Fully reviewed.
	 */
	const REVIEWED = 'reviewed';
	
	/**
	 * Pushed to remote.
	 */
	const PUSHED = 'pushed';
	
	/**
	 * Review rejected.
	 */
	const REJECTED = 'rejected';
	
	/**
	 * Review deprecated (changes since creation).
	 */
	const DEPRECATED = 'deprecated';
	
	/**
	 * The possible next states by state.
	 */
	protected static $next = array(
		self::FRESH => array(
			self::REINITIALIZED,
			self::PARTIAL,
			self::REVIEWED,
			self::REJECTED,
			self::DEPRECATED
		),
		self::REINITIALIZED => array(
			self::PARTIAL,
			self::REJECTED,
			self::DEPRECATED
		),
		self::PARTIAL => array(
			self::REINITIALIZED,
			self::PUSHED,
			self::REVIEWED,
			self::REJECTED,
			self::DEPRECATED
		),
		self::REJECTED => array(
			self::PARTIAL,
			self::PUSHED,
			self::REVIEWED,
			self::REINITIALIZED
		),
		self::DEPRECATED => array(
			self::REINITIALIZED
		),
		self::REVIEWED => array(
			self::REINITIALIZED,
			self::REJECTED,
			self::DEPRECATED,
			self::PUSHED
		),
		self::PUSHED => array()
	);

	/**
	 * The time this record was created.
	 *
	 * @var DateTime
	 */
	protected $created;
	
	/**
	 * The state.
	 *
	 * @var string
	 * @validate NotEmpty
	 */
	protected $state = self::FRESH;

	/**
	 * The user that initiated this state change.
	 *
	 * @var Tx_Contentstage_Domain_Model_BackendUser
	 * @lazy
	 */
	protected $user;

	/**
	 * Returns the created
	 *
	 * @return DateTime $created
	 */
	public function getCreated() {
		return $this->created;
	}

	/**
	 * Sets the created
	 *
	 * @param DateTime $created
	 * @return void
	 */
	public function setCreated($created) {
		$this->created = $created;
	}

	/**
	 * Returns the state
	 *
	 * @return string $state
	 */
	public function getState() {
		return $this->state;
	}

	/**
	 * Sets the state
	 *
	 * @param string $state
	 * @return void
	 */
	public function setState($state) {
		$this->state = $state;
	}

	/**
	 * Returns the user
	 *
	 * @return Tx_Contentstage_Domain_Model_BackendUser $user
	 */
	public function getUser() {
		return $this->user;
	}

	/**
	 * Sets the user
	 *
	 * @param Tx_Contentstage_Domain_Model_BackendUser $user
	 * @return void
	 */
	public function setUser(Tx_Contentstage_Domain_Model_BackendUser $user = null) {
		$this->user = $user;
	}

	/**
	 * Returns the next possible stats in an array.
	 *
	 * @return array The following states.
	 */
	public function getPossibleNextStates() {
		if (!is_string($this->getState()) || !isset(self::$next[$this->getState()])) {
			$this->setState(self::FRESH);
		}
		
		return self::$next[$this->getState()];
	}
}

