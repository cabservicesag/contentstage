<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Jonas Felix <jf@cabag.ch>, Nils Blattner <nb@cabag.ch>, cab services ag
 *  
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
 * Packetizer utility class.
 *
 * @package contentstage
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class Tx_Contentstage_Utility_Packetize {
	private $piVars; // http post/get vars for packetize
	private $filepreset; // presets with filelists for tar
	private $form; // object for form widgets
	private $lastremovedTables; // array with tables removed from an array
	private $fileincludeliste = array(); // list of files for tar
	private $fileexcludelist; // list of files for tar to exclude
	
	private $ownermod; // object of the owner module
	
	private $content;
	
	const dbdumpfile = 'dbdump.sql'; // filename (path relative to PATH_site) for db dump
	const packagefile = '%Y%m%d-%H%M%S_contentstage_snapshot.tar.gz'; // tar archive file for all the data :-)
	
	public function __construct(){
		
		$this->filepreset['projectdata'] = array(
			'fileadmin',
			'uploads'
		);
		
		$this->excludedcachetables = array(
			'cache_extensions',
			'cache_hash',
			'cache_imagesizes',
			'cache_pages',
			'cache_pagesection',
			'tx_realurl_pathcache',
			'tx_realurl_uniqalias',
			'tx_realurl_urldecodecache',
			'tx_realurl_urlencodecache',
			'tx_rtehtmlarea_acronym',
			'cachingframework_cache_hash',
			'cachingframework_cache_hash_tags',
			'cachingframework_cache_pages',
			'cachingframework_cache_pagesection',
			'cachingframework_cache_pagesection_tags',
			'cachingframework_cache_pages_tags'
			);
		
		$this->excludedindextables = array(
			'index_fulltext',
			'index_grlist',
			'index_phash',
			'index_rel',
			'index_section',
			'index_words'
			);
	}
	
	private function packetize() {
		global $LANG;
		
		$content = '';
		
		if(!empty($this->piVars['dbstructure']) || !empty($this->piVars['dbdata'])){
			$content .= $this->packetizeDatabase();
		}
		
		$content .= $this->packetizeFiles();
		
		$content .= '<hr/>';
		return $content;
	}
	
	private function packetizeDatabase(){
		global $LANG;
		
		$content = '';
		
		if(file_put_contents(PATH_site.self::dbdumpfile, '') === false){
			throw new Exception('Could not writhe db file '.PATH_site.self::dbdumpfile);
		} else {
			$this->fileincludeliste[] = self::dbdumpfile;
		}
		
		// get existing tables
		$origTables = $GLOBALS['TYPO3_DB']->admin_get_tables();
		
		// remove obsolete tables
		foreach($origTables as $tablename => $info){
			if(stristr($tablename, 'zzz_deleted') === false){
				$tables[] = $tablename; 
			}
		}
		
		// get mysql port
		$mysqlPort = ini_get('mysql.default_port');
		
		if($mysqlPort == '') {
			//$mysqlPort = 3306;
		}
		
		// dump structure information
		if(!empty($this->piVars['dbstructure'])){
			$content .= '<h3>Dump structure.</h3>';
			
			$structuredumpcmd = tx_cabagsla_shell::findCmd('mysqldump').' \
				--add-drop-table  \
				--quote-names \
				--no-data \
				--no-create-db \
				--skip-comments \
				--port='.$mysqlPort.'\
				--user='.TYPO3_db_username.' \
				--password='.TYPO3_db_password.' \
				--host='.TYPO3_db_host.' \
				'.TYPO3_db.' \
				'.implode(' ', $tables);
				
			$dumpstructure = t3lib_div::makeInstance('tx_cabagsla_shell');
			if(!$dumpstructure->exec($structuredumpcmd, PATH_site, self::dbdumpfile, 'w')){
				throw new Exception('Could not dump db structure '.$dumpstructure->getStderr());
			}
			
			$content .= '<div>'.$structuredumpcmd.'</div>';
		}
		
		
		if(!empty($this->piVars['dbdata'])){
			// remove cache tables 
			if(empty($this->piVars['dbincludecache'])){
				$content .= '<h3>'.$LANG->getLL('removecachetables').'</h3>';
				$tables = $this->removeTables($tables, $this->excludedcachetables);
				$content .= '<div>'.implode(', ', $this->lastremovedTables).'</div>';
			}
			
			// remove index tables 
			if(empty($this->piVars['dbincludeindex'])){
				$content .= '<h3>'.$LANG->getLL('removeindextables').'</h3>';
				$tables = $this->removeTables($tables, $this->excludedindextables);
				$content .= '<div>'.implode(', ', $this->lastremovedTables).'</div>';
			}
			
			// remove log tables 
			if(empty($this->piVars['dbincludelog'])){
				$content .= '<h3>'.$LANG->getLL('removelogtables').'</h3>';
				$tables = $this->removeTables($tables, $this->excludedlogtables);
				$content .= '<div>'.implode(', ', $this->lastremovedTables).'</div>';
			}
			
			// dump data
			$content .= '<h3>Dump data.</h3>';
			$datadumpcmd = tx_cabagsla_shell::findCmd('mysqldump').' \
				--quote-names \
				--no-create-db \
				--no-create-info \
				--complete-insert \
				--skip-comments \
				--port='.$mysqlPort.'\
				--user='.TYPO3_db_username.' \
				--password='.TYPO3_db_password.' \
				--host='.TYPO3_db_host.' \
				'.TYPO3_db.' \
				'.implode(' ', $tables);
			
			$dumpdata = t3lib_div::makeInstance('tx_cabagsla_shell');
			if(!$dumpdata->exec($datadumpcmd, PATH_site, self::dbdumpfile, 'a')){
				throw new Exception('Could not dump db data '.$dumpdata->getStderr());
			}
			
			$content .= '<div>'.$datadumpcmd.'</div>';
		}
		
		$content .= '
			<h3>
				Generated successfully db dump file. Size: '.round((@filesize(PATH_site.self::dbdumpfile)/1024)/1024, 1).' MB
			</h3>';
		
		return $content;
	}
	
	private function packetizeFiles(){
		$content = '';
		
		if(!empty($this->piVars['filepreset'])){
			if(!empty($this->filepreset[$this->piVars['filepreset']])){
				$this->fileincludeliste = array_merge($this->fileincludeliste, $this->filepreset[$this->piVars['filepreset']]);
			} else {
				throw new Exception('Filepreset '.$this->piVars['filepreset'].' not found.');
			}
		} else if(!empty($this->piVars['files'])){
			$this->fileincludeliste = array_merge($this->fileincludeliste, $this->piVars['files']);
		}
		
		if(count($this->fileincludeliste) < 1){
			throw new Exception('No files in include list for packetizing');
		} else {
			
			if(empty($this->piVars['includecachefiles'])){
				$this->fileexcludelist[] = 'typo3temp/\*.jpg';
				$this->fileexcludelist[] = 'typo3temp/\*.gif';
				$this->fileexcludelist[] = 'typo3temp/\*.zip';
				$this->fileexcludelist[] = 'typo3temp/\*temp\*';
				$this->fileexcludelist[] = 'typo3temp/mw_\*';
				$this->fileexcludelist[] = 'typo3temp/\*.cache';
				$this->fileexcludelist[] = 'typo3temp/\*.tbl';
				$this->fileexcludelist[] = 'typo3temp/\*.js';
				$this->fileexcludelist[] = 'typo3temp/\*.css';
				$this->fileexcludelist[] = 'typo3temp/\*.html';
				$this->fileexcludelist[] = 'typo3temp/\*.txt';
				$this->fileexcludelist[] = 'typo3temp/\*.xml.gz';
				$this->fileexcludelist[] = 'typo3conf/temp_\*';
				$this->fileexcludelist[] = 'typo3conf/l10n/\*.zip';
				$exclude = ' --exclude='.implode(' --exclude=', $this->fileexcludelist);
			} else {
				$exclude = ' ';
			}
			
			// remove files which do not exist
			foreach($this->fileincludeliste as $filekey => $filename) {
				if(!file_exists(PATH_site.$filename)){
					unset($this->fileincludeliste[$filekey]);
				}
			}
			
			$packetizecmd =  tx_cabagsla_shell::findCmd('tar').' \
				-zcf '.self::packagefile.' \
				'.$exclude.' \
				'.implode(' ', $this->fileincludeliste);
				
			$packetize = t3lib_div::makeInstance('tx_cabagsla_shell');
			if(!$packetize->exec($packetizecmd, PATH_site)){
				throw new Exception('Could not tar files '.$packetize->getStderr().' '.$packetize->getCmd());
			}
			
			$content .= '<h3> Create tape archive package: </h3>';
			$content .= '<div>'.$packetizecmd.'</div>';
			$content .= '<div>'.$packetize->getStdout().'</div>';
		}
		
		return $content;
	}
	
	private function removeTables($tables, $removetables){
		$this->lastremovedTables = array();
		foreach($removetables as $tablename){
			$this->lastremovedTables[] = $tablename;
			$key = array_search($tablename, $tables);
			if($key !== false){
				unset($tables[$key]);
			}
		}
		
		return $tables;
	}
}
