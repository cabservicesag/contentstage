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
class Tx_Contentstage_Domain_Model_BackendUser extends Tx_Contentstage_Domain_Model_BaseModel {

	/**
	 * The username.
	 *
	 * @var string
	 * @validate NotEmpty
	 */
	protected $username;

	/**
	 * The password (possibly salted).
	 *
	 * @var string
	 * @validate NotEmpty
	 */
	protected $password;

	/**
	 * Whether or not the user is an administrator.
	 *
	 * @var boolean
	 */
	protected $admin = FALSE;

	/**
	 * The two character language representation.
	 *
	 * @var string
	 */
	protected $lang;

	/**
	 * The users email.
	 *
	 * @var string
	 */
	protected $email;

	/**
	 * A list of page uids that are accessible.
	 *
	 * @var string
	 */
	protected $dbMountpoints;

	/**
	 * Some inheritance options.
	 *
	 * @var integer
	 */
	protected $options;

	/**
	 * The user's real name.
	 *
	 * @var string
	 */
	protected $realName;

	/**
	 * User specific access modifications.
	 *
	 * @var string
	 */
	protected $userMods;

	/**
	 * List of allowed languages.
	 *
	 * @var string
	 */
	protected $allowedLanguages;

	/**
	 * The serialized user configuration.
	 *
	 * @var string
	 */
	protected $uc;

	/**
	 * Folder records that can be accessed.
	 *
	 * @var string
	 */
	protected $fileMountpoints;

	/**
	 * File operation permissions.
	 *
	 * @var integer
	 */
	protected $fileoperPerms;

	/**
	 * User is only allowed to log in from this domain.
	 *
	 * @var string
	 */
	protected $lockToDomain;

	/**
	 * Lift IP restriction for this user.
	 *
	 * @var boolean
	 */
	protected $disableIPlock = FALSE;

	/**
	 * UserTS configuration.
	 *
	 * @var string
	 */
	protected $tSconfig;

	/**
	 * Users last login.
	 *
	 * @var DateTime
	 */
	protected $lastlogin;

