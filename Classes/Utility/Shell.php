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
 * Shell utility class.
 *
 * @package contentstage
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class Tx_Contentstage_Utility_Shell {
	private $cmd = '';
	private $stdout = '';
	private $stderr = '';
	private $stderrfile;
	
	public function __construct(){
		$this->stderrfile = PATH_site.'typo3temp/tx_contentstage_shell_exec_stderr';
	}
	
	public function exec($cmd, $cwd, $stdoutfile = false, $stdoutfilemode = 'w'){
		$this->stdout = '';
		$this->stderr = '';
		
		$prefix = 'export LC_ALL=de_CH.UTF-8; export LANG=de_CH.UTF-8; cd '.$cwd.' ; ';
		if (TYPO3_OS == 'WIN') {
			$prefix = '';
			$cmd = preg_replace('/\\\\\s*\r?\n/', ' ', $cmd);
		}
		
		if(empty($stdoutfile)){
			$this->cmd = $prefix . $cmd . ' 2>' . $this->stderrfile;
			if(function_exists('shell_exec')) {
				$this->stdout = shell_exec($this->cmd);
			} else {
				exec($this->cmd, $outputarray);
				$this->stdout = implode("\n", $outputarray);
			}
		} else {
			if($stdoutfilemode == 'a'){
				$stdoutfilemode = '>>';
			} else {
				$stdoutfilemode = '>';
			}
			$this->cmd = $prefix . $cmd . ' 2>' . $this->stderrfile . ' ' . $stdoutfilemode . $stdoutfile;
			if(function_exists('shell_exec')) {
				shell_exec($this->cmd);
			} else {
				exec($this->cmd);
			}
			$this->stdout = 'dumped into '.$stdoutfilemode.$stdoutfile;
		}
		
		if(file_exists($this->stderrfile)){
			$this->stderr = file_get_contents($this->stderrfile);
			unlink($this->stderrfile);
			if(strlen($this->stderr) > 0){
				return false;
			} else {
				return true;
			}
		} else {
			return true;
		}
	}
	
	public function getStdout() {
		return $this->stdout;
	}
	
	public function getStderr() {
		return $this->stderr;
	}

	public function getCmd() {
		return $this->cmd;
	}
	
	static function findCmd($cmd) {
		$binFolders = array(
			'',
			'/usr/bin/',
			'/usr/local/bin/',
			'/bin/',
			'/Applications/xampp/xamppfiles/bin/'
		);
		$binFolders = array_merge($binFolders, t3lib_div::trimExplode(',', $GLOBALS['TYPO3_CONF_VARS']['SYS']['binPath']));
		
		$extensions = array('');
		
		if (TYPO3_OS == 'WIN') {
			array_unshift($extensions, '.exe');
		}
		
		foreach ($extensions as $extension) {
			reset($binFolders);
			foreach ($binFolders as $cwd) {
				$which = t3lib_div::makeInstance('Tx_Contentstage_Utility_Shell');
				if (TYPO3_OS == 'WIN') {
					$which->exec('WHERE ' . (($cwd === '') ? $cwd : $cwd . ':') . $cmd . $extension, '');
				} else {
					$which->exec('which ' . $cwd . $cmd . $extension, '');
				}
				$stdout = $which->getStdout();
				if (strlen($stdout) > 0) {
					return trim($stdout);
				}
			}
		}

		throw new Exception('Command not found '.$cmd);
	}
}
