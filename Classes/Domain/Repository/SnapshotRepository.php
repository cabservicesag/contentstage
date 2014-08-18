<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Nils Blattner <nb@cabag.ch>, cab services ag
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
 * Allows to create/revert/delete mysqldump snapshots.
 *
 * @package contentstage
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class Tx_Contentstage_Domain_Repository_SnapshotRepository {
	/**
	 * @const string strftime()-filename for the snapshot.
	 */
	const DUMPFILE = 'uploads/tx_contentstage/snapshots/%Y%m%d-%H%M%S_contentstage_snapshot_%type.sql.gz';
	
	/**
	 * @const string Write error.
	 */
	const ERROR_WRITE = 1354723058;
	
	/**
	 * @const string Dump error.
	 */
	const ERROR_DUMP = 1354723059;
	
	/**
	 * @const string No file error.
	 */
	const ERROR_NOFILE = 1354723060;
	
	/**
	 * @const string Read error.
	 */
	const ERROR_READ = 1354723061;
	
	/**
	 * @const string SQL error.
	 */
	const ERROR_ALTER = 1354790965;
	
	/**
	 * @var array An array of table => true pairs to be ignored in snapshots.
	 */
	protected $ignoreSnapshotTables = array();
	
	/**
	 * Creates a new snapshot.
	 *
	 * @param array $tables An array of the tables to dump.
	 * @param array $login The DB login data.
	 * @param string $type The type of the snapshot. Should be either local or remote.
	 * @return array An array with additional debug information.
	 * @throws Exception
	 */
	public function create($tables, $login, $type = Tx_Contentstage_Domain_Repository_ContentRepository::TYPE_LOCAL) {
		$debug = array();
		$debug['file'] = $filename = PATH_site . strftime(str_replace(array('%type'), array($type), self::DUMPFILE));
		
		if (file_put_contents($filename, '') === false){
			throw new Exception($filename, self::ERROR_WRITE);
		}
		
		@file_put_contents(dirname($filename) . '/.htaccess', 'deny from all');
		
		$debug['command'] = $datadumpcmd = Tx_Contentstage_Utility_Shell::findCmd('mysqldump').' \
			--quote-names \
			--complete-insert \
			--skip-comments \
			' . Tx_Contentstage_Utility_Shell::getMysqlLoginCredentials($login) . ' \
			' . $login['database'] . ' \
			'.implode(' ', $tables) . ' | ' . Tx_Contentstage_Utility_Shell::findCmd('gzip');
		
		$dumpdata = t3lib_div::makeInstance('Tx_Contentstage_Utility_Shell');
		if (!$dumpdata->exec($datadumpcmd, PATH_site, $filename, 'a')){
			throw new Exception($dumpdata->getStderr(), self::ERROR_DUMP);
		}
		
		$debug['filesize'] = round((@filesize($filename)/1024)/1024, 1).' MB';
		
		return $debug;
	}
	
	/**
	 * Revert given db to an existing snapshot.
	 *
	 * @param string $file The snapshot file. Must be relative to the snapshot dir or absolute!
	 * @param array $login The DB login data.
	 * @return array An array with additional debug information.
	 * @throws Exception
	 */
	public function revert($file, $login) {
		$debug = array();
		if (substr($file, 0, 1) !== '/' && !preg_match('#[A-Z]:[/\\\\]#', $file)) {
			$file = PATH_site . dirname(self::DUMPFILE) . '/' . $file;
		}
		$debug['file'] = $filename = $file;
		
		if (!file_exists($filename)){
			throw new Exception($filename, self::ERROR_NOFILE);
		}
		
		$debug['command'] = $datadumpcmd = Tx_Contentstage_Utility_Shell::findCmd('cat') . ' ' . escapeshellarg($filename) . ' | ' . Tx_Contentstage_Utility_Shell::findCmd('gunzip') . ' | ' . Tx_Contentstage_Utility_Shell::findCmd('mysql').' \
			' . Tx_Contentstage_Utility_Shell::getMysqlLoginCredentials($login) . ' \
			' . $login['database'];
		
		$dumpdata = t3lib_div::makeInstance('Tx_Contentstage_Utility_Shell');
		if (!$dumpdata->exec($datadumpcmd, PATH_site, $filename, 'a')){
			throw new Exception($dumpdata->getStderr(), self::ERROR_READ);
		}
		
		return $debug;
	}
	
	/**
	 * Deletes a snapshot file.
	 *
	 * @param string $file The file name to delete.
	 * @return void
	 */
	public function remove($file) {
		$path = PATH_site . dirname(self::DUMPFILE) . '/' . $file;
		if (file_exists($path)) {
			unlink($path);
		}
	}
	
	/**
	 * Go through all tables and raise the auto_increment index for each table.
	 *
	 * @param Tx_Contentstage_Domain_Repository_ContentRepository $repository The repository to do the raise for.
	 * @param int $step The step to go up.
	 * @param float $threshold Ensures that there will be at least (1 - threshold) * step auto_increments left between the next remote uid hits the new auto_increment.
	 * @return array An array with additional debug information.
	 * @throws Exception
	 */
	public function raiseAutoIncrement($repository, $step, $threshold) {
		$debug = array();
		
		$debug['tables'] = $tables = $repository->getTables();
		
		$maximumAutoIncrement = 0;
		foreach ($tables as $tableName => &$table) {
			$maximumAutoIncrement = max($maximumAutoIncrement, $table['Auto_increment']);
		}
		
		$plusOne = ($maximumAutoIncrement % $step) > ($step * $threshold) ? 1 : 0;
		
		$debug['plusOne'] = $plusOne;
		$debug['maximumAutoIncrement'] = $maximumAutoIncrement;
		$debug['newAutoIncrement'] = $newAutoIncrement = (ceil($maximumAutoIncrement / $step) + $plusOne) * $step + 1;
		
		$db = $repository->_getDb();
		foreach ($tables as $tableName => &$table) {
			if (intval($table['Auto_increment']) > 0) {
				$db->sql_query('ALTER TABLE ' . $db->quoteStr($tableName, $tableName) . ' AUTO_INCREMENT = ' . $newAutoIncrement . ';');
				
				if ($db->sql_error()){
					throw new Exception('[' . $tableName . '] ' . $db->sql_error(), self::ERROR_ALTER);
				}
			}
		}
		
		return $debug;
	}
	
	/**
	 * Returns all snapshots.
	 *
	 * @return array Array of arrays with keys fileName and relativePath.
	 */
	public function findAll() {
		$files = array();
		$relativeDirectory = dirname(self::DUMPFILE) . '/';
		$directory = PATH_site . $relativeDirectory;
		
		if ($handle = opendir($directory)) {
			while (false !== ($file = readdir($handle))) {
				if (substr($file, 0, 1) !== '.' && $file !== 'index.html') {
					$files[$file] = array(
						'fileName' => $file,
						'relativePath' => $relativeDirectory . $file,
						'size' => round((@filesize($directory . $file)/1024)/1024, 1).' MB'
					);
				}
			}
			closedir($handle);
		}
		krsort($files);
		return $files;
	}
	
	/**
	 * Removes the ignored tables from the given tables for a snapshot.
	 *
	 * @param array $tables An array of table names.
	 * @return array An array of table names.
	 */
	public function removeIgnoredTablesForSnapshot($tables) {
		$cleanTables = array();
		
		foreach ($tables as $table) {
			if (!isset($this->ignoreSnapshotTables[$table])) {
				$cleanTables[] = $table;
			}
		}
		
		return $cleanTables;
	}
	
	/**
	 * Sets the tables to be ignored in a snapshot.
	 *
	 * @param array $ignoreSnapshotTables An array of table => true of tables to be ignored.
	 * @return Tx_Contentstage_Domain_Repository_SnapshotRepository Self reference.
	 */
	public function setIgnoreSnapshotTables($ignoreSnapshotTables) {
		$this->ignoreSnapshotTables = $ignoreSnapshotTables;
		return $this;
	}
	
	/**
	 * Gets the tables to be ignored in a snapshot.
	 *
	 * @return array  An array of table => true of tables to be ignored.
	 */
	public function getIgnoreSnapshotTables() {
		return $this->ignoreSnapshotTables;
	}
}
?>