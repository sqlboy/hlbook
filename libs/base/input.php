<?
/*
type: row
example: "SELECT * FROM phpstep_table WHERE id = 1 LIMIT 1";
notes: a single row

type: multirow
example: "SELECT * FROM phpstep_table WHERE parent="4";
		notes: expect multiple rows, so must input with multiple queries

	type: keyval
		exmaple: SELECT keyname,value from PHPstep_table where type='1'
		notes: multi row outputtd as single row, then inputted as multi again, limited to 2 values
*/
class Input
{
	var $presets = array("words","float","int","alphanum","alpha","email","url","ipaddr");
	var $errors;
	var $owner;
	/*
		All functions pretaining to the validity of a value
		Fist, assimilate, then check
	*/

	function check_value(&$value,&$widget)
	{
		// do some checking on the value
		if ($value == false && $widget->options["required"] == true && $widget->options["null"] == false)
			$this->errors[] = "The field '" . $widget->options["title"] . "' is required.";

		// apply regex filter
		if($widget->options["regexfilter"])
		{
			if(!preg_match("/" . $widget->options["regexfilter"] . "/",$value))
			{
				if($widget->options["regexerror"])
					$this->errors[] = $widget->options["regexerror"];
				else
					$this->errors[] = "The field '" . $widget->options["title"] . "' is in the wrong format.";
			}
		}

		if($widget->options["regexpreset"] && in_array($widget->options["regexpreset"],$this->presets)) {
			$this->verify_regex_preset(&$value,&$widget);
		}

		if($widget->options["minlength"] && strlen($value) < $widget->options["minlength"])
			$this->errors[] = "The field '" . $widget->options["title"] . "' must be at least " . $widget->options["minlength"] . " character(s) long.";

		// check for max length of data
		if($widget->options["maxlength"] && strlen($value) > $widget->options["maxlength"])
			$this->errors[] = "The field '" . $widget->options["title"]  . "' cannot be more than " . $widget->options["maxlength"] . " character(s).";

		if(isset($widget->options["maxval"]) && $value > $widget->options["maxval"]) {
			$this->errors[] = "The field '" . $widget->options["title"] . " cannot be greater than " .  $widget->options["maxval"] . ". It is $value";
		}
	}

	function verify_regex_preset(&$value,&$widget)
	{
		$title = $widget->options["title"];

		switch($widget->options["regexpreset"])
		{
			case "int":
				if(!is_numeric($value) || !preg_match("/^[0-9\-]+$/",$value)) {
					$this->errors[] = "The field '$title' must be an integer value";
				}
				break;

			case "float":
				if(!is_numeric($value) || !preg_match("/^[0-9\-]+\.[0-9]+$/",$value)) {
					$this->errors[] = "The field '$title' must be a floating point value";
				}
				break;

			case "alphanum":
				if(!preg_match("/^[0-9a-z_]+$/i",$value)) {
					$this->errors[] = "The field '$title' must be an alpha numeric string";
				}
				break;

			case "alpha":
				if(!preg_match("/^[a-z]$/i",$value)) {
					$this->errors[] = "The field '$title' must consist of only the letters a-z";
				}
				break;

			case "email":
				if(!is_email($value)) {
					$this->errors[] = "The field '$title' must be an email address";
				}
				break;

			case "url":
				if(!is_url($value)) {
					$this->errors[] = "The field '$title' must be a URL";
				}
				break;

			case "ipaddr":
				if(!is_ipaddr($value)) {
					$this->errors[] = "The field '$title' must be an ip address";
				}
				break;

			case "words":
				if(!preg_match("/^[0-9a-z\s\-_\#]+$/i",$value)) {
					$this->errors[] = "The field '$title' must be an alpha numeric string a-z and 0-9,spaces are allowed.";
				}

				break;
		}
	}

	# Sometimes widgets have special filters or features that need to be 
	# checked. This is where its done.

	function assimilate_value(&$value,&$widget)
	{
		if($widget->options["tags"] != true)
			$value = strip_tags($value,$widget->options["allowtags"]);

		// if a crypted field is somethign besides the default, crypt it
		// else don't write to it.

		if($widget->options["in_crypt"]) {

			if($value == "(Encrypted)") {
				$widget->options["write"] = 0;
			}
			else {
				if(_CRYPT_) {
					$value = crypt($value,salt());
				}	
			}
		}

		// reverse the effect of replacing all the BR's with \n
		if($widget->options["linebreaks"])
		{
			$value = ereg_replace("\n","<br>",$value);
		}
	}

