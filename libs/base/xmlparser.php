<?
class XMLParser
{
	var $template;
	var $options;

	var $xparser;
	var $xbuffer;
	var $errors;

	var $TAGS;

	function parse_template()
	{
		if(!$this->open_template()) {
			return $this->errors;
		}

		$this->spawn_parser();
	}

	function open_template()
	{
		if(!file_exists($this->template)) {
			$this->errors[] = "XML Parser: Unable to load template";
			return false;
		}

		if(!$fp = fopen($this->template,"r")) {
			$this->errors[] = "Parser: Unable to load template";
			return false;
		}

		while (!feof ($fp)) 
		{
			$buffer = fgets($fp,512);
			if(!$buffer) { continue; }

			$this->xbuffer .= $buffer;

			#$buffer = str_replace("\t","",$buffer);
			#$buffer = str_replace("\n","",$buffer);
		}


		fclose ($fp);
		unset($buffer);
		return true;
	}

	function spawn_parser()
	{
		// create xml parser
		$this->xparser = xml_parser_create();
		xml_set_object($this->xparser, &$this);
		xml_set_element_handler($this->xparser, "tag_open", "tag_close");
		xml_set_character_data_handler($this->xparser, "cdata_handler");

		if (!xml_parse($this->xparser, $this->xbuffer)) {
			die(sprintf("XML error: %s at line %d",
					xml_error_string(xml_get_error_code($this->xparser)),
					xml_get_current_line_number($this->xparser)));
		}

		xml_parser_free($this->xparser);
	}	

	function print_tag($name,$attrs)
	{
		if($attrs["SCLASS"]) {
			list($class,$region) = split("\.",$attrs["SCLASS"]);
			unset($attrs["SCLASS"]);
		}

		$tag = "<$name" . tag_attr(strtolower($name),$class,$region,$attrs) . ">";

		print $tag;
	}	
}