	/**
	 * The usergroups.
	 *
	 * @var Tx_Extbase_Persistence_ObjectStorage<Tx_Contentstage_Domain_Model_BackendUserGroup>
	 * @lazy
	 */
	protected $usergroup;

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
		$this->usergroup = new Tx_Extbase_Persistence_ObjectStorage();
	}

	/**
	 * Returns the username
	 *
	 * @return string $username
	 */
	public function getUsername() {
		return $this->username;
	}

	/**
	 * Sets the username
	 *
	 * @param string $username
	 * @return void
	 */
	public function setUsername($username) {
		$this->username = $username;
	}

	/**
	 * Returns the password
	 *
	 * @return string $password
	 */
	public function getPassword() {
		return $this->password;
	}

	/**
	 * Sets the password
	 *
	 * @param string $password
	 * @return void
	 */
	public function setPassword($password) {
		$this->password = $password;
	}

	/**
	 * Returns the admin
	 *
	 * @return boolean $admin
	 */
	public function getAdmin() {
		return $this->admin;
	}

	/**
	 * Sets the admin
	 *
	 * @param boolean $admin
	 * @return void
	 */
	public function setAdmin($admin) {
		$this->admin = $admin;
	}

	/**
	 * Returns the boolean state of admin
	 *
	 * @return boolean
	 */
	public function isAdmin() {
		return $this->getAdmin();
	}

	/**
	 * Returns the lang
	 *
	 * @return string $lang
	 */
	public function getLang() {
		return $this->lang;
	}

	/**
	 * Sets the lang
	 *
	 * @param string $lang
	 * @return void
	 */
	public function setLang($lang) {
		$this->lang = $lang;
	}

	/**
	 * Returns the email
	 *
	 * @return string $email
	 */
	public function getEmail() {
		return $this->email;
	}

	/**
	 * Sets the email
	 *
	 * @param string $email
	 * @return void
	 */
	public function setEmail($email) {
		$this->email = $email;
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
	 * Returns the dbMountpoints of this user an its groups
	 *
	 * @return array $dbMountpoints Array of integers.
	 */
	public function getDbMountpointsRecursive() {
		$mountpoints = $this->collectRecursiveDataCached('getDbMountpoints', 'getUsergroup', function($data){
			return t3lib_div::intExplode(',', $data, true);
		}, function($item){
			return $item;
		});
		
		foreach (t3lib_div::intExplode(',', $this->getDbMountpoints(), true) as $page) {
			$mountpoints[$page] = $page;
		}
		return $mountpoints;
	}

	/**
	 * Returns the options
	 *
	 * @return integer $options
	 */
	public function getOptions() {
		return $this->options;
	}

	/**
	 * Sets the options
	 *
	 * @param integer $options
	 * @return void
	 */
	public function setOptions($options) {
		$this->options = $options;
	}

	/**
	 * Returns the realName
	 *
	 * @return string $realName
	 */
	public function getRealName() {
		return $this->realName;
	}

	/**
	 * Sets the realName
	 *
	 * @param string $realName
	 * @return void
	 */
	public function setRealName($realName) {
		$this->realName = $realName;
	}

	/**
	 * Returns the userMods
	 *
	 * @return string $userMods
	 */
	public function getUserMods() {
		return $this->userMods;
	}

	/**
	 * Sets the userMods
	 *
	 * @param string $userMods
	 * @return void
	 */
	public function setUserMods($userMods) {
		$this->userMods = $userMods;
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
	 * Returns the uc
	 *
	 * @return string $uc
	 */
	public function getUc() {
		return $this->uc;
	}

	/**
	 * Sets the uc
	 *
	 * @param string $uc
	 * @return void
	 */
	public function setUc($uc) {
		$this->uc = $uc;
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
	 * Returns the disableIPlock
	 *
	 * @return boolean $disableIPlock
	 */
	public function getDisableIPlock() {
		return $this->disableIPlock;
	}

	/**
	 * Sets the disableIPlock
	 *
	 * @param boolean $disableIPlock
	 * @return void
	 */
	public function setDisableIPlock($disableIPlock) {
		$this->disableIPlock = $disableIPlock;
	}

	/**
	 * Returns the boolean state of disableIPlock
	 *
	 * @return boolean
	 */
	public function isDisableIPlock() {
		return $this->getDisableIPlock();
	}

	/**
	 * Returns the tSconfig
	 *
	 * @return string $tSconfig
	 */
	public function getTSconfig() {
		return $this->tSconfig;
	}

	/**
	 * Sets the tSconfig
	 *
	 * @param string $tSconfig
	 * @return void
	 */
	public function setTSconfig($tSconfig) {
		$this->tSconfig = $tSconfig;
	}

	/**
	 * Returns the lastlogin
	 *
	 * @return DateTime $lastlogin
	 */
	public function getLastlogin() {
		return $this->lastlogin;
	}

	/**
	 * Sets the lastlogin
	 *
	 * @param DateTime $lastlogin
	 * @return void
	 */
	public function setLastlogin($lastlogin) {
		$this->lastlogin = $lastlogin;
	}

	/**
	 * Adds a BackendUserGroup
	 *
	 * @param Tx_Contentstage_Domain_Model_BackendUserGroup $usergroup
	 * @return void
	 */
	public function addUsergroup(Tx_Contentstage_Domain_Model_BackendUserGroup $usergroup) {
		$this->usergroup->attach($usergroup);
	}

	/**
	 * Removes a BackendUserGroup
	 *
	 * @param Tx_Contentstage_Domain_Model_BackendUserGroup $usergroupToRemove The BackendUserGroup to be removed
	 * @return void
	 */
	public function removeUsergroup(Tx_Contentstage_Domain_Model_BackendUserGroup $usergroupToRemove) {
		$this->usergroup->detach($usergroupToRemove);
	}

	/**
	 * Returns the usergroup
	 *
	 * @return Tx_Extbase_Persistence_ObjectStorage<Tx_Contentstage_Domain_Model_BackendUserGroup> $usergroup
	 */
	public function getUsergroup() {
		return $this->usergroup;
	}

	/**
	 * Sets the usergroup
	 *
	 * @param Tx_Extbase_Persistence_ObjectStorage<Tx_Contentstage_Domain_Model_BackendUserGroup> $usergroup
	 * @return void
	 */
	public function setUsergroup(Tx_Extbase_Persistence_ObjectStorage $usergroup) {
		$this->usergroup = $usergroup;
	}
	
	/**
	 * Returns this groups and all included subgroups (cached).
	 *
	 * @return array<Tx_Contentstage_Domain_Model_BackendUserGroup> The contained groups.
	 */
	public function getGroups() {
		return $this->collectRecursiveDataCached('getGroup', 'getUsergroup', null, function($item) {
			return $item->getUid();
		});
	}

	/**
	 * Returns the "real name" or, if missing, the username.
	 *
	 * @return string
	 */
	public function getName() {
		return !empty($this->realName) ? $this->realName : $this->username;
	}

}
?>