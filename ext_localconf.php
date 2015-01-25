<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

$TYPO3_CONF_VARS['FE']['eID_include']['tx_contentstage'] = 'EXT:contentstage/Classes/Eid/ClearCache.php';

$pageTS = <<<'EOD'
tx_contentstage {
	review = 0
	review {
		required = 2
		editCreate = 0
		mayPush = 0
	}
	
	mails {
		default {
			templateFile = 
			from = info@example.com
			fromName = example.com - Contentstage (noreply)
			html = 0
		}
		
		reviewChanged {
			templateFile = EXT:contentstage/Resources/Private/Backend/Mails/ReviewChanged.html
		}
		
		reviewCreated {
			templateFile = EXT:contentstage/Resources/Private/Backend/Mails/ReviewCreated.html
		}
	}
}
EOD;

t3lib_extMgm::addPageTSConfig($pageTS);
?>