	function check_owner()
	{
		if($this->options["owner"] != userid()) {
			$this->errors[] = "You cannot update this data, it is owned by: " . $this->options["owner"];	
			return false;
		}

		return true;
	}

	function error()
	{
		if (is_array($this->errors))
			return implode("<p>\n\n", $this->errors);
		else                     
			return false;
	}		

	function process_table_deps()
	{
		$deps = split(",",$this->options["table_deps"]);

		foreach($deps as $table)
		{
			$DB->query("DELETE FROM $table WHERE $this->keycol='" . $this->keyval . "'");
		}
	}

	function where()
	{
		if(is_array($this->keycol)) {

			for($i=0;$i<count($this->keycol);$i++) {
				if($where) { $where .= " && "; }
				$where .= $this->keycol[$i] . "='" . $this->keyval[$i] . "'";
			}
		}
		else {
			$where = $this->keycol . "='" . $this->keyval . "'";
		}

		return $where;
	}

	function addkeys()
	{
		if(is_array($this->keycol)) {

			for($i=0;$i<count($this->keycol);$i++) {
				if($qcols) $qcols .= ", ";
				$qcols .= $this->keycol[$i];

				if($qvals) $qvals .= ", ";
				$qvals .= "'" . $this->keyval[$i] . "'";
			}
		}
		elseif($this->keycol && $this->keyval)
		{
			if ($qcols) $qcols .= ", ";
			$qcols .= $this->keycol;

			if ($qvals) $qvals .= ", ";
			$qvals .= "'" . $this->keyval . "'";
		}

		return array($qcols,$qvals);
	}
}

/*
This one will probably not be used too often.  Its for multirow output displayed in a container
designed for  a single row output.  The admin options page is the best example of how to use this
class right now.
*/


class KeyValInput extends Input
{
	var $widgets;
	var $table;
	var $keycol;
	var $valcol;
	var $options;

	var $queries = array();
	var $values = array();

	function KeyValInput(&$widgets,$table,$keycol,$valcol,$options=false)
	{
		$this->widgets = $widgets;
		$this->table = $table;
		$this->keycol = $keycol;
		$this->valcol = $valcol;

		//not implemented
		$this->options["lock_tables"]		=		true;
		$this->options["low_priority"]	=		true;

		if(is_array($options))
			$this->options = array_merge($this->options,$options);
	}	

	function update()
	{
		if($this->options["owner"] > 0 && !$this->check_owner())
			return false;

		if(!$this->set_values())
			return false;

		$this->build_query();

		if($this->do_query())
			return true;
		else
			return false;
	}

	function set_values()
	{
		if(!is_array($this->widgets))
		{
			$this->errors[] = "There is nothing to update!";
			return false;
		}

		foreach($this->widgets as $widget)
		{
			if(!$widget->options["write"]< 0)
				continue;

			// find the base value
			if($_POST[$widget->name])
				$value = $_POST[$widget->name];
			elseif(isset($widget->options["default"]))
				$value = $widget->options["default"];
			else
				$value = '0';

			$this->assimilate_value(&$value,&$widget->options);
			$this->check_value(&$value,&$widget);
			$this->values[$widget->name] = $value;
		}

		if(is_array($this->errors))
			return false;
		else
			return true;
	}

	function build_query()
	{
		foreach ($this->widgets as $widget)
		{
			$query = "";

			if($widget->options["write"] < 1)
				continue;

			$query = $this->valcol . "='" . $this->values[$widget->name] . "'";

			if($query)
				$this->queries[$widget->name] = $query;
		}
	}

	function do_query()
	{
		global $DB;

		if($this->options["low_priority"])
			$priority = " LOW_PRIORITY ";
		else
			$priority = "";

		foreach ($this->queries as $keycol=>$val)
		{
			$query = "UPDATE $priority $this->table SET " . $val . " WHERE $this->keycol='" . $keycol . "'";
			$result = $DB->query($query,false);

			if($DB->dberror())
			{
				$this->errors[] = "DB Error: " . $DB->dberror();
				return false;
			}
		}

		return true;
	}
}


class MultiRowInput extends Input
{
	var $widgets;
	var $table;
	var $keycol;
	var $options = array();

	var $queries = array();
	var $values = array();

