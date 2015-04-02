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
class Tx_Contentstage_Domain_Model_BackendUserGroup extends Tx_Contentstage_Domain_Model_BaseModel {

	/**
	 * The title.
	 *
	 * @var string
	 * @validate NotEmpty
	 */
	protected $title;

	/**
	 * Exclude fields that are to be shown.
	 *
	 * @var string
	 */
	protected $nonExcludeFields;

	/**
	 * Explicitly allow/deny field values.
	 *
	 * @var string
	 */
	protected $explicitAllowdeny;

	/**
	 * List of allowed languages.
	 *
	 * @var string
	 */
	protected $allowedLanguages;

	/**
	 * None-Core module options.
	 *
	 * @var string
	 */
	protected $customOptions;

	/**
	 * A list of page uids that are accessible.
	 *
	 * @var string
	 */
	protected $dbMountpoints;

	/**
	 * List of page types that are allowed.
	 *
	 * @var string
	 */
	protected $pagetypesSelect;

	/**
	 * List of tables that can be viewed.
	 *
	 * @var string
	 */
	protected $tablesSelect;

	/**
	 * List of tables that can be modified.
	 *
	 * @var string
	 */
	protected $tablesModify;

	/**
	 * Group specific access modifications.
	 *
	 * @var string
	 */
	protected $groupMods;

	/**
	 * Folder records that can be accessed.
	 *
	 * @var string
	 */
	protected $fileMountpoints;

	/**
	 * Include access lists.
	 *
	 * @var string
	 */
	protected $incAccessLists;

	/**
	 * Groups description.
	 *
	 * @var string
	 */
	protected $description;

	/**
	 * User is only allowed to log in from this domain.
	 *
	 * @var string
	 */
	protected $lockToDomain;

	/**
	 * If set, group cannot be selected.
	 *
	 * @var boolean
	 */
	protected $hideInLists = FALSE;

	/**
	 * File operation permissions.
	 *
	 * @var integer
	 */
	protected $fileoperPerms;

