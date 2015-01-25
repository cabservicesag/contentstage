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
	'version' => '0.5.0',
	'constraints' => 
	array (
		'depends' => 
		array (
			'php' => '5.3-0.0.0',
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
	'_md5_values_when_last_written' => 'a:114:{s:9:"ChangeLog";s:4:"0e68";s:21:"ext_conf_template.txt";s:4:"701c";s:12:"ext_icon.gif";s:4:"9df8";s:17:"ext_localconf.php";s:4:"a04c";s:14:"ext_tables.php";s:4:"1444";s:14:"ext_tables.sql";s:4:"6770";s:28:"ext_typoscript_constants.txt";s:4:"82d1";s:24:"ext_typoscript_setup.txt";s:4:"e295";s:37:"Classes/Controller/BaseController.php";s:4:"784e";s:40:"Classes/Controller/ContentController.php";s:4:"7ac0";s:43:"Classes/Controller/InitializeController.php";s:4:"e968";s:39:"Classes/Controller/ReviewController.php";s:4:"ab6c";s:41:"Classes/Controller/SnapshotController.php";s:4:"8886";s:36:"Classes/Domain/Model/BackendUser.php";s:4:"1656";s:41:"Classes/Domain/Model/BackendUserGroup.php";s:4:"1ec9";s:34:"Classes/Domain/Model/BaseModel.php";s:4:"ef66";s:31:"Classes/Domain/Model/Review.php";s:4:"948e";s:33:"Classes/Domain/Model/Reviewed.php";s:4:"ede5";s:30:"Classes/Domain/Model/State.php";s:4:"eeed";s:56:"Classes/Domain/Repository/BackendUserGroupRepository.php";s:4:"38d5";s:51:"Classes/Domain/Repository/BackendUserRepository.php";s:4:"6117";s:47:"Classes/Domain/Repository/ContentRepository.php";s:4:"f9d6";s:36:"Classes/Domain/Repository/Result.php";s:4:"ca79";s:48:"Classes/Domain/Repository/ReviewedRepository.php";s:4:"e5e8";s:46:"Classes/Domain/Repository/ReviewRepository.php";s:4:"acd7";s:48:"Classes/Domain/Repository/SnapshotRepository.php";s:4:"9fba";s:45:"Classes/Domain/Repository/StateRepository.php";s:4:"13ac";s:26:"Classes/Eid/ClearCache.php";s:4:"cfc6";s:24:"Classes/Hook/TCEMain.php";s:4:"04a3";s:24:"Classes/Utility/Diff.php";s:4:"7bdf";s:29:"Classes/Utility/Packetize.php";s:4:"2801";s:25:"Classes/Utility/Shell.php";s:4:"2850";s:23:"Classes/Utility/Tca.php";s:4:"47a8";s:43:"Classes/ViewHelpers/ReviewForViewHelper.php";s:4:"4793";s:42:"Classes/ViewHelpers/TcaLabelViewHelper.php";s:4:"4694";s:28:"Configuration/TCA/Review.php";s:4:"b17f";s:30:"Configuration/TCA/Reviewed.php";s:4:"b59c";s:27:"Configuration/TCA/State.php";s:4:"55f9";s:44:"Resources/Private/Backend/Layouts/Admin.html";s:4:"8684";s:46:"Resources/Private/Backend/Layouts/Default.html";s:4:"84a4";s:50:"Resources/Private/Backend/Mails/ReviewChanged.html";s:4:"8036";s:50:"Resources/Private/Backend/Mails/ReviewCreated.html";s:4:"3b93";s:49:"Resources/Private/Backend/Mails/ReviewPushed.html";s:4:"6587";s:50:"Resources/Private/Backend/Partials/FormErrors.html";s:4:"f5bc";s:55:"Resources/Private/Backend/Partials/LightboxComment.html";s:4:"9d69";s:48:"Resources/Private/Backend/Partials/PageNode.html";s:4:"6872";s:48:"Resources/Private/Backend/Partials/Rootline.html";s:4:"9a33";s:51:"Resources/Private/Backend/Partials/Field/Check.html";s:4:"931a";s:54:"Resources/Private/Backend/Partials/Field/ReadOnly.html";s:4:"2c68";s:53:"Resources/Private/Backend/Partials/Review/Fields.html";s:4:"55de";s:51:"Resources/Private/Backend/Partials/Review/List.html";s:4:"74bf";s:59:"Resources/Private/Backend/Partials/Snapshot/FormFields.html";s:4:"d41d";s:58:"Resources/Private/Backend/Templates/Content/#Compare.html#";s:4:"a6a3";s:56:"Resources/Private/Backend/Templates/Content/Compare.html";s:4:"2233";s:63:"Resources/Private/Backend/Templates/Content/Compare_backup.html";s:4:"cb75";s:53:"Resources/Private/Backend/Templates/Content/View.html";s:4:"9625";s:56:"Resources/Private/Backend/Templates/Initialize/Show.html";s:4:"9c68";s:52:"Resources/Private/Backend/Templates/Review/Edit.html";s:4:"0cd2";s:54:"Resources/Private/Backend/Templates/Snapshot/List.html";s:4:"303b";s:53:"Resources/Private/Backend/Templates/Snapshot/New.html";s:4:"3d55";s:40:"Resources/Private/Language/locallang.xml";s:4:"6363";s:81:"Resources/Private/Language/locallang_csh_tx_contentstage_domain_model_content.xml";s:4:"719c";s:84:"Resources/Private/Language/locallang_csh_tx_contentstage_domain_model_initialize.xml";s:4:"77e8";s:80:"Resources/Private/Language/locallang_csh_tx_contentstage_domain_model_review.xml";s:4:"cfbf";s:82:"Resources/Private/Language/locallang_csh_tx_contentstage_domain_model_reviewed.xml";s:4:"46de";s:82:"Resources/Private/Language/locallang_csh_tx_contentstage_domain_model_snapshot.xml";s:4:"76af";s:79:"Resources/Private/Language/locallang_csh_tx_contentstage_domain_model_state.xml";s:4:"8825";s:43:"Resources/Private/Language/locallang_db.xml";s:4:"148e";s:46:"Resources/Private/Language/locallang_stage.xml";s:4:"0022";s:51:"Resources/Private/Language/locallang_stageAdmin.xml";s:4:"79f4";s:50:"Resources/Private/Language/locallang_stageInit.xml";s:4:"15c1";s:55:"Resources/Private/Language/locallang_stageSnapshots.xml";s:4:"f806";s:29:"Resources/Public/CSS/Main.css";s:4:"8ec9";s:33:"Resources/Public/HTML/Return.html";s:4:"3654";s:33:"Resources/Public/Icons/accept.png";s:4:"709c";s:33:"Resources/Public/Icons/action.png";s:4:"b6ed";s:32:"Resources/Public/Icons/close.png";s:4:"41c7";s:31:"Resources/Public/Icons/down.png";s:4:"86f3";s:31:"Resources/Public/Icons/push.png";s:4:"6e60";s:33:"Resources/Public/Icons/reject.png";s:4:"8339";s:35:"Resources/Public/Icons/relation.gif";s:4:"e615";s:32:"Resources/Public/Icons/state.png";s:4:"d56b";s:63:"Resources/Public/Icons/tx_contentstage_domain_model_content.gif";s:4:"905a";s:66:"Resources/Public/Icons/tx_contentstage_domain_model_initialize.gif";s:4:"905a";s:62:"Resources/Public/Icons/tx_contentstage_domain_model_review.gif";s:4:"d616";s:64:"Resources/Public/Icons/tx_contentstage_domain_model_reviewed.gif";s:4:"edee";s:64:"Resources/Public/Icons/tx_contentstage_domain_model_snapshot.gif";s:4:"905a";s:61:"Resources/Public/Icons/tx_contentstage_domain_model_state.gif";s:4:"b4d0";s:29:"Resources/Public/Icons/up.png";s:4:"6e60";s:39:"Resources/Public/Images/ajax-loader.gif";s:4:"30d8";s:32:"Resources/Public/Images/file.gif";s:4:"9ab0";s:41:"Resources/Public/Images/folder-closed.gif";s:4:"262d";s:34:"Resources/Public/Images/folder.gif";s:4:"9f41";s:33:"Resources/Public/Images/minus.gif";s:4:"e009";s:32:"Resources/Public/Images/plus.gif";s:4:"6c46";s:47:"Resources/Public/Images/treeview-black-line.gif";s:4:"0cdd";s:42:"Resources/Public/Images/treeview-black.gif";s:4:"a3ff";s:49:"Resources/Public/Images/treeview-default-line.gif";s:4:"5e3c";s:44:"Resources/Public/Images/treeview-default.gif";s:4:"4687";s:51:"Resources/Public/Images/treeview-famfamfam-line.gif";s:4:"18b3";s:46:"Resources/Public/Images/treeview-famfamfam.gif";s:4:"dc33";s:46:"Resources/Public/Images/treeview-gray-line.gif";s:4:"9c26";s:41:"Resources/Public/Images/treeview-gray.gif";s:4:"02b4";s:40:"Resources/Public/Images/treeview-red.gif";s:4:"c94a";s:40:"Resources/Public/JavaScript/iLightbox.js";s:4:"a12d";s:47:"Resources/Public/JavaScript/jquery-1.8.3.min.js";s:4:"24bd";s:42:"Resources/Public/JavaScript/jquery.tmpl.js";s:4:"f222";s:46:"Resources/Public/JavaScript/jquery.tmpl.min.js";s:4:"27bc";s:46:"Resources/Public/JavaScript/jquery.tmplPlus.js";s:4:"d63a";s:50:"Resources/Public/JavaScript/jquery.tmplPlus.min.js";s:4:"ed4a";s:46:"Resources/Public/JavaScript/jquery.treeview.js";s:4:"a0cc";s:37:"Resources/Public/JavaScript/Loader.js";s:4:"fb6d";s:35:"Resources/Public/JavaScript/Main.js";s:4:"7712";s:14:"doc/manual.sxw";s:4:"8d2d";}',
);

?>