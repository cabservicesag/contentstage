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
class Tx_Contentstage_Controller_ReviewController extends Tx_Contentstage_Controller_BaseController {

	/**
	 * The reviewed repository.
	 *
	 * @var Tx_Contentstage_Domain_Repository_ReviewedRepository
	 */
	protected $reviewedRepository;

	/**
	 * @var Tx_Extbase_Persistence_ManagerInterface
	 */
	protected $persistenceManager;

	/**
	 * Injects the reviewed repository.
	 *
	 * @param Tx_Contentstage_Domain_Repository_ReviewedRepository $reviewedRepository
	 * @return void
	 */
	public function injectReviewedRepository(Tx_Contentstage_Domain_Repository_ReviewedRepository $reviewedRepository) {
		$this->reviewedRepository = $reviewedRepository;
	}

	/**
	 * @param Tx_Extbase_Persistence_ManagerInterface $persistenceManager
	 * @return void
	 */
	public function injectPersistenceManager(Tx_Extbase_Persistence_ManagerInterface $persistenceManager) {
		$this->persistenceManager = $persistenceManager;
	}

	/**
	 * action reviewed
	 *
	 * @param Tx_Contentstage_Domain_Model_Review $review
	 * @param string $submitButton The submit text.
	 * @param boolean $usedByCreate Whether this function is called from createAction.
	 * @return void
	 */
	public function reviewedAction(Tx_Contentstage_Domain_Model_Review $review, $submitButton, $usedByCreate = false) {
		$found = $review->getActiveReviewed($this->activeBackendUser);
		
		if ($found === null) {
			$this->log->log($this->translate('info.review.notAssigned'), Tx_CabagExtbase_Utility_Logging::WARNING);
		} else {
			$ok = $submitButton === $this->translate('review.submit.ok');
			$found->setOk($ok);
			$found->setReviewed(new DateTime());
			$this->reviewedRepository->update($found);
			$changed = $review->calculateState($this->activeBackendUser);
			$this->log->log($this->translate('info.review.' . ($ok ? 'accepted' : 'rejected')), Tx_CabagExtbase_Utility_Logging::OK, Tx_Extbase_Reflection_ObjectAccess::getGettableProperties($review));
			if ($changed) {
				$this->sendReviewMailAndLog(($usedByCreate ? 'created' : 'changed'), $review);
				
				if ($review->getAutoPush() && $review->getState()->getState() === Tx_Contentstage_Domain_Model_State::REVIEWED) {
					$this->redirect('push', 'Content');
				}
			}
		}
		
		$this->redirect('compare', 'Content');
	}

	/**
	 * action reinitialize
	 *
	 * @param Tx_Contentstage_Domain_Model_Review $review
	 * @return void
	 */
	public function reinitializeAction(Tx_Contentstage_Domain_Model_Review $review) {
		$this->checkPermission();
		$changed = $review->calculateState($this->activeBackendUser, true);
		$review->setCreated(new DateTime());
		$review->setCreator($this->activeBackendUser);
		$this->reviewRepository->update($review);
		
		foreach ($review->getReviewed() as $reviewed) {
			$reviewed->reset();
			$this->reviewedRepository->update($reviewed);
		}
		
		$this->log->log($this->translate('info.review.reinitialized'), Tx_CabagExtbase_Utility_Logging::OK, Tx_Extbase_Reflection_ObjectAccess::getGettableProperties($review));
		if ($changed) {
			$this->sendReviewMailAndLog('changed', $review);
		}
		
		$this->redirect('compare', 'Content');
	}

	/**
	 * action new
	 *
	 * @param Tx_Contentstage_Domain_Model_Review $newReview
	 * @dontvalidate $newReview
	 * @return void
	 */
	public function newAction(Tx_Contentstage_Domain_Model_Review $newReview = NULL) {
		$this->view->assign('newReview', $newReview);
	}

	/**
	 * action create
	 *
	 * @param Tx_Contentstage_Domain_Model_Review $newReview
	 * @param array $reviewers Array of be_user ids
	 * @return void
	 */
	public function createAction(Tx_Contentstage_Domain_Model_Review $review, array $reviewers = array()) {
		$this->checkPermission();
		if (!is_array($reviewers) || count($reviewers) != $this->review->getRequired()) {
			$this->flashMessageContainer->add($this->translate('review.create.error.badReviewers', array($this->review->getRequired(), count($reviewers))), '', t3lib_FlashMessage::ERROR);
			$this->redirect('compare', 'Content');
		}
		
		$reviewed = array();
		try {
			$this->mapReviewers($reviewers, $reviewed);
		} catch (Exception $e) {
			$this->log->log($this->translate('error.' . $e->getCode(), array($e->getMessage())) ?: $e->getMessage(), Tx_CabagExtbase_Utility_Logging::ERROR, $e->getTraceAsString());
			$this->redirect('compare', 'Content');
		}
		
		$review->setRequired($this->review->getRequired());
		$review->setCreated(new DateTime());
		$review->setCreator($this->activeBackendUser);
		$this->reviewRepository->add($review);
		$this->persistenceManager->persistAll();
		$review->addChangeString($this->activeBackendUser);
		
		foreach ($reviewed as $reviewedObject) {
			$review->addReviewed($reviewedObject);
		}
		
		$this->reviewRepository->update($review);
		$this->persistenceManager->persistAll();
		$this->log->log($this->translate('review.create.success'), Tx_CabagExtbase_Utility_Logging::OK);
		
		if ($this->reviewConfiguration['autoReviewIfSelf']) {
			$found = $review->getActiveReviewed($this->activeBackendUser);
			
			if ($found !== null) {
				// the action will redirect to compare action and thus prevent the initial mail sent and only sends a "changed" mail
				$this->reviewedAction($review, $this->translate('review.submit.ok'), true);
			}
		}
		
		$this->sendReviewMailAndLog('created', $review);
		
		$this->redirect('compare', 'Content');
	}

