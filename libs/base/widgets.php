<?
function widget($name)
{
	if(!file_exists(LIB_PATH . "widgets/" . $name . ".php"))
		return false;

	require_once(LIB_PATH . "widgets/" . $name . ".php");
	return true;
}

/* 
Prototype:
bool = register_all_widgets(string)

Use:
loads all the widgets in a specific directory
*/

function register_all_widgets()
{
	if($dh = opendir(LIB_PATH . "widgets"))
	{
		while (false !== ($file = readdir($dh)))
		{ 
			if(preg_match("/\.php$/",$file))
			{
				require_once(LIB_PATH . "widgets/" . $file);
			}
		}

		closedir($dh);
		return true;
	}

	return false;
}

function get_available_widgets(&$list)
{
	if(!is_array($list)) {
		$list = array();
	}

	if($dh = opendir(LIB_PATH . "widgets"))
	{
		while (false !== ($file = readdir($dh)))
		{ 
			if(preg_match("/^(.*?)\.php$/",$file,$match))
			{
				$list[] = $match[1];
			}
		}

		closedir($dh);
		asort($list);
		return true;
	}

	return false;
}



/* first, init widget, then have sub class set its defaults */

class Widget
{
	var $name;
	var $title;
	var $link;

	var $img;
	var $options;
	var $style;
	var $attrs;

	var $valuesource;
	var $value;
	var $datasource;
	var $data;

	function option($string,$value)
	{
		$this->options[$string] = $value;
		return 1;
	}

	function style($array) {

		if(is_array($this->style))
			$this->style = array_merge($this->style,$array);
		else
			$this->style = $array;
	}

	function set($string,$value)
	{
		$this->$string = $value;
		return 1;
	}

	function has_access_to_widget()
	{
		$ok = 0;

		if(check_access($this->options["acclevel"])) {
			$ok++;
		}

		if(check_auth($this->options["auth"])) {
			$ok++;
		}

		if($ok == 2)
			return true;

		return false;
	}

	function do_attr($attr,$val) {

		$html = " " . $attr . "=\"" . $val . "\"";
		return $html;
	}

	function mk_link()
	{
		if($this->options["target"])
			$target = $this->do_attr("target",$this->options["target"]);

		if($this->options["tooltip"])
			$tooltip = $this->do_attr("title",$this->options["tooltip"]);

		$link = "<a href=\"" . $this->link . "\"" . $target . $tooltip . ">";

		return $link;
	}

	function set_widget_value($value)
	{
		#first run convert if it exists
		#if($this->options["convert"] && method_exists($this,"convert") && $_POST)
		#{
		#$this->convert();
		#}

		if($this->options["sticky"] && $_POST[$this->name])
		{
			$this->value =  $_POST[$this->name];
		}
		elseif($this->options["callback"])
		{
			if($this->valuesource)
			{
				$this->value = $this->options["callback"]($this->valuesource);
			}
			else
			{
				$this->value = $this->options["callback"]($value);
			}	
		}
		elseif($this->valuesource)
		{
			$this->valuesource_to_value();
		}
		elseif($value)
		{
			$this->value = &$value;
		}

	/* Special processing options for output

	Sometimes the output needs to be modified to hide password data
	or format a date.  The options below are the output formating
	options.

	option(default)

	-tags(false) - strip html/php tags from obvalue
	-stripslashes(false) - strip escape slashes from obvalue
	-crypt(false) - crypt the value, *nix only...but like you use anything else anyway.
	-md5(false) - create md5 hash from obvalue
	-strpad(false) - pad a string with another string to a specific length
	-lower(false) - make string all lowercase
	-upper(false) - make string all upercase
   -dateformat(false) -format an integer to supplied php date

	*/
		if(!$this->value && isset($this->options["default"])) {
			$this->value = $this->options["default"];
		}

		if($this->value > 0 && $this->options["timezone"] == true && get_option("base","apply_timezone")) {
			$this->value = $this->value + (apply_offset());
		}

		if($this->options["linebreaks"]) {
			$this->value = ereg_replace("<br>","\n",$this->value);
		}

		if(is_array($this->options) && $this->value)
		{
			foreach ($this->options as $option=>$opval)
			{
				if($opval && strstr($option,"out_"))
				{
					switch($option)
					{
						case "out_stripslashes":
							$this->value = stripslashes($this->value);
							break;

						case "out_crypt":
							$this->value = crypt($this->value);
							break;

						case "out_md5":
							$this->value = md5($this->value);
							break;

						case "out_strpad":

							if(list($length,$char) = split(":",$opval)) {
								$this->value = str_pad($this->value,$length,$char);
							}
							break;

						case "out_lower":
							$this->value = strtolower($this->value);
							break;

						case "out_upper":
							$this->value = strtoupper($this->value);
							break;

						case "out_sprintf":
							$this->value = sprintf($opval,$this->value);
							break;
					}
				}
			}
		}

		// finally, strip out tags and stuff
		if($this->options["tags"] != true && $this->value)
		{
			$this->value = strip_tags($this->value,$this->options["allow"]);
		}
	}

