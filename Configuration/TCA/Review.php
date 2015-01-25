<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

$TCA['tx_contentstage_domain_model_review'] = array(
	'ctrl' => $TCA['tx_contentstage_domain_model_review']['ctrl'],
	'interface' => array(
		'showRecordFieldList' => 'sys_language_uid, l10n_parent, l10n_diffsource, hidden, crdate, pid, levels, required, debug, reviewed, cruser_id, changes, state, auto_push',
	),
	'types' => array(
		'1' => array('showitem' => 'sys_language_uid;;;;1-1-1, l10n_parent, l10n_diffsource, hidden;;1, crdate, pid, levels, auto_push, required, debug, reviewed, cruser_id, changes, state,--div--;LLL:EXT:cms/locallang_ttc.xml:tabs.access,starttime, endtime'),
	),
	'palettes' => array(
		'1' => array('showitem' => ''),
	),
	'columns' => array(
		'sys_language_uid' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.language',
			'config' => array(
				'type' => 'select',
				'foreign_table' => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'items' => array(
					array('LLL:EXT:lang/locallang_general.xml:LGL.allLanguages', -1),
					array('LLL:EXT:lang/locallang_general.xml:LGL.default_value', 0)
				),
			),
		),
		'l10n_parent' => array(
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.l18n_parent',
			'config' => array(
				'type' => 'select',
				'items' => array(
					array('', 0),
				),
				'foreign_table' => 'tx_contentstage_domain_model_review',
				'foreign_table_where' => 'AND tx_contentstage_domain_model_review.pid=###CURRENT_PID### AND tx_contentstage_domain_model_review.sys_language_uid IN (-1,0)',
			),
		),
		'l10n_diffsource' => array(
			'config' => array(
				'type' => 'passthrough',
			),
		),
		't3ver_label' => array(
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.versionLabel',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'max' => 255,
			)
		),
		'hidden' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config' => array(
				'type' => 'check',
			),
		),
		'starttime' => array(
			'exclude' => 1,
			'l10n_mode' => 'mergeIfNotBlank',
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.starttime',
			'config' => array(
				'type' => 'input',
				'size' => 13,
				'max' => 20,
				'eval' => 'datetime',
				'checkbox' => 0,
				'default' => 0,
				'range' => array(
					'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y'))
				),
			),
		),
		'endtime' => array(
			'exclude' => 1,
			'l10n_mode' => 'mergeIfNotBlank',
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.endtime',
			'config' => array(
				'type' => 'input',
				'size' => 13,
				'max' => 20,
				'eval' => 'datetime',
				'checkbox' => 0,
				'default' => 0,
				'range' => array(
					'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y'))
				),
			),
		),
		'crdate' => array(
			'exclude' => 0,
			'readOnly' => true,
			'label' => 'LLL:EXT:contentstage/Resources/Private/Language/locallang_db.xml:tx_contentstage_domain_model_review.created',
			'config' => array(
				'type' => 'input',
				'size' => 10,
				'eval' => 'datetime,required',
				'checkbox' => 1,
				'default' => time()
			),
		),
		'pid' => array(
			'exclude' => 0,
			'readOnly' => true,
			'label' => 'LLL:EXT:contentstage/Resources/Private/Language/locallang_db.xml:tx_contentstage_domain_model_review.page',
			'config' => array(
				'type' => 'input',
				'size' => 4,
				'eval' => 'int,required'
			),
		),
		'levels' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:contentstage/Resources/Private/Language/locallang_db.xml:tx_contentstage_domain_model_review.levels',
			'config' => array(
				'type' => 'input',
				'size' => 4,
				'eval' => 'int'
			),
		),
		'required' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:contentstage/Resources/Private/Language/locallang_db.xml:tx_contentstage_domain_model_review.required',
			'config' => array(
				'type' => 'input',
				'size' => 4,
				'eval' => 'int,required'
			),
		),
		'debug' => array(
			'exclude' => 1,
			'readOnly' => true,
			'label' => 'LLL:EXT:contentstage/Resources/Private/Language/locallang_db.xml:tx_contentstage_domain_model_review.debug',
			'config' => array(
				'type' => 'text',
				'cols' => 40,
				'rows' => 15,
				'eval' => 'trim'
			),
		),
		'reviewed' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:contentstage/Resources/Private/Language/locallang_db.xml:tx_contentstage_domain_model_review.reviewed',
			'config' => array(
				'type' => 'inline',
				'foreign_table' => 'tx_contentstage_domain_model_reviewed',
				'foreign_field' => 'review',
				'maxitems' => 9999,
				'appearance' => array(
					'collapseAll' => 0,
					'levelLinksPosition' => 'top',
					'showSynchronizationLink' => 1,
					'showPossibleLocalizationRecords' => 1,
					'showAllLocalizationLink' => 1
				),
			),
		),
		'cruser_id' => array(
			'exclude' => 0,
			'readOnly' => true,
			'label' => 'LLL:EXT:contentstage/Resources/Private/Language/locallang_db.xml:tx_contentstage_domain_model_review.creator',
			'config' => array(
				'foreign_table' => 'be_users',
				'minitems' => 1,
				'type' => 'select',
				'suppress_icons' => 1,
				'foreign_table_where' => 'ORDER BY be_users.username',
				'items' => array(
					array('', 0)
				),
			),
		),
		'changes' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:contentstage/Resources/Private/Language/locallang_db.xml:tx_contentstage_domain_model_review.changes',
			'config' => array(
				'type' => 'inline',
				'foreign_table' => 'tx_contentstage_domain_model_state',
				'foreign_field' => 'review',
				'foreign_sortby' => 'sorting',
				'maxitems' => 9999,
				'appearance' => array(
					'collapseAll' => 0,
					'levelLinksPosition' => 'top',
					'showSynchronizationLink' => 1,
					'showPossibleLocalizationRecords' => 1,
					'showAllLocalizationLink' => 1
				),
			),
		),
		'state' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:contentstage/Resources/Private/Language/locallang_db.xml:tx_contentstage_domain_model_review.state',
			'config' => array(
				'foreign_table' => 'tx_contentstage_domain_model_state',
				'type' => 'select',
				'suppress_icons' => 1,
				'foreign_table_where' => ' AND tx_contentstage_domain_model_state.review = ###THIS_UID### ORDER BY tx_contentstage_domain_model_state.crdate ASC',
				'items' => array(
					array('', 0)
				),
			),
		),
		'auto_push' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:contentstage/Resources/Private/Language/locallang_db.xml:tx_contentstage_domain_model_review.auto_push',
			'config' => array(
				'type' => 'check',
			),
		),
	),
);

?>