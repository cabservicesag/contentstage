<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

$TCA['tx_contentstage_domain_model_state'] = array(
	'ctrl' => $TCA['tx_contentstage_domain_model_state']['ctrl'],
	'interface' => array(
		'showRecordFieldList' => 'sys_language_uid, l10n_parent, l10n_diffsource, hidden, state, user',
	),
	'types' => array(
		'1' => array('showitem' => 'sys_language_uid;;;;1-1-1, l10n_parent, l10n_diffsource, hidden;;1, state, user,--div--;LLL:EXT:cms/locallang_ttc.xml:tabs.access,starttime, endtime'),
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
				'foreign_table' => 'tx_contentstage_domain_model_state',
				'foreign_table_where' => 'AND tx_contentstage_domain_model_state.pid=###CURRENT_PID### AND tx_contentstage_domain_model_state.sys_language_uid IN (-1,0)',
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
		'state' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:contentstage/Resources/Private/Language/locallang_db.xml:tx_contentstage_domain_model_state.state',
			'config' => array(
				'type' => 'select',
				'items' => array(
					array('LLL:EXT:contentstage/Resources/Private/Language/locallang_db.xml:tx_contentstage_domain_model_state.state.fresh', 'fresh'),
					array('LLL:EXT:contentstage/Resources/Private/Language/locallang_db.xml:tx_contentstage_domain_model_state.state.reinitialized', 'reinitialized'),
					array('LLL:EXT:contentstage/Resources/Private/Language/locallang_db.xml:tx_contentstage_domain_model_state.state.partial', 'partial'),
					array('LLL:EXT:contentstage/Resources/Private/Language/locallang_db.xml:tx_contentstage_domain_model_state.state.reviewed', 'reviewed'),
					array('LLL:EXT:contentstage/Resources/Private/Language/locallang_db.xml:tx_contentstage_domain_model_state.state.pushed', 'pushed'),
					array('LLL:EXT:contentstage/Resources/Private/Language/locallang_db.xml:tx_contentstage_domain_model_state.state.rejected', 'rejected'),
					array('LLL:EXT:contentstage/Resources/Private/Language/locallang_db.xml:tx_contentstage_domain_model_state.state.deprecated', 'deprecated'),
				),
				'size' => 1,
				'maxitems' => 1,
				'eval' => 'required'
			),
		),
		'user' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:contentstage/Resources/Private/Language/locallang_db.xml:tx_contentstage_domain_model_state.user',
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
		'review' => array(
			'config' => array(
				'type' => 'passthrough',
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
	),
);

?>