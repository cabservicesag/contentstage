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
class Tx_Contentstage_Domain_Model_Review extends Tx_Extbase_DomainObject_AbstractEntity {

	/**
	 * The time this record was created.
	 *
	 * @var DateTime
	 */
	protected $created;

	/**
	 * The page to be reviewed.
	 *
	 * @var integer
	 * @validate NotEmpty
	 */
	protected $page;

	/**
	 * The page record to be reviewed.
	 *
	 * @var array
	 */
	protected $pageRecord = null;

	/**
	 * The levels to be reviewed. -1 infinite, 0 just this page, 1 this page and children.
	 *
	 * @var integer
	 */
	protected $levels;

	/**
	 * The amount of reviews required.
	 *
	 * @var integer
	 * @validate NotEmpty
	 */
	protected $required;

	/**
	 * Debugging information for old reviews.
	 *
	 * @var string
	 */
	protected $debug;

	/**
	 * The relation to the reviewers.
	 *
	 * @var Tx_Extbase_Persistence_ObjectStorage<Tx_Contentstage_Domain_Model_Reviewed>
	 * @lazy
	 */
	protected $reviewed;

	/**
	 * The reviewed record of the currently logged in user.
	 *
	 * @var Tx_Contentstage_Domain_Model_Reviewed
	 */
	protected $currentReviewed = null;

	/**
	 * The creator of this review record.
	 *
	 * @var Tx_Contentstage_Domain_Model_BackendUser
	 * @lazy
	 */
	protected $creator;

	/**
	 * The state changes.
	 *
	 * @var Tx_Extbase_Persistence_ObjectStorage<Tx_Contentstage_Domain_Model_State>
	 * @lazy
	 */
	protected $changes;

	/**
	 * The last active state.
	 *
	 * @var Tx_Contentstage_Domain_Model_State
	 */
	protected $state;

	/**
	 * Whether or not to automatically push the changes upon success.
	 *
	 * @var boolean
	 */
	protected $autoPush = false;

	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct() {
		//Do not remove the next line: It would break the functionality
		$this->initStorageObjects();
	}

	/**
	 * Initializes all Tx_Extbase_Persistence_ObjectStorage properties.
	 *
	 * @return void
	 */
	protected function initStorageObjects() {
		/**
		 * Do not modify this method!
		 * It will be rewritten on each save in the extension builder
		 * You may modify the constructor of this class instead
		 */
		$this->reviewed = new Tx_Extbase_Persistence_ObjectStorage();
		
		$this->changes = new Tx_Extbase_Persistence_ObjectStorage();
	}

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
	 * Returns the page
	 *
	 * @return integer $page
	 */
	public function getPage() {
		return $this->page;
	}

	/**
	 * Sets the page
	 *
	 * @param integer $page
	 * @return void
	 */
	public function setPage($page) {
		$this->page = $page;
	}