	/**
	 * action edit
	 *
	 * @param Tx_Contentstage_Domain_Model_Review $review
	 * @return void
	 */
	public function editAction(Tx_Contentstage_Domain_Model_Review $review) {
		$this->view->assign('review', $review);
	}

	/**
	 * action update
	 *
	 * @param Tx_Contentstage_Domain_Model_Review $review
	 * @param array $reviewers Array of be_user ids
	 * @return void
	 */
	public function updateAction(Tx_Contentstage_Domain_Model_Review $review, array $reviewers = array()) {
		$this->checkPermission();
		try {
			$this->mapReviewers($reviewers, iterator_to_array($review->getReviewed()));
		} catch (Exception $e) {
			$this->log->log($this->translate('error.' . $e->getCode(), array($e->getMessage())) ?: $e->getMessage(), Tx_CabagExtbase_Utility_Logging::ERROR);
			$this->redirect('compare', 'Content');
		}
		
		$this->reviewRepository->update($review);
		$this->persistenceManager->persistAll();
		$this->log->log($this->translate('review.update.success'), Tx_CabagExtbase_Utility_Logging::OK);
		
		if ($this->reviewConfiguration['autoReviewIfSelf']) {
			$found = $review->getActiveReviewed($this->activeBackendUser);
			
			if ($found !== null && $found->getState() === Tx_Contentstage_Domain_Model_State::FRESH) {
				// the action will redirect to compare action and thus prevent the initial mail sent and only sends a "changed" mail
				$this->reviewedAction($review, $this->translate('review.submit.ok'), true);
			}
		}
		
		$this->sendReviewMailAndLog('changed', $review);
		
		$this->redirect('compare', 'Content');
	}

	/**
	 * action delete
	 *
	 * @param Tx_Contentstage_Domain_Model_Review $review
	 * @return void
	 */
	public function deleteAction(Tx_Contentstage_Domain_Model_Review $review) {
		$this->reviewRepository->remove($review);
		$this->log->log($this->translate('review.deleted'), Tx_CabagExtbase_Utility_Logging::OK);
		$this->redirect('compare', 'Content');
	}
	
	/**
	 * Maps the given reviewer backend users to a (possible) given reviewed set. Also inserts/updates the reviewed entries.
	 *
	 * @param array $reviewers An array of backend user uids.
	 * @param array $reviewed An array or array like object (iterable!) of reviewed records. Will be populated if not enough entries.
	 * @return void
	 */
	protected function mapReviewers($reviewers, &$reviewed = array()) {
		$users = array();
		$newReviewed = array();
		reset($reviewed);
		
		foreach ($reviewers as $reviewer) {
			$user = $this->backendUserRepository->findByUid(intval($reviewer));
			if ($user === null) {
				throw new Exception('Non existing or deleted Backend-User given.', 1380023686);
			}
			if ($users[$user->getUid()]) {
				throw new Exception('Backend-User used twice.', 1380023687);
			}
			$users[$user->getUid()] = true;
			$reviewedObject = current($reviewed);
			next($reviewed);
			if (!is_object($reviewedObject) || !($reviewedObject instanceof Tx_Contentstage_Domain_Model_Reviewed)) {
				$reviewedObject = $this->objectManager->create('Tx_Contentstage_Domain_Model_Reviewed');
				$reviewedObject->setReviewer($user);
				$newReviewed[] = $reviewedObject;
				$this->reviewedRepository->add($reviewedObject);
			} else {
				if ($reviewedObject->getReviewer() === null || $reviewedObject->getUid() !== $user->getUid()) {
					$reviewedObject->reset();
				}
				$reviewedObject->setReviewer($user);
				$this->reviewedRepository->update($reviewedObject);
				$newReviewed[] = $reviewedObject;
			}
		}
		$reviewed = $newReviewed;
	}
	
	/**
	 * Checks if the edit/create permission is given, adds a message and redirects if necessary.
	 *
	 * @return void 
	 */
	protected function checkPermission() {
		if ($this->reviewConfiguration === null || empty($this->reviewConfiguration['editCreate'])) {
			$this->log->log($this->translate('info.review.noPermission'), Tx_CabagExtbase_Utility_Logging::WARNING);
			$this->redirect('compare', 'Content');
		}
	}
}
?>