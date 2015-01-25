<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

$TYPO3_CONF_VARS['FE']['eID_include']['tx_contentstage'] = 'EXT:contentstage/Classes/Eid/ClearCache.php';

$pageTS = <<<'EOD'
tx_contentstage {
	doNotSync =
	doNotSnapshot =
	doNotDisplay =
	
	depthOptions = 0,1,2,3,4,5,6,7,8,9,-1
	defaultDepth = -1
	minimumDepth = 0
	maximumDepth = -1
	
	useHttpsLocal = 0
	useHttpsRemote = 0
	
	overrideDomainLocal =
	overrideDomainRemote =
	
	review = 0
	review {
		// the amount of backend users that are needed to review
		required = 2
		// if this is set, review records may be created
		editCreate = 0
		// if this is set, push may be used even when the review is not "reviewed"
		mayPush = 0
		// explizit list of backend groups that are supposed to review
		groups = 0
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
