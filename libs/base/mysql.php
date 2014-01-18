<?
/*
         * HLstats - Real-time player and clan rankings and statistics for Half-Life
         * http://www.hlstats.org
			*
         * Copyright (C) 2001  Simon Garner
         *
			* Playway.net: added a couple methods
			*
         * This program is free software; you can redistribute it and/or
         * modify it under the terms of the GNU General Public License
         * as published by the Free Software Foundation; either version 2
         * of the License, or (at your option) any later version.
         *
         * This program is distributed in the hope that it will be useful,
         * but WITHOUT ANY WARRANTY; without even the implied warranty of
         * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
         * GNU General Public License for more details.
         *
         * You should have received a copy of the GNU General Public License
         * along with this program; if not, write to the Free Software
         * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
*/

class db
{
	var $db_addr;
	var $db_user;
	var $db_pass;
	var $db_name;

	var $link;
	var $result;
	var $rowdata;
	var $insert_id;
	var $numrows;
	var $fieldnames;
	var $query;
	var $affected;
	var $count = 0;
	var $queries;

	function db ($db_addr=-1, $db_user=-1, $db_pass=-1, $db_name=-1)
	{
		if ($db_addr == -1) $db_addr = DB_ADDR;
		if ($db_user == -1) $db_user = DB_USER;
		if ($db_pass == -1) $db_pass = DB_PASS;
		if ($db_name == -1) $db_name = DB_NAME;

		$this->db_addr = $db_addr;
		$this->db_user = $db_user;
		$this->db_pass = $db_pass;
		$this->db_name = $db_name;

		if (DB_PCONNECT == true)
		{
			$connectfunc = "mysql_pconnect";
		}
		else
		{
			$connectfunc = "mysql_connect";
		}

		$this->link = $connectfunc($db_addr, $db_user, $db_pass)
			or $this->error("Could not connect to database server.");
		mysql_select_db($db_name, $this->link)
		or $this->error("Could not select database '$db_name'.");
	}

	function data_seek ($row_number, $result=-1)
	{
		if ($result < 0) $result = $this->result;
		return mysql_data_seek($result, $row_number);
	}

	function reset ($result=-1)
	{
		if ($result < 0) $result = $this->result;
		return mysql_data_seek($result,0);
	}

	function affected ()
	{
		$this->affected = mysql_affected_rows();
		return $this->affected;
	}

	function optimize($table)
	{
		if(!$table) return false;
		$this->result = $this->query("OPTIMIZE TABLE $table");
		return $this->result;
	}

	function list_tables()
	{
		$this->result = mysql_list_tables($this->db_name);
		return $this->result;
	}

	function escape_string($string)
	{
		$string = mysql_escape_string($string);
		return $string;
	}

	function fetch_array ($result=-1)
	{
		if ($result < 0) $result = $this->result;
		$this->rowdata = mysql_fetch_array($result);
		return $this->rowdata;
	}

	function fetch_assoc ($result=-1)
	{
		if ($result < 0) $result = $this->result;
		$this->rowdata = mysql_fetch_assoc($result);
		return $this->rowdata;
	}

	function fetch_row ($result=-1)
	{
		if ($result < 0) $result = $this->result;
		$this->rowdata = mysql_fetch_row($result);
		return $this->rowdata;
	}

	function free_result ($result=-1)
	{
		if ($result < 0) $result = $this->result;
		return mysql_free_result($result);
	}

	function insert_id ()
	{
		$this->insert_id = mysql_insert_id($this->link);
		return $this->insert_id;
	}

	function num_rows ($result=-1)
	{
		if ($result < 0) $result = $this->result;
		$this->numrows = mysql_num_rows($result);
		return $this->numrows;
	}

	function num_fields ($result=-1)
	{
		if($result < 0) $result = $this->result;
		$this->numfields = mysql_num_fields($result);
		return $this->numfields;
	}

	function field_name ($result=-1,$row)
	{
		if($result < 0) $result = $this->result;
		$this->fieldnames = mysql_field_name($result,$row);
		return $this->fieldnames;
	}

	function value($query)
	{
		global $db_debug;

		$this->query = $query;
		$this->result = mysql_query($query,$this->link);

		if ($db_debug)
		{
			echo "<p><pre>$query</pre><hr></p>";
		}

		if (!$this->result && $showerror)
		{
			$this->error("Bad query for $qid");
		}

		list($ret_val) = $this->fetch_row($this->result);
		$this->free_result($this->result);

		return $ret_val;
	}

	function query ($query, $showerror=true,$qid=false)
	{
		global $db_debug;

		$this->count++;
		$this->queries[] = $query;

		$this->query = $query;
		$this->result = mysql_query($query, $this->link);

		if ($db_debug)
		{
			echo "<p><pre>$query</pre><hr></p>";
		}

		if (!$this->result && $showerror)
		{
			$this->error("Bad query for $qid");
		}

		return $this->result;
	}

	function result ($row, $field, $result=-1)
	{
		if ($result < 0) $result = $this->result;

		return mysql_result($result, $row, $field);
	}

	function error ($message, $exit= true)
	{
		error(
			"<b>Database Error</b><br>\n<br>\n" .
			"<i>Server Address:</i> $this->db_addr<br>\n" .
			"<i>Server Username:</i> $this->db_user<p>\n" .
			"<i>Error Diagnostic:</i><br>\n$message<p>\n" .
			"<i>Server Error:</i> (" . mysql_errno() . ") " . mysql_error() . "<p>\n" .
			"<i>Last SQL Query:</i><br>\n<pre><font size=2>$this->query</font></pre>",
			"MySQL DB Error",
			$exit
		);
	}

	function dberror ()
	{
		return mysql_error();
	}
}
?>