	function get_widget_datasource()
	{
		if(!$this->datasource)
		{
			$this->data = array();
			return true;
		}
		elseif(is_array($this->datasource))
		{
			$this->data = $this->datasource;
			return true;
		}
		elseif(preg_match("/^(.*):(.*)$/",$this->datasource,$match))
		{
			$type = $match[1];
			$source = $match[2];

			if($type == "list")
			{
			/*

			this is a ; delimited list that be either single dimensional
			associative or both at the same time

			order is key
			key/value;
			key;key/value;key/valye;key;key/value

			*/

				foreach (explode(";", $source) as $element)
				{
					if (ereg("/", $element))
					{
						list($key, $val) = explode("/", $element);
						$this->data[$key] = $val;
					}
					else
					{
						$this->data[$element] = $element;
					}
				}
			}
			if($type == "file")
			{
				/*

				This loads a datasource from a file.

				Options
				file_delimit: what the list is delmited by, default ,
				datapath -> where it can be found, default INCLUDE_PATH

				it needs a file with some kind of delimiter to split into key, value
				if there is no delimiter it does a 1d list delimited by newline

				*/

				// defaults
				$source = $match[2];
				$delimit = ",";

				#dont let people use .. or get out of the BASE_PATH dir
				if(preg_match("/\.\./",$source))
					return false;

				if(!preg_match("/^" . BASE_PATH . "/",$source))
					return false;

				if($this->options["file_delimit"])
					$delimit = $this->options["file_delimit"];

				// open file
				if($fp = fopen( $source, "r"))
				{
					while(!feof ($fp))
					{
						$buffer = fgets($fp,1024);
						#remove \r\n
						$buffer=str_replace(chr(10),"",$buffer);
						$buffer=str_replace(chr(13),"",$buffer);

						if(ereg($delimit,$buffer))
						{
							list($key,$val) = explode($delimit,$buffer);
							$this->data[$key] = $val;
						}
						elseif($buffer)
						{
							$this->data[] = $buffer;
						}
					}

					fclose($fp);
				}
			}
			elseif($type == "sql")
			{
				global $DB;

				$result = $DB->query($source);

				if($DB->num_rows($result) == 0)
				{
					/* There is no result */
					$DB->free_result($result);
				}
				elseif($DB->num_fields($result) == 1)
				{
					/* it only has 1 returned field so its like a normal array */
					while(list($element) = $DB->fetch_row($result))
					{
						$this->data[] = $element;
					}
				}
				elseif($DB->num_fields($result) == 2)
				{
					/* returned 2 fields */
					while (list($key, $val) = $DB->fetch_row($result))
					{
						$this->data[$key] = $val;
					}
				}
			}
			elseif($type == "dir")
			{
				$filter = $this->options["dir_filter"];
				if(!$dh = opendir($source)) {
					return false;
				}

				while (false != ($file = readdir($dh)))
				{ 
					if(preg_match("/$filter/",$file))
					{
						$this->data[$file] = $file;
					}
				}

				closedir($dh);
			}
		}
		else
		{
			$this->data = &$this->datasource;
		}
	}

	function valuesource_to_value()
	{
		if(is_array($this->valuesource))
		{
			foreach($this->valuesource as $val)
			{
				if($this->value)
					$this->value .= $this->options["delimit"];

				$this->value .= $val;
			}
		}
		elseif(preg_match("/^(sql|file|scalar|callback):(.*?)$/",$this->valuesource,$match))
		{
			$type = $match[1];
			$source = $match[2];

			if($type == "scalar")
			{
				// this is just a normal scalar var
				$this->value = $source;
			}
			elseif($type == "file")
			{
				// open file
				if($fp = fopen($source, "r"))
				{
					while(!feof ($fp))
					{
						//remove some crap
						$buffer = fgets($fp,1024);
						$buffer=str_replace(chr(10),"",$buffer);
						$buffer=str_replace(chr(13),"",$buffer);

						$this->value .= $buffer ."\n";
					}
					unset($buffer);
					fclose($fp);
				}
			}
			elseif($type == "sql")
			{
				global $DB;

				$result = $DB->query($source);

				$rows   = $DB->num_rows($result);
				$fields = $DB->num_fields($result);

				if($rows == 0)
				{
					$this->value = $this->options["default"];
					return false;
				}	
				while($rowdata = $DB->fetch_row($result))
				{
					for($x=0; $x != $fields; $x++)
					{
						if($this->value)
							$this->value .= $delimit;

						$this->value .= $rowdata[$x];
					}
				}

				$DB->free_result($result);
			}
			elseif($type == "callback")
			{
				$str = '$this->value=' . $source . ';';
				eval($str);
			}
		}
		else
		{
			$this->value = &$this->valuesource;
		}
	}	
}
