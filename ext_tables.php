<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

if (TYPO3_MODE === 'BE') {

	/**
	 * Registers a Backend Module
	 */
	Tx_Extbase_Utility_Extension::registerModule(
		$_EXTKEY,
		'web',	 // Make module a submodule of 'web'
		'stage',	// Submodule key
		'',						// Position
		array(
			'Content' => 'compare, view, push',
			'Review' => 'list, reviewed, reinitialize, new, create, edit, update, delete',
		),
		array(
			'access' => 'user,group',
			'icon'   => 'EXT:' . $_EXTKEY . '/ext_icon.gif',
			'labels' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang_stage.xml',
			'navigationComponentId' => 'typo3-pagetree',
		)
	);

	/**
	 * Registers a Backend Module
	 */
	Tx_Extbase_Utility_Extension::registerModule(
		$_EXTKEY,
		'web',	 // Make module a submodule of 'web'
		'stageSnapshots',	// Submodule key
		'',						// Position
		array(
			'Snapshot' => 'list, create, delete, revert',
		),
		array(
			'access' => 'user,group',
			'icon'   => 'EXT:' . $_EXTKEY . '/ext_icon.gif',
			'labels' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang_stageSnapshots.xml',
			'navigationComponentId' => 'typo3-pagetree',
		)
	);

	/**
	 * Registers a Backend Module
	 */
	Tx_Extbase_Utility_Extension::registerModule(
		$_EXTKEY,
		'web',	 // Make module a submodule of 'web'
		'stageInit',	// Submodule key
		'',						// Position
		array(
			'Initialize' => 'show, doInitialize',
		),
		array(
			'access' => 'user,group',
			'icon'   => 'EXT:' . $_EXTKEY . '/ext_icon.gif',
			'labels' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang_stageInit.xml',
			'navigationComponentId' => 'typo3-pagetree',
		)
	);
	
	
	/**
	 * Registers a Backend Module
	 */
	Tx_Extbase_Utility_Extension::registerModule(
		$_EXTKEY,
		'web',	 // Make module a submodule of 'web'
		'stageFederated',	// Submodule key
		'',						// Position
		array(
			'Federated' => 'show, doFederated',
		),
		array(
			'access' => 'user,group',
			'icon'   => 'EXT:' . $_EXTKEY . '/ext_icon.gif',
			'labels' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang_stageFederated.xml',
			'navigationComponentId' => 'typo3-pagetree',
		)
	);
	
	/**
	 * History/undo xclass to show publish of reviews.
	 */
	//require_once(t3lib_extMgm::extPath('contentstage', 'Classes/Xclass/RecordHistory.php'));
}

t3lib_extMgm::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'Content staging');

t3lib_extMgm::addLLrefForTCAdescr('tx_contentstage_domain_model_review', 'EXT:contentstage/Resources/Private/Language/locallang_csh_tx_contentstage_domain_model_review.xml');
t3lib_extMgm::allowTableOnStandardPages('tx_contentstage_domain_model_review');
$TCA['tx_contentstage_domain_model_review'] = array(
	'ctrl' => array(
		'hideTable' => 0,
		'title'	=> 'LLL:EXT:contentstage/Resources/Private/Language/locallang_db.xml:tx_contentstage_domain_model_review',
		'label' => 'crdate',
		'label_alt' => 'pid,levels',
		'label_alt_force' => 1,
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'dividers2tabs' => TRUE,
		'sortby' => 'crdate',
		'versioningWS' => 2,
		'versioning_followPages' => TRUE,
		'origUid' => 't3_origuid',
		'languageField' => 'sys_language_uid',
		'transOrigPointerField' => 'l10n_parent',
		'transOrigDiffSourceField' => 'l10n_diffsource',
		'delete' => 'deleted',
		'enablecolumns' => array(
			'disabled' => 'hidden',
			'starttime' => 'starttime',
			'endtime' => 'endtime',
		),
		'searchFields' => 'created,page,levels,required,debug,reviewed,creator,changes,state,',
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY) . 'Configuration/TCA/Review.php',
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY) . 'Resources/Public/Icons/tx_contentstage_domain_model_review.gif'
	),
);

t3lib_extMgm::addLLrefForTCAdescr('tx_contentstage_domain_model_reviewed', 'EXT:contentstage/Resources/Private/Language/locallang_csh_tx_contentstage_domain_model_reviewed.xml');
t3lib_extMgm::allowTableOnStandardPages('tx_contentstage_domain_model_reviewed');
$TCA['tx_contentstage_domain_model_reviewed'] = array(
	'ctrl' => array(
		'hideTable' => 0,
		'title'	=> 'LLL:EXT:contentstage/Resources/Private/Language/locallang_db.xml:tx_contentstage_domain_model_reviewed',
		'label' => 'reviewed',
		'label_alt' => 'reviewer,ok',
		'label_alt_force' => 1,
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'dividers2tabs' => TRUE,
		'sortby' => 'sorting',
		'versioningWS' => 2,
		'versioning_followPages' => TRUE,
		'origUid' => 't3_origuid',
		'languageField' => 'sys_language_uid',
		'transOrigPointerField' => 'l10n_parent',
		'transOrigDiffSourceField' => 'l10n_diffsource',
		'delete' => 'deleted',
		'enablecolumns' => array(
			'disabled' => 'hidden',
			'starttime' => 'starttime',
			'endtime' => 'endtime',
		),
		'searchFields' => 'reviewed,ok,reviewer,',
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY) . 'Configuration/TCA/Reviewed.php',
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY) . 'Resources/Public/Icons/tx_contentstage_domain_model_reviewed.gif'
	),
);

t3lib_extMgm::addLLrefForTCAdescr('tx_contentstage_domain_model_state', 'EXT:contentstage/Resources/Private/Language/locallang_csh_tx_contentstage_domain_model_state.xml');
t3lib_extMgm::allowTableOnStandardPages('tx_contentstage_domain_model_state');
$TCA['tx_contentstage_domain_model_state'] = array(
	'ctrl' => array(
		'hideTable' => 0,
		'title'	=> 'LLL:EXT:contentstage/Resources/Private/Language/locallang_db.xml:tx_contentstage_domain_model_state',
		'label' => 'state',
		'label_alt' => 'crdate',
		'label_alt_force' => 1,
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'dividers2tabs' => TRUE,
		'sortby' => 'crdate',
		'versioningWS' => 2,
		'versioning_followPages' => TRUE,
		'origUid' => 't3_origuid',
		'languageField' => 'sys_language_uid',
		'transOrigPointerField' => 'l10n_parent',
		'transOrigDiffSourceField' => 'l10n_diffsource',
		'delete' => 'deleted',
		'enablecolumns' => array(
			'disabled' => 'hidden',
			'starttime' => 'starttime',
			'endtime' => 'endtime',
		),
		'searchFields' => 'state,user,',
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY) . 'Configuration/TCA/State.php',
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY) . 'Resources/Public/Icons/tx_contentstage_domain_model_state.gif'
	),
);