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
	'uploadfolder' => 0,
	'createDirs' => 'uploads/tx_contentstage/snapshots/',
	'modify_tables' => '',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'version' => '0.1.7',
	'constraints' => 
	array (
		'depends' => 
		array (
			'extbase' => '1.3',
			'fluid' => '1.3',
			'typo3' => '4.5-0.0.0',
			'cabag_extbase' => '0.5.5',
		),
		'conflicts' => 
		array (
		),
		'suggests' => 
		array (
		),
	),
	'_md5_values_when_last_written' => 'a:65:{s:9:"ChangeLog";s:4:"f754";s:21:"ext_conf_template.txt";s:4:"da16";s:12:"ext_icon.gif";s:4:"9df8";s:17:"ext_localconf.php";s:4:"dd47";s:14:"ext_tables.php";s:4:"1bd3";s:28:"ext_typoscript_constants.txt";s:4:"82d1";s:24:"ext_typoscript_setup.txt";s:4:"1172";s:37:"Classes/Controller/BaseController.php";s:4:"d5ea";s:40:"Classes/Controller/ContentController.php";s:4:"502d";s:43:"Classes/Controller/InitializeController.php";s:4:"e174";s:41:"Classes/Controller/SnapshotController.php";s:4:"1638";s:47:"Classes/Domain/Repository/ContentRepository.php";s:4:"a1e4";s:36:"Classes/Domain/Repository/Result.php";s:4:"5522";s:48:"Classes/Domain/Repository/SnapshotRepository.php";s:4:"3d0a";s:26:"Classes/Eid/ClearCache.php";s:4:"3f7d";s:24:"Classes/Utility/Diff.php";s:4:"162d";s:29:"Classes/Utility/Packetize.php";s:4:"2801";s:25:"Classes/Utility/Shell.php";s:4:"ec6b";s:23:"Classes/Utility/Tca.php";s:4:"0a6b";s:42:"Classes/ViewHelpers/TcaLabelViewHelper.php";s:4:"4694";s:46:"Resources/Private/Backend/Layouts/Default.html";s:4:"9133";s:50:"Resources/Private/Backend/Partials/FormErrors.html";s:4:"f5bc";s:48:"Resources/Private/Backend/Partials/PageNode.html";s:4:"8f95";s:48:"Resources/Private/Backend/Partials/Rootline.html";s:4:"4339";s:59:"Resources/Private/Backend/Partials/Snapshot/FormFields.html";s:4:"d41d";s:56:"Resources/Private/Backend/Templates/Content/Compare.html";s:4:"1c74";s:53:"Resources/Private/Backend/Templates/Content/View.html";s:4:"9625";s:56:"Resources/Private/Backend/Templates/Initialize/Show.html";s:4:"02f6";s:54:"Resources/Private/Backend/Templates/Snapshot/List.html";s:4:"831a";s:53:"Resources/Private/Backend/Templates/Snapshot/New.html";s:4:"3d55";s:40:"Resources/Private/Language/locallang.xml";s:4:"1d7d";s:81:"Resources/Private/Language/locallang_csh_tx_contentstage_domain_model_content.xml";s:4:"719c";s:84:"Resources/Private/Language/locallang_csh_tx_contentstage_domain_model_initialize.xml";s:4:"77e8";s:82:"Resources/Private/Language/locallang_csh_tx_contentstage_domain_model_snapshot.xml";s:4:"76af";s:43:"Resources/Private/Language/locallang_db.xml";s:4:"08f2";s:46:"Resources/Private/Language/locallang_stage.xml";s:4:"796b";s:29:"Resources/Public/CSS/Main.css";s:4:"0a17";s:35:"Resources/Public/Icons/relation.gif";s:4:"e615";s:63:"Resources/Public/Icons/tx_contentstage_domain_model_content.gif";s:4:"905a";s:66:"Resources/Public/Icons/tx_contentstage_domain_model_initialize.gif";s:4:"905a";s:64:"Resources/Public/Icons/tx_contentstage_domain_model_snapshot.gif";s:4:"905a";s:39:"Resources/Public/Images/ajax-loader.gif";s:4:"30d8";s:32:"Resources/Public/Images/file.gif";s:4:"9ab0";s:41:"Resources/Public/Images/folder-closed.gif";s:4:"262d";s:34:"Resources/Public/Images/folder.gif";s:4:"9f41";s:33:"Resources/Public/Images/minus.gif";s:4:"e009";s:32:"Resources/Public/Images/plus.gif";s:4:"6c46";s:47:"Resources/Public/Images/treeview-black-line.gif";s:4:"0cdd";s:42:"Resources/Public/Images/treeview-black.gif";s:4:"a3ff";s:49:"Resources/Public/Images/treeview-default-line.gif";s:4:"5e3c";s:44:"Resources/Public/Images/treeview-default.gif";s:4:"4687";s:51:"Resources/Public/Images/treeview-famfamfam-line.gif";s:4:"18b3";s:46:"Resources/Public/Images/treeview-famfamfam.gif";s:4:"dc33";s:46:"Resources/Public/Images/treeview-gray-line.gif";s:4:"9c26";s:41:"Resources/Public/Images/treeview-gray.gif";s:4:"02b4";s:40:"Resources/Public/Images/treeview-red.gif";s:4:"c94a";s:47:"Resources/Public/JavaScript/jquery-1.8.3.min.js";s:4:"24bd";s:42:"Resources/Public/JavaScript/jquery.tmpl.js";s:4:"f222";s:46:"Resources/Public/JavaScript/jquery.tmpl.min.js";s:4:"27bc";s:46:"Resources/Public/JavaScript/jquery.tmplPlus.js";s:4:"d63a";s:50:"Resources/Public/JavaScript/jquery.tmplPlus.min.js";s:4:"ed4a";s:46:"Resources/Public/JavaScript/jquery.treeview.js";s:4:"a0cc";s:37:"Resources/Public/JavaScript/Loader.js";s:4:"e485";s:35:"Resources/Public/JavaScript/Main.js";s:4:"83a5";s:14:"doc/manual.sxw";s:4:"8d2d";}',
);

?>