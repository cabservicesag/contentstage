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
class Tx_Contentstage_Domain_Repository_ReviewRepository extends Tx_Extbase_Persistence_Repository {

	/**
	 * @var array
	 */
	protected $defaultOrderings = array(
		'created' => Tx_Extbase_Persistence_QueryInterface::ORDER_DESCENDING
	);

	/**
	 * Returns the currently active review record.
	 *
	 * @param int $page The page to use for the review record.
	 * @return Tx_Contentstage_Domain_Model_Review The active review.
	 */
	public function findActive($page) {
		$query = $this->createQuery();
		
		$query->matching(
				$query->logicalAnd(
					$query->equals('page', $page),
					$query->logicalNot($query->equals('state.state', Tx_Contentstage_Domain_Model_State::PUSHED))
				)
			)
			->setOrderings(array(
				'created' => Tx_Extbase_Persistence_QueryInterface::ORDER_DESCENDING
			))
			->setLimit(1);
		return $query->execute()->getFirst();
	}

	/**
	 * Returns all reviews that are still active.
	 *
	 * @return array The active reviews.
	 */
	public function findAllActive() {
		$query = $this->createQuery();
		
		$query->matching(
				$query->logicalNot($query->equals('state.state', Tx_Contentstage_Domain_Model_State::PUSHED))
			);
		return $query->execute();
	}

	/**
	 * Returns the currently active review recordsin given pages.
	 *
	 * @param array $page The pages to get the review records from.
	 * @return array The active reviews.
	 */
	public function findActiveInPages($page) {
		$query = $this->createQuery();
		
		$query->matching(
				$query->logicalAnd(
					$query->equals('page', $page),
					$query->logicalNot($query->equals('state.state', Tx_Contentstage_Domain_Model_State::PUSHED))
				)
			);
		return $query->execute();
	}
}
?>