<?php

class SessionUtils {

	private $dbaccess;

	function __construct($dbaccess) {
		$this->dbaccess = $dbaccess;

	}

	function getSessionMaxAge() {
		$query = new DbObj($this->dbaccess);
		$err = $query->exec_query("SELECT val FROM paramv WHERE name = 'CORE_SESSIONMAXAGE'");
		if( $err != "" ) {
			error_log(__CLASS__."::".__FUNCTION__." "."exec_query returned with error: ".$err);
			return false;
		}
		if( $query->numrows() <= 0 ) {
			error_log(__CLASS__."::".__FUNCTION__." "."exec_query returned an empty result set");
			return false;
		}
		$res = $query->fetch_array(0);
		if( is_numeric($res['val']) ) {
			return $res['val']." seconds";
		}
		return $res['val'];
	}

	function getSessionMaxAgeSeconds($default="1 week") {
		$session_maxage = $this->getSessionMaxAge($default);
		if( $session_maxage === false ) {
			return false;
		}
		if( preg_match('/^(\d+)\s+(\w+)/i', $session_maxage, $m) ) {
			$maxage = $m[1];
			$unit = strtolower($m[2]);
			switch( substr($unit, 0, 1) ) {
				case 'y':
					$maxage = $maxage*364*24*60*60; break; # years
				case 'm':
					if( substr($unit, 0, 2) == 'mo' ) {
						$maxage = $maxage*30*24*60*60; break; # months
					} else {
						$maxage = $maxage*60; break; # minutes
					}
				case 'w':
					$maxage = $maxage*7*24*60*60; break; # weeks
				case 'd':
					$maxage = $maxage*24*60*60; break; # days
				case 'h':
					$maxage = $maxage*60*60; break; # hours
				case 's':
					break; # seconds
				default:
					return FALSE;
			}
			return $maxage;
		}
		return FALSE;
	}

	function deleteExpiredSessionFiles() {
		include_once('WHAT/Lib.Prefix.php');

		global $pubdir;

		$session_maxage = $this->getSessionMaxAgeSeconds();
		if( $session_maxage === false ) {
			$err = sprintf("Malformed CORE_SESSIONMAXAGE");
			return $err;
		}
		$maxage = time() - $session_maxage;

		$sessionDir = sprintf("%s/session", $pubdir);
		$dir = opendir($sessionDir);
		if( $dir === false ) {
			$err = sprintf("Error opening directory '%s'.", $sessionDir);
			return $err;
		}

		$sessions = array();
		while( $file = readdir($dir) ) {
			if( preg_match("/^sess_(.+)$/", $file, $m) ) {
				$sess_id = $m[1];
				$sess_file = sprintf("%s/%s", $sessionDir, $file);
				$stat = @stat($sess_file);
				if( $stat !== false && $stat['mtime'] < $maxage ) {
					@unlink($sess_file);
				}
			}
		}
		closedir($dir);

		return "";
	}

}
?>