	function MultiRowInput(&$widgets,$table,$keycol,$options=false)
	{
		$this->widgets = $widgets;
		$this->table = $table;
		$this->keycol = $keycol;

		// not implemented
		$this->options["read_lock"]		=		false;
		$this->options["write_lock"]		=		false;
		$this->options["allow_del"]		=		false;		// run delete stuff

		$this->options["low_priority"]	=		true;

		$this->options["new_allow"]		=		false; 		// allow a new entry to be added via the form
		$this->options["new_activate"]	=		"title";		// if this field is set, activate new entry, else ignore

		if(is_array($options))
			$this->options = array_merge($this->options,$options);
	}

	/*
		Ok, there is now a 2 dimentional set of values coming in.
		This function takes whats coming in over POST and builds
		a 2d array of values, adding errors to the error array as it goes.
	*/

	function update()
	{
		if($this->options["owner"] > 0 && !$this->check_owner())
			return false;

		$this->delete();

		if(!$this->set_values())
			return false;

		$this->build_query();

		if($this->do_query())
			return true;
		else
			return false;
	}

	function delete()
	{
		global $DB;

		if(!$this->options["allow_del"] || !$_POST)
			return true;

		foreach ($_POST["rows"] as $row)
		{
			if($_POST[$row . "_delete"] && $row != "new")
			{
				$DB->query("DELETE FROM $this->table WHERE $this->keycol='" . $row . "'");
			}
		}

	}

	function set_values()
	{	
		if(!is_array($_POST["rows"]) || !is_array($this->widgets))
		{
			$this->errors[] = "There is nothing to update!";
			return false;
		}

		/*

		pop = knock the last array of the rows array...if the output object was set to
		allow a new entry, the last one should be new

		If new is not allowed then we dont' pop.
			if new is allowed but the thingy don't exist, we pop

		*/
		if(!$this->options["new_allow"] || !$_POST["new_" . $this->options["new_activate"]])
		{
			array_pop($_POST["rows"]);
		}


		/*-----------------------------------------*/

		foreach ($_POST["rows"] as $row)
		{
			foreach($this->widgets as $widget)
			{
				// skip if widget is not writable
				if($widget->options["write"] < 1)
					continue;

				// get the name of the actual row
				$rowname = $row . "_" . $widget->name;

				if($_POST[$rowname])
					$value = $_POST[$rowname];
				elseif(isset($widget->options["default"]))
					$value = $widget->options["default"];
				else
					$value = '0';

				$this->assimilate_value(&$value,&$widget->options);
				$this->check_value(&$value,&$widget);
				$this->values[$row][$widget->name] = $value;
			}
		}

		if(is_array($this->errors))
			return false;
		else
			return true;	
	}

	function build_query()
	{
		foreach ($this->values as $row=>$val)
		{
			#each element of the values array is technically a query
			$query = false;

			foreach ($this->widgets as $widget)
			{
				if($widget->options["write"] < 1)
					continue;

				if($row == "new")
				{
					if ($qcols) $qcols .= ", ";
					$qcols .= $widget->name;

					if ($qvals) $qvals .= ", ";
					$qvals .= "'" . $val[$widget->name] . "'";
				}
				else
				{
					if ($query) 
						$query .= ", ";

					$query .= $widget->name . "='" . $val[$widget->name] . "'";
				}	
			}

			$this->queries[$row] = $query;
		}

		if($qcols && $qvals)
		{
			$this->queries["new"] = "INSERT INTO $this->table ($qcols) VALUES ($qvals)";
		}

	}

	function do_query()
	{
		global $DB;

		if($this->options["low_priority"])
			$priority = " LOW_PRIORITY ";
		else
			$priority = "";

		foreach ($this->queries as $row=>$val)
		{
			if($row != "new")
				$query = "UPDATE $priority $this->table SET " . $val . " WHERE $this->keycol='" . $row . "'" . $this->options["update_where"];
			else
				$query = $val;

			$result = $DB->query($query);

			if($DB->dberror())
			{
				$this->errors[] = "DB Error: " . $DB->dberror();
				return false;
			}
		}

		return true;
	}
}
/* 
	This is a column based DBInput class.  It generates and executes 1 SQL query based on the an
 	array of widget objects and $_POST vars.
*/

class RowInput extends Input
{
	/* contructor */
	var $widgets;
	var $table;
	var $keycol;
	var $keyval;
	var $options;

	var $query;
	var $values;
	var $exists;

