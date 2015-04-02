<?php
/**                                                                        *
 * This script belongs to the Extbase framework                           *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License as published by the *
 * Free Software Foundation, either version 3 of the License, or (at your *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser       *
 * General Public License for more details.                               *
 *                                                                        *
 * You should have received a copy of the GNU Lesser General Public       *
 * License along with the script.                                         *
 * If not, see http://www.gnu.org/licenses/lgpl.html                      *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */
/**
 * Converter which transforms strings to Tx_Contentstage_Domain_Model_Dbrecord.
 *
 * @api
 */
class Tx_Contentstage_Property_TypeConverter_DbrecordConverter extends \TYPO3\CMS\Extbase\Property\TypeConverter\AbstractTypeConverter {

	/**
	 * @var array<string>
	 */
	protected $sourceTypes = array('array');

	/**
	 * @var string
	 */
	protected $targetType = 'Tx_Extbase_Persistence_ObjectStorage<Tx_Contentstage_Domain_Model_Dbrecord>';

	/**
	 * @var integer
	 */
	protected $priority = 1;

	/**
	 * We can only convert empty strings to array or array to array.
	 *
	 * @param mixed $source
	 * @param string $targetType
	 * @return boolean
	 */
	public function canConvertFrom($source, $targetType) {
		return is_array($source) && count($source) > 0;
	}

	/**
	 * Convert from $source to $targetType, a noop if the source is an array.
	 * If it is an empty string it will be converted to an empty array.
	 *
	 * @param string|array $source
	 * @param string $targetType
	 * @param array $convertedChildProperties
	 * @param \TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationInterface $configuration
	 * @return array
	 * @api
	 */
	public function convertFrom($source, $targetType, array $convertedChildProperties = array(), \TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationInterface $configuration = NULL) {
		$subject = null;
		if ($this->canConvertFrom($source, $targetType)) {
			debug($convertedChildProperties,'convertedChildProperties');
			/** @var $subject Tx_Contentstage_Domain_Model_Dbrecord */
			$subject = $this->objectManager->get('Tx_Extbase_Persistence_ObjectStorage');
			$child = null;
			debug($source);
			foreach($source as $value) {
				$child = $this->objectManager->get('Tx_Contentstage_Domain_Model_Dbrecord');
				list($tablename, $recorduid) = explode('|', $value);
				$child->setTablename($tablename);
				$child->setRecorduid($recorduid);
				$subject->attach($child);
			}
		}

		return $subject;
	}
	
	/**
	 * Returns an empty list of sub property names
	 *
	 * @param mixed $source
	 * @return array
	 * @api
	 */
	public function getSourceChildPropertiesToBeConverted($source) {
		return array('dbrecord');
	}
}