	/**
	 * Returns the page record
	 *
	 * @return array $pageRecord
	 */
	public function getPageRecord() {
		if ($this->pageRecord === null) {
			$this->pageRecord = array();
			$pageRecords = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
				'*',
				'pages',
				'uid = ' . intval($this->getPage()),
				'',
				'',
				1
			);
			if (count($pageRecords) === 1) {
				$this->pageRecord = current($pageRecords);
			}
		}
		return $this->pageRecord;
	}

	/**
	 * Returns the levels
	 *
	 * @return integer $levels
	 */
	public function getLevels() {
		return $this->levels;
	}

	/**
	 * Sets the levels
	 *
	 * @param integer $levels
	 * @return void
	 */
	public function setLevels($levels) {
		$this->levels = $levels;
	}

	/**
	 * Returns the required
	 *
	 * @return integer $required
	 */
	public function getRequired() {
		return $this->required;
	}

	/**
	 * Sets the required
	 *
	 * @param integer $required
	 * @return void
	 */
	public function setRequired($required) {
		$this->required = $required;
	}

	/**
	 * Returns the debug
	 *
	 * @return string $debug
	 */
	public function getDebug() {
		return $this->debug;
	}

	/**
	 * Sets the debug
	 *
	 * @param string $debug
	 * @return void
	 */
	public function setDebug($debug) {
		$this->debug = $debug;
	}

	/**
	 * Adds a Reviewed
	 *
	 * @param Tx_Contentstage_Domain_Model_Reviewed $reviewed
	 * @return void
	 */
	public function addReviewed(Tx_Contentstage_Domain_Model_Reviewed $reviewed) {
		$this->reviewed->attach($reviewed);
	}

	/**
	 * Removes a Reviewed
	 *
	 * @param Tx_Contentstage_Domain_Model_Reviewed $reviewedToRemove The Reviewed to be removed
	 * @return void
	 */
	public function removeReviewed(Tx_Contentstage_Domain_Model_Reviewed $reviewedToRemove) {
		$this->reviewed->detach($reviewedToRemove);
	}

	/**
	 * Returns the reviewed
	 *
	 * @return Tx_Extbase_Persistence_ObjectStorage<Tx_Contentstage_Domain_Model_Reviewed> $reviewed
	 */
	public function getReviewed() {
		return $this->reviewed;
	}

	/**
	 * Sets the reviewed
	 *
	 * @param Tx_Extbase_Persistence_ObjectStorage<Tx_Contentstage_Domain_Model_Reviewed> $reviewed
	 * @return void
	 */
	public function setReviewed(Tx_Extbase_Persistence_ObjectStorage $reviewed) {
		$this->reviewed = $reviewed;
	}
	
	/**
	 * Checks if the active backend user is part of the reviewers and returns the corresponding Reviewed entry.
	 *
	 * @param Tx_Contentstage_Domain_Model_BackendUser $backendUser Optional backend user to look for instead.
	 * @return Tx_Contentstage_Domain_Model_Reviewed The reviewed record or null.
	 */
	public function getActiveReviewed(Tx_Contentstage_Domain_Model_BackendUser $backendUser = null) {
		$uid = $backendUser === null ? intval($GLOBALS['BE_USER']->user['uid']) : $backendUser->getUid();
		$found = null;
		foreach ($this->getReviewed() as $reviewed) {
			if ($uid === $reviewed->getReviewer()->getUid()) {
				$found = $reviewed;
			}
		}
		return $found;
	}

	/**
	 * Returns the recipients
	 *
	 * @param boolean $includeActiveUser Returns the email/name for the active user aswell.
	 * @return array The recipients for mails.
	 */
	public function getRecipients($includeActiveUser = false) {
		$recipients = array();
		
		foreach ($this->getReviewed() as $reviewed) {
			if ($reviewed->getReviewer() !== null && ($includeActiveUser || $reviewed->getReviewer()->getUid() !== intval($GLOBALS['BE_USER']->user['uid']))) {
				$recipients[$reviewed->getReviewer()->getEmail()] = $reviewed->getReviewer()->getName();
			}
		}
		
		return $recipients;
	}
	
	/**
	 * Finds the reviewed record of the currently logged in be_user (if any).
	 *
	 * @return Tx_Contentstage_Domain_Model_Reviewed
	 */
	public function getCurrentReviewed() {
		if ($this->currentReviewed === null && is_object($GLOBALS['BE_USER']) && is_array($GLOBALS['BE_USER']->user) && intval($GLOBALS['BE_USER']->user['uid']) > 0) {
			foreach ($this->getReviewed() as $reviewed) {
				if ($reviewed->getReviewer() !== null && intval($GLOBALS['BE_USER']->user['uid']) === $reviewed->getReviewer()->getUid()) {
					$this->currentReviewed = $reviewed;
					break;
				}
			}
		}
		return $this->currentReviewed;
	}
	
	/**
	 * Returns an array with items from 1 to {required}
	 */
	public function getReviewerIndices() {
		return range(1, $this->getRequired());
	}

	/**
	 * Returns the creator
	 *
	 * @return Tx_Contentstage_Domain_Model_BackendUser $creator
	 */
	public function getCreator() {
		return $this->creator;
	}

	/**
	 * Sets the creator
	 *
	 * @param Tx_Contentstage_Domain_Model_BackendUser $creator
	 * @return void
	 */
	public function setCreator(Tx_Contentstage_Domain_Model_BackendUser $creator) {
		$this->creator = $creator;
	}

	/**
	 * Adds a State
	 *
	 * @param Tx_Contentstage_Domain_Model_State $change
	 * @return void
	 */
	public function addChange(Tx_Contentstage_Domain_Model_State $change) {
		$this->changes->attach($change);
		$this->setState($change);
	}
	
	/**
	 * Adds a state by it's string.
	 *
	 * @param Tx_Contentstage_Domain_Model_BackendUser $editor The user prompting the change.
	 * @param string $stateString The state string.
	 * @return void
	 */
	public function addChangeString(Tx_Contentstage_Domain_Model_BackendUser $editor = null, $stateString = Tx_Contentstage_Domain_Model_State::FRESH) {
		$objectManager = t3lib_div::makeInstance('Tx_Extbase_Object_ObjectManager');
		$stateRepository = $objectManager->get('Tx_Contentstage_Domain_Repository_StateRepository');
		$reviewRepository = $objectManager->get('Tx_Contentstage_Domain_Repository_ReviewRepository');
		$state = $objectManager->create('Tx_Contentstage_Domain_Model_State');
		$state->setUser($editor);
		$state->setState($stateString);
		$stateRepository->add($state);
		$persistenceManager = $objectManager->get('Tx_Extbase_Persistence_ManagerInterface');
		$persistenceManager->persistAll();
		$this->addChange($state);
		$reviewRepository->update($this);
	}

	/**
	 * Removes a State
	 *
	 * @param Tx_Contentstage_Domain_Model_State $changeToRemove The State to be removed
	 * @return void
	 */
	public function removeChange(Tx_Contentstage_Domain_Model_State $changeToRemove) {
		$this->changes->detach($changeToRemove);
	}

	/**
	 * Returns the changes
	 *
	 * @return Tx_Extbase_Persistence_ObjectStorage<Tx_Contentstage_Domain_Model_State> $changes
	 */
	public function getChanges() {
		return $this->changes;
	}

	/**
	 * Sets the changes
	 *
	 * @param Tx_Extbase_Persistence_ObjectStorage<Tx_Contentstage_Domain_Model_State> $changes
	 * @return void
	 */
	public function setChanges(Tx_Extbase_Persistence_ObjectStorage $changes) {
		$this->changes = $changes;
	}

	/**
	 * Returns the state
	 *
	 * @return Tx_Contentstage_Domain_Model_State $state
	 */
	public function getState() {
		if ($this->getUid() === null) {
			$objectManager = t3lib_div::makeInstance('Tx_Extbase_Object_ObjectManager');
			return $objectManager->create('Tx_Contentstage_Domain_Model_State');
		}
		if ($this->state === null) {
			$highestTstamp = 0;
			foreach ($this->getChanges() as $state) {
				if ($state->getCreated() !== null && $state->getCreated()->format('U') > $highestTstamp) {
					$this->state = $state;
					$highestTstamp = $state->getCreated()->format('U');
				}
			}
		}
		if ($this->state === null) {
			$this->addChangeString();
		}
		return $this->state;
	}

	/**
	 * Sets the state
	 *
	 * @param Tx_Contentstage_Domain_Model_State $state
	 * @return void
	 */
	public function setState(Tx_Contentstage_Domain_Model_State $state) {
		$this->state = $state;
	}

	/**
	 * Returns whether or not to automatically push the changes upon success.
	 *
	 * @return boolean $autoPush
	 */
	public function getAutoPush() {
		return $this->autoPush;
	}

	/**
	 * Sets whether or not to automatically push the changes upon success.
	 *
	 * @param boolean $autoPush
	 * @return void
	 */
	public function setAutoPush($autoPush) {
		$this->autoPush = $autoPush;
	}
	
	/**
	 * Calculates if a state change is needed.
	 *
	 * @param Tx_Contentstage_Domain_Model_BackendUser $editor The user prompting the change.
	 * @param boolean $reinitialize Whether or not to reinitialize the review.
	 * @param int $maximumTstamp The newest tstamp found for this page tree.
	 * @return boolean Whether or not the state was changed.
	 */
	public function calculateState(Tx_Contentstage_Domain_Model_BackendUser $editor = null, $reinitialize = false, $maximumTstamp = false) {
		if ($this->getUid() === null) {
			return false;
		}
		
		$changed = false;
		
		if ($this->getState() === null) {
			$this->addChangeString($editor);
		}
		
		$reviewedByState = array(
			Tx_Contentstage_Domain_Model_State::REVIEWED => 0,
			Tx_Contentstage_Domain_Model_State::REJECTED => 0,
			Tx_Contentstage_Domain_Model_State::FRESH => 0
		);
		foreach ($this->getReviewed() as $reviewed) {
			$reviewedByState[$reviewed->getState()]++;
		}
		$accepted = $reviewedByState[Tx_Contentstage_Domain_Model_State::REVIEWED];
		$rejected = $reviewedByState[Tx_Contentstage_Domain_Model_State::REJECTED];
		
		$deprecated = false;
		if ($this->getCreated() !== null && $maximumTstamp !== false) {
			$deprecated = $this->getCreated()->format('U') < $maximumTstamp;
		}
		
		$possibleState = false;
		
		if ($reinitialize) {
			$possibleState = Tx_Contentstage_Domain_Model_State::REINITIALIZED;
		} else if ($deprecated) {
			$possibleState = Tx_Contentstage_Domain_Model_State::DEPRECATED;
		} else if ($rejected > 0) {
			$possibleState = Tx_Contentstage_Domain_Model_State::REJECTED;
		} else if ($accepted >= $this->getRequired()) {
			$possibleState = Tx_Contentstage_Domain_Model_State::REVIEWED;
		} else if ($accepted > 0) {
			$possibleState = Tx_Contentstage_Domain_Model_State::PARTIAL;
		}
		
		/* print_r(array(
			'$reinitialize' => $reinitialize,
			'$reviewedByState' => $reviewedByState,
			'$accepted' => $accepted,
			'$rejected' => $rejected,
			'$deprecated' => $deprecated,
			'$possibleState' => $possibleState,
			'$this->getRequired()' => $this->getRequired(),
			'$this->getAutoPush()' => $this->getAutoPush(),
			'$this->getState()->getPossibleNextStates()' => $this->getState()->getPossibleNextStates(),
		));die(); */
		
		$found = false;
		foreach ($this->getState()->getPossibleNextStates() as $next) {
			if ($next === $possibleState) {
				$found = true;
				break;
			}
		}
		
		if ($found) {
			$this->addChangeString($editor, $possibleState);
			$changed = true;
		}
		
		return $changed;
	}
	
	/**
	 * Returns whether this review is currently reviewable.
	 * 
	 * @return boolean Whether this review is currently reviewable.
	 */
	public function isReviewable() {
		$result = $this->getUid() > 0;
		switch ($this->getState()->getState()) {
			case Tx_Contentstage_Domain_Model_State::DEPRECATED:
			case Tx_Contentstage_Domain_Model_State::REVIEWED:
			case Tx_Contentstage_Domain_Model_State::PUSHED:
				$result = false;
		}
		return $result;
	}
	
	/**
	 * Returns whether this review is currently pushable.
	 * 
	 * @return boolean Whether this review is currently pushable.
	 */
	public function isPushable() {
		return $this->getState()->getState() === Tx_Contentstage_Domain_Model_State::REVIEWED;
	}

}
?>