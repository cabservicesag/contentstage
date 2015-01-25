<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "contentstage".
 *
 * Auto generated 25-01-2015 10:42
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array (
	'title' => 'Content staging',
	'description' => 'An extension that allows to migrate partial content from one system to another.',
	'category' => 'module',
	'author' => 'Nils Blattner',
	'author_email' => 'nb@cabag.ch',
	'author_company' => 'cab services ag',
	'shy' => '',
	'priority' => '',
	'module' => '',
	'state' => 'stable',
	'internal' => '',
	'uploadfolder' => '0',
	'createDirs' => 'uploads/tx_contentstage/snapshots/',
	'modify_tables' => '',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'version' => '0.1.2',
	'constraints' => 
	array (
		'depends' => 
		array (
			'extbase' => '1.3',
			'fluid' => '1.3',
			'typo3' => '4.5-0.0.0',
		),
		'conflicts' => 
		array (
		),
		'suggests' => 
		array (
		),
	),
);

?>