	/**
	 * Groups to extend.
	 *
	 * @var Tx_Extbase_Persistence_ObjectStorage<Tx_Contentstage_Domain_Model_BackendUserGroup>
	 * @lazy
	 */
	protected $subgroup;

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
		$this->subgroup = new Tx_Extbase_Persistence_ObjectStorage();
	}

	/**
	 * Returns the title
	 *
	 * @return string $title
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * Sets the title
	 *
	 * @param string $title
	 * @return void
	 */
	public function setTitle($title) {
		$this->title = $title;
	}

	/**
	 * Returns the nonExcludeFields
	 *
	 * @return string $nonExcludeFields
	 */
	public function getNonExcludeFields() {
		return $this->nonExcludeFields;
	}

	/**
	 * Sets the nonExcludeFields
	 *
	 * @param string $nonExcludeFields
	 * @return void
	 */
	public function setNonExcludeFields($nonExcludeFields) {
		$this->nonExcludeFields = $nonExcludeFields;
	}

	/**
	 * Returns the explicitAllowdeny
	 *
	 * @return string $explicitAllowdeny
	 */
	public function getExplicitAllowdeny() {
		return $this->explicitAllowdeny;
	}

	/**
	 * Sets the explicitAllowdeny
	 *
	 * @param string $explicitAllowdeny
	 * @return void
	 */
	public function setExplicitAllowdeny($explicitAllowdeny) {
		$this->explicitAllowdeny = $explicitAllowdeny;
	}

	/**
	 * Returns the allowedLanguages
	 *
	 * @return string $allowedLanguages
	 */
	public function getAllowedLanguages() {
		return $this->allowedLanguages;
	}

	/**
	 * Sets the allowedLanguages
	 *
	 * @param string $allowedLanguages
	 * @return void
	 */
	public function setAllowedLanguages($allowedLanguages) {
		$this->allowedLanguages = $allowedLanguages;
	}

	/**
	 * Returns the customOptions
	 *
	 * @return string $customOptions
	 */
	public function getCustomOptions() {
		return $this->customOptions;
	}

	/**
	 * Sets the customOptions
	 *
	 * @param string $customOptions
	 * @return void
	 */
	public function setCustomOptions($customOptions) {
		$this->customOptions = $customOptions;
	}

	/**
	 * Returns the dbMountpoints
	 *
	 * @return string $dbMountpoints
	 */
	public function getDbMountpoints() {
		return $this->dbMountpoints;
	}

	/**
	 * Sets the dbMountpoints
	 *
	 * @param string $dbMountpoints
	 * @return void
	 */
	public function setDbMountpoints($dbMountpoints) {
		$this->dbMountpoints = $dbMountpoints;
	}

	/**
	 * Returns the dbMountpoints recursively
	 *
	 * @return array $dbMountpoints Array of integers.
	 */
	public function getDbMountpointsRecursive() {
		return $this->collectRecursiveDataCached('getDbMountpoints', 'getSubgroup', function($data){
			return t3lib_div::intExplode(',', $data, true);
		}, function($item) {
			return $item;
		});
	}

	/**
	 * Returns the pagetypesSelect
	 *
	 * @return string $pagetypesSelect
	 */
	public function getPagetypesSelect() {
		return $this->pagetypesSelect;
	}

	/**
	 * Sets the pagetypesSelect
	 *
	 * @param string $pagetypesSelect
	 * @return void
	 */
	public function setPagetypesSelect($pagetypesSelect) {
		$this->pagetypesSelect = $pagetypesSelect;
	}

	/**
	 * Returns the tablesSelect
	 *
	 * @return string $tablesSelect
	 */
	public function getTablesSelect() {
		return $this->tablesSelect;
	}

	/**
	 * Sets the tablesSelect
	 *
	 * @param string $tablesSelect
	 * @return void
	 */
	public function setTablesSelect($tablesSelect) {
		$this->tablesSelect = $tablesSelect;
	}

	/**
	 * Returns the tablesModify
	 *
	 * @return string $tablesModify
	 */
	public function getTablesModify() {
		return $this->tablesModify;
	}

	/**
	 * Sets the tablesModify
	 *
	 * @param string $tablesModify
	 * @return void
	 */
	public function setTablesModify($tablesModify) {
		$this->tablesModify = $tablesModify;
	}

	/**
	 * Returns the groupMods
	 *
	 * @return string $groupMods
	 */
	public function getGroupMods() {
		return $this->groupMods;
	}

	/**
	 * Sets the groupMods
	 *
	 * @param string $groupMods
	 * @return void
	 */
	public function setGroupMods($groupMods) {
		$this->groupMods = $groupMods;
	}

	/**
	 * Returns the fileMountpoints
	 *
	 * @return string $fileMountpoints
	 */
	public function getFileMountpoints() {
		return $this->fileMountpoints;
	}

	/**
	 * Sets the fileMountpoints
	 *
	 * @param string $fileMountpoints
	 * @return void
	 */
	public function setFileMountpoints($fileMountpoints) {
		$this->fileMountpoints = $fileMountpoints;
	}

	/**
	 * Returns the incAccessLists
	 *
	 * @return string $incAccessLists
	 */
	public function getIncAccessLists() {
		return $this->incAccessLists;
	}

	/**
	 * Sets the incAccessLists
	 *
	 * @param string $incAccessLists
	 * @return void
	 */
	public function setIncAccessLists($incAccessLists) {
		$this->incAccessLists = $incAccessLists;
	}

	/**
	 * Returns the description
	 *
	 * @return string $description
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 * Sets the description
	 *
	 * @param string $description
	 * @return void
	 */
	public function setDescription($description) {
		$this->description = $description;
	}

	/**
	 * Returns the lockToDomain
	 *
	 * @return string $lockToDomain
	 */
	public function getLockToDomain() {
		return $this->lockToDomain;
	}

	/**
	 * Sets the lockToDomain
	 *
	 * @param string $lockToDomain
	 * @return void
	 */
	public function setLockToDomain($lockToDomain) {
		$this->lockToDomain = $lockToDomain;
	}

	/**
	 * Returns the hideInLists
	 *
	 * @return boolean $hideInLists
	 */
	public function getHideInLists() {
		return $this->hideInLists;
	}

	/**
	 * Sets the hideInLists
	 *
	 * @param boolean $hideInLists
	 * @return void
	 */
	public function setHideInLists($hideInLists) {
		$this->hideInLists = $hideInLists;
	}

	/**
	 * Returns the boolean state of hideInLists
	 *
	 * @return boolean
	 */
	public function isHideInLists() {
		return $this->getHideInLists();
	}

	/**
	 * Returns the fileoperPerms
	 *
	 * @return integer $fileoperPerms
	 */
	public function getFileoperPerms() {
		return $this->fileoperPerms;
	}

	/**
	 * Sets the fileoperPerms
	 *
	 * @param integer $fileoperPerms
	 * @return void
	 */
	public function setFileoperPerms($fileoperPerms) {
		$this->fileoperPerms = $fileoperPerms;
	}

	/**
	 * Adds a BackendUserGroup
	 *
	 * @param Tx_Contentstage_Domain_Model_BackendUserGroup $subgroup
	 * @return void
	 */
	public function addSubgroup(Tx_Contentstage_Domain_Model_BackendUserGroup $subgroup) {
		$this->subgroup->attach($subgroup);
	}

	/**
	 * Removes a BackendUserGroup
	 *
	 * @param Tx_Contentstage_Domain_Model_BackendUserGroup $subgroupToRemove The BackendUserGroup to be removed
	 * @return void
	 */
	public function removeSubgroup(Tx_Contentstage_Domain_Model_BackendUserGroup $subgroupToRemove) {
		$this->subgroup->detach($subgroupToRemove);
	}

	/**
	 * Returns the subgroup
	 *
	 * @return Tx_Extbase_Persistence_ObjectStorage<Tx_Contentstage_Domain_Model_BackendUserGroup> $subgroup
	 */
	public function getSubgroup() {
		return $this->subgroup;
	}

	/**
	 * Sets the subgroup
	 *
	 * @param Tx_Extbase_Persistence_ObjectStorage<Tx_Contentstage_Domain_Model_BackendUserGroup> $subgroup
	 * @return void
	 */
	public function setSubgroup(Tx_Extbase_Persistence_ObjectStorage $subgroup) {
		$this->subgroup = $subgroup;
	}
	
	/**
	 * Returns this group and all included subgroups (cached).
	 *
	 * @return array<Tx_Contentstage_Domain_Model_BackendUserGroup> The contained groups.
	 */
	public function getGroups() {
		return $this->collectRecursiveDataCached('getSubgroup', 'getSubgroup', null, function($item) {
			return $item->getUid();
		});
	}

}