	function RowInput(&$widgets,$table,$keycol,$keyval,$options=false)
	{
		$this->widgets 	= $widgets;
		$this->table 		= $table;
		$this->keycol 		= $keycol;		// both must be array or string
		$this->keyval 		= $keyval;

		if(is_array($this->keycol) && !is_array($this->keyval)) {
			$this->error[] = "Keycol and keyval must be the same data type.";
		}

		if(is_array($this->keyval) && !is_array($this->keycol)) {
			$this->error[] = "Keycol and keyval must be the same data type.";
		}
	}

	function update()
	{
		/*
			Loop through Objects and verify a post value exists, peform extra options on that value
		*/
		if($this->options["owner"] > 0 && !$this->check_owner())
			return false;

		if(!$this->set_values())
			return false;

		/* build an actual SQL query */
		$this->build_query();

		/* send query to sql server */
		$status = $this->do_query();

		if($status)
			return $status;

		return false;
	}

	/*
	Prototype:
	void set_values(void)

	Use:
	Loops through widgets and creates an associated array of values;
	*/

	function set_values()
	{
		for($i=0;$i < count($this->widgets);$i++) {

			$widget = &$this->widgets[$i];

			if($widget->options["write"] == 0)
				continue;

			if($widget->options["convert"] && method_exists($widget,"convert")) {
				$widget->convert();
			}

			// find the base value
			if($_POST[$widget->name])
				$value = $_POST[$widget->name];
			elseif(isset($widget->options["default"]))
				$value = $widget->options["default"];
			else
				$value = '0';

			// bitwise value building
			if($widget->options["search_type"] == "bitwise")
			{
				$value = 0;
				foreach ($_POST[$widget->name . "_bitrows"] as $bitrow)
				{   
					if ($_POST[$widget->name . "_" . $bitrow])
						$value = $value + $bitrow;
				}
			}

			$this->assimilate_value(&$value,&$widget);
			$this->check_value(&$value,&$widget);
			if($widget->options["write"] == 0)
				continue;

			$this->values[$widget->name] = $value;
		}

		if(is_array($this->errors))
			return false;
		else
			return true;
	}

	function check_exists()
	{
		global $DB;

		if(is_array($this->keycol)) {

			$result = $DB->query("SELECT count(*) FROM $this->table WHERE " . $this->where() . " LIMIT 1");
			list($this->exists) = $DB->fetch_row($result);
			$DB->free_result();

			if(!$this->exists) {
				list($qcols,$qvals) = $this->addkeys();
			}

		}
		elseif($this->keyval)
		{
			$result = $DB->query("SELECT count($this->keycol) FROM $this->table WHERE " . $this->where() . " LIMIT 1");
			list($this->exists) = $DB->fetch_row($result);

			if($this->exists == 0) {
				list($qcols,$qvals) = $this->addkeys();
			}

			$DB->free_result($result);
		}
		else
		{
			list($qcols,$qvals) = $this->addkeys();
			$this->exists = false;
		}	
	}

	function build_query()
	{
		$this->check_exists();

		for($i=0;$i < count($this->widgets);$i++) {

			$widget = &$this->widgets[$i];

			if($widget->options["write"] == 0)
				continue;

			if($this->exists)
			{
				if ($query) 
					$query .= ", ";

				$query .= $widget->name . "='" . $this->values[$widget->name] . "'";
			}
			else
			{
				if ($qcols) $qcols .= ", ";
				$qcols .= $widget->name;

				if ($qvals) $qvals .= ", ";
				$qvals .= "'" . $this->values[$widget->name] . "'";
			}
		}

		#add all the other stuff
		if($this->exists)
		{
			$startquery = "UPDATE $this->table SET ";
			$endquery .= " WHERE " . $this->where();
			$this->query = $startquery . $query . $endquery;
		}
		else
		{
			$this->query = "INSERT INTO $this->table ($qcols) VALUES ($qvals)";
		}
	}

	function do_query()
	{
		global $DB;

		$result = $DB->query($this->query,false);

		if($DB->dberror())
		{
			$this->errors[] = "DB Error: " . $DB->dberror();
			return false;
		}

		if($this->exists)
			return $this->keyval;
		else
		{
			# sometimes we are not inserting in a table with auto_increment
			# so if there is no insert ID, we're just going to return true.

			if($DB->insert_id()) {
				return $DB->insert_id();
			}	
			else {
				return true;
			}
		}	
	}
}
