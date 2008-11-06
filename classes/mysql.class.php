<?php

defined( '_ACM_VALID' ) or die( 'Direct Access to this location is not allowed.' );

class mysql {
	
	var $host, $user, $pass, $db;

	function mysql($host, $user, $pass, $db) {
		$this->host	= $host;
		$this->user	= $user;
		$this->pass	= $pass;
		$this->db	= $db;
		
		if(empty($this->pass))
			DEBUG::add('Your configuration file contains settings ('.$user.' with no password) that correspond to the default MySQL privileged account. Your MySQL server is running with this default, is open to intrusion, and you really should fix this security hole.', 'red');
	}

	function connect () {
		global $error, $vm;
		if(!@mysql_connect ($this->host,$this->user,$this->pass)) {
			$error = $vm['_error_db_connect'];
			return false;
		}
		if(!@mysql_select_db ($this->db)) {
			$error = $vm['_error_db_select'];
			return false;
		}
		return true;
	}

	function query ($q) {
		DEBUG::add($q);
		$rslt = @mysql_query ($q);
		DEBUG::add('Records: '.mysql_affected_rows());
		return $rslt;
	}

	function result ($q) {
		DEBUG::add($q);
		$rslt = @mysql_result (@mysql_query($q), 0);
		DEBUG::add('Result: '.gettype($rslt).'('.var_export($rslt, true).')');
		return $rslt;
	}

	function close () {
		@mysql_close ();
	}
}

class mysql_ls extends mysql{
	function mysql_ls() {
		global $ls_host, $ls_user, $ls_pass, $ls_db;
		$this->mysql($ls_host, $ls_user, $ls_pass, $ls_db);
	}
}

class mysql_gs extends mysql{
	function mysql_gs($id) {
		global $gs_host, $gs_user, $gs_pass, $gs_db;
		$this->mysql($gs_host[$id], $gs_user[$id], $gs_pass[$id], $gs_db[$id]);
	}
}

?>