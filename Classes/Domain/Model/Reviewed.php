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
class Tx_Contentstage_Domain_Model_Reviewed extends Tx_Extbase_DomainObject_AbstractEntity {

	/**
	 * The time this was reviewed.
	 *
	 * @var DateTime
	 */
	protected $reviewed;

	/**
	 * Whether or not the review was OK.
	 *
	 * @var boolean
	 */
	protected $ok = FALSE;

	/**
	 * The reviewer.
	 *
	 * @var Tx_Contentstage_Domain_Model_BackendUser
	 */
	protected $reviewer;

	/**
	 * The review.
	 *
	 * @var Tx_Contentstage_Domain_Model_Review
	 */
	protected $review;

	/**
	 * Returns the reviewed
	 *
	 * @return DateTime $reviewed
	 */
	public function getReviewed() {
		return $this->reviewed;
	}

	/**
	 * Sets the reviewed
	 *
	 * @param DateTime $reviewed
	 * @return void
	 */
	public function setReviewed($reviewed) {
		$this->reviewed = $reviewed;
	}

	/**
	 * Returns the ok
	 *
	 * @return boolean $ok
	 */
	public function getOk() {
		return $this->ok;
	}

	/**
	 * Sets the ok
	 *
	 * @param boolean $ok
	 * @return void
	 */
	public function setOk($ok) {
		$this->ok = $ok;
	}

	/**
	 * Returns the boolean state of ok
	 *
	 * @return boolean
	 */
	public function isOk() {
		return $this->getOk();
	}

	/**
	 * Returns the reviewer
	 *
	 * @return Tx_Contentstage_Domain_Model_BackendUser $reviewer
	 */
	public function getReviewer() {
		return $this->reviewer;
	}

	/**
	 * Sets the reviewer
	 *
	 * @param Tx_Contentstage_Domain_Model_BackendUser $reviewer
	 * @return void
	 */
	public function setReviewer(Tx_Contentstage_Domain_Model_BackendUser $reviewer) {
		$this->reviewer = $reviewer;
	}

	/**
	 * Returns the review
	 *
	 * @return Tx_Contentstage_Domain_Model_Review $review
	 */
	public function getReview() {
		return $this->review;
	}

	/**
	 * Sets the review
	 *
	 * @param Tx_Contentstage_Domain_Model_Review $review
	 * @return void
	 */
	public function setReview(Tx_Contentstage_Domain_Model_Review $review) {
		$this->review = $review;
	}
	
	/**
	 * Returns the state string, one of the options:
	 * - Tx_Contentstage_Domain_Model_State::FRESH => Not reviewed
	 * - Tx_Contentstage_Domain_Model_State::REVIEWED => User reviewed and OK'd the changes
	 * - Tx_Contentstage_Domain_Model_State::REJECTED => User reviewed and rejected the changes
	 *
	 * @return string The "state"
	 */
	public function getState() {
		if ($this->getReviewed() === null || $this->getReviewed()->format('U') == 0) {
			return Tx_Contentstage_Domain_Model_State::FRESH;
		}
		
		return $this->isOk() ? Tx_Contentstage_Domain_Model_State::REVIEWED : Tx_Contentstage_Domain_Model_State::REJECTED;
	}
	
	/**
	 * Reset the review to a fresh state.
	 *
	 * @return void
	 */
	public function reset() {
		$this->setOk(false);
		$this->setReviewed(null);
	}

}
?>