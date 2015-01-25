<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

$TYPO3_CONF_VARS['FE']['eID_include']['tx_contentstage'] = 'EXT:contentstage/Classes/Eid/ClearCache.php';

$pageTS = <<<'EOD'
tx_contentstage {
	// comma separated list of tables that should not be synced
	doNotSync =
	
	// comma separated list of tables that should not be included in the snapshot
	doNotSnapshot =
	
	// deactivate the automatic snapshot when pushing something
	doNotSnapshotOnPush = 0
	
	// comma separated list of tables that should be federated (are automatically excluded from snapshots/sync)
	toFederate =
	
	// array of tables => fields that should not be displayed in the comparison
	doNotDisplay {
		// special table that applies to all tables
		__all {
			// list of field => 1 to be ignored
			l18n_diffsource = 1
			l10n_diffsource = 1
			SYS_LASTCHANGED = 1
		}
	}
	
	// if this is set to 0, only files and folders are pushed
	pushDependencies = 1
	
	// the possible recursion depths in order to display
	depthOptions = 0,1,2,3,4,5,6,7,8,9,-1
	
	// default depth when nothing is selected yet and no cookie is set
	defaultDepth = -1
	
	// minimum recursion depth that may be selected
	minimumDepth = 0
	
	// maximum recursion depht that may be selected
	maximumDepth = -1

	// disable the feature that first uses md5 sums from the db
	disableHashedCompare = 0
	
	// use https for links to the local system
	useHttpsLocal = 0
	
	// use https for links to the remote system (also affects the eID call to clear the cache)
	useHttpsRemote = 0
	
	// use a different domain for links to the local system than the one detected
	overrideDomainLocal =
	
	// use a different domain for links to the remote system than the one detected (also affects the eID call to clear the cache)
	overrideDomainRemote =
	
	// enable/disable the review system
	review = 0
	review {
		// the amount of backend users that are needed to review
		required = 2
		// if this is set, review records may be created
		editCreate = 0
		// if this is set, push may be used even when the review is not "reviewed"
		mayPush = 0
		// explicit list of backend groups that are supposed to review
		groups = 0
		// default auto push to yes for new reviews
		defaultAutoPush = 0
		// automatically set to reviewed if a person sets him/herself as reviewer
		autoReviewIfSelf = 1
		// send mail to active user if found
		sendMailToCurrentUser = 0
	}
	
	// the mail configuration for the review mails
	mails {
		// default settings for the mails, will always be merged with the specific mail
		default {
			// set the (fluid) template
			templateFile = 
			
			// send from this email
			from = info@example.com
			
			// send from this name
			fromName = example.com - Contentstage (noreply)
			
			// send as html mail
			html = 0
			
			// send to reviewers of the current review (except active be_user)
			sendToReviewers = 1
			to {
				/*
				# allows to send additional mails to defined email addresses
				0 {
					name = Example info mail
					mail = info@example.com
				}
				*/
			}
		}
		
		// mail when a review was updated
		reviewChanged {
			templateFile = EXT:contentstage/Resources/Private/Backend/Mails/ReviewChanged.html
		}
		
		// mail when a review was created
		reviewCreated {
			templateFile = EXT:contentstage/Resources/Private/Backend/Mails/ReviewCreated.html
		}
		
		// mail when a review was completed
		reviewPushed {
			templateFile = EXT:contentstage/Resources/Private/Backend/Mails/ReviewPushed.html
		}
	}
}
EOD;

t3lib_extMgm::addPageTSConfig($pageTS);
