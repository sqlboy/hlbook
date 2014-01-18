<?
function register_all_containers()
{
	if($dh = opendir(LIB_PATH . "containers"))
	{
		while (false !== ($file = readdir($dh)))
		{ 
			if(preg_match("/\.php$/",$file))
			{
				require_once(LIB_PATH . "containers/" . $file);
			}
		}

		closedir($dh);
		return true;
	}

	return false;
}

function get_available_containers()
{
	if($dh = opendir(LIB_PATH . "containers"))
	{
		while (false !== ($file = readdir($dh)))
		{ 
			if(preg_match("/\.php$/",$file))
			{
				require_once(LIB_PATH . "containers/" . $file);
			}
		}

		closedir($dh);
		return true;
	}

	return false;
}

/*
Prototype
bool = container(string name)

Use
load a single container
*/

function register_container($name)
{
	if(!file_exists(LIB_PATH . "containers/" . $name . ".php"))
		return false;

	require_once(LIB_PATH . "containers/" . $name . ".php");
	return true;
}

/* Container Data Sources!!!

mixed container_data_source($object)

Use:
Process a container data source

Notes:
will return an array, or array of array's(list)

rtype=array

the array is a key value matrix.  with a query its the
column name as the key and the col value as the value.  With a
file you only want to use it with a delimited file with only 2 elements
like name=bob,address=20

*/


class Container extends XMLParser
{
	var $name;
	var $options;
	var $datasource;

	var $data = array();
	var $col;

	var $widgets;
	var $rows;

	var $footer;
	var $header;

	var $style;
	var $style_base;
	var $style_state;

	var $rowcolor;
	var $widget_count;
	var $allow_draw;
	var $rowcount;

	function init($name,$datasource)
	{
		$this->name 								= $name;						# the name of the container,make tis the same as the var name
		$this->datasource 						= $datasource;				# the datasource	

		$this->rowcolor							= 1;							# a bit for keeping track of multi row colors
		$this->widget_count						= 0;							# a count of the total number of widgets
		$this->errors								= false;						# an array of errors
		$this->widgets								= array();
		$this->rows									= array();
		$this->subrows								= array();

		/* 
			for parsing file datasources

			when a file: datasource is passed to the container use these options
			to make sure your file is parsed correctly.
		*/

		$this->options["file_delimit"] 		= 		"=";					// the file delimiter
		$this->options["file_maxline"] 		= 		"1024";				// max bytes to read on a single line
		$this->options["file_comment"] 		= 		"#";					// ignore line if it starts with this
		$this->options["file_maxfields"] 	= 		"2";					// max number of fields to read in
		$this->options["file_strip"]			=		array("\n","\r"," ");	// strip out these charcaters


		$this->options["sort_track"]			=		false;				// track sort settings between page loads
		$this->options["sql_orderby"]			=		"";					// col to order query results by
		$this->options["sql_sort"]				=		"";					// asc or desc (ascedning or decending)

		$this->options["acclevel"]				=		0;						// access flag needed to see container
		$this->options["auth"]					=		0;						// login status needed to see container
	}

	function init_style($style)
	{
		global $STYLE;

		/*
		What I'm doing here is copying the style for the container into the object
		from the main style object.  This is the base_style.  The base_style
		is then modified depending on a couple options.  So, we do all the mods to the
		base style and then copy the base style in the the "style" array whichs is
		what we actually use for output because since each widget can modify the style array, we
		fall back to the original base style style after each loop.
		*/

		#if the container is clean is basically a table with no borders or colors.  Good for overlaying
		#into other containers....we'll keep the fonts though

		$this->style_base = array();

		if($this->options["clean"])
		{
			$this->options["draw_module"] = false;

			$this->style_base["body"]["font"]["class"] 	= $STYLE->attr_val($this->options["class"] . ".body.font.class");
			$this->style_base["head"]["font"]["class"] 	= $STYLE->attr_val($this->options["class"] . ".head.font.class");
			$this->style_base["foot"]["font"]["class"] 	= $STYLE->attr_val($this->options["class"] . ".foot.font.class");
			$this->style_base["row"]["font"]["class"] 	= $STYLE->attr_val($this->options["class"] . ".row.font.class");
			$this->style_base["labels"]["font"]["class"] = $STYLE->attr_val($this->options["class"] . ".labels.font.class");
		}
		else
		{
			#if its not clean, copy the container's style into the object
			$this->style_base = $STYLE->export($this->options["class"]);
		}

		$this->style_merge_array_perm($style);
		$this->style_state = 1;

		#check the width to make sure it was merged
		if(!$this->attr_val("body.table.width"))
			$this->style("body.table.width","100%");

		#reset the style
		$this->style_reset();

		#merge the default module setting
		if($this->options["draw_module"] && $this->verify_module())
		{
			if($this->style["body"]["module"]["name"] && !$this->options["module"])
				$this->options["module"] = $this->style["body"]["module"]["name"];

			if($this->style["body"]["module"]["format"] && !$this->options["module_format"])	
				$this->options["module_format"] = $this->style["body"]["module"]["format"];		

			if(!$this->options["module"] || !$this->options["module_format"] 
					|| $this->options["clean"])
			{
				$this->options["draw_module"] = false;
			}
		}
		else
		{
			$this->options["draw_module"] = false;
		}

		unset($style);
	}

	#
	# The table sort can come from 3 different places..in this order 
	# The $_GET var overrides all other sort options
	# then the saved SESSION sort option
	# finally, we default to whatever is defined in the container options
	#
	# In order to allow multiple containers to be sorted on the same page
	# we need to include the name of the container in the GET url and in
	# the SESSION sort option.
	#


	function set_sql_orderby()
	{
		global $SESSION;

		if($_GET["orderby"]) {

			#if it doesn't ave our name on it, we ignore it.
			if(!preg_match("/^$this->name/",$_GET["orderby"])) {
				return false;
			}

			$orderby = $_GET["orderby"];
		}
		elseif($SESSION->exists($this->name . "_orderby") && $this->options["sort_track"])
			$orderby = $this->name . "_" . $SESSION->get($this->name . "_orderby");
		elseif($this->options["orderby"])
			$orderby = $this->name . "_" . $this->options["orderby"];
		else
			return false;

		if(preg_match("/^([a-z0-9_]+)_([a-z0-9_]+)([AD]{1})$/i",$orderby,$match))
		{
			$this->options["sql_orderby"] = " ORDER BY " . $match[2];

			if($match[3] == "A") {
				$this->options["sql_sort"] = " ASC";
			}
			elseif($match[3] == "D") {
				$this->options["sql_sort"] = " DESC";
			}


			if($this->options["sort_track"])
				$SESSION->set($this->name . "_orderby",$match[2] . $match[3]);	
		}
	}

	function draw()
	{
		global $OPTIONS;

		/* check to make sure the user has access to the container */
		if(!$this->has_access_to_container())
			return false;

		/* if an owner is set, the userid must match the ownerid */
		if(!$this->owns_this_container()) {
			error("You are unable to access this resource.");
			return false;
		}

		/* count the number of widgets */
		$this->count_widgets();

		/*
			some containers need to calculate their own td width depending on the number of
			widgets they are hozintally outputting.  Browser and hlist do this.  All body.td.widths
			will be set according to this function
		*/

		if($this->options["calcwidth"])
			$this->calculate_width();

		/*
			if a datasource exists, load it up
		*/

		if($this->datasource)
			$this->load_datasource();

		if($this->options["rtype"] != "multirow")
			$this->x_process_tags();

		$this->render();

		unset($this->col);
		unset($this->data);	
	}

	function verify_module()
	{
		if(file_exists(ATHEME_PATH . $this->style["body"]["module"]["name"]))
			return true;

		return false;

	}

	function draw_header($colspan=false)
	{
		if(!is_object($this->header))
			return true;

		if(!$colspan) { $colspan = $this->widget_count; }
		$colhtml = " colspan=\"" . $colspan . "\"";


		$this->style_merge_array($this->header->style);

		print $this->tag("tr","head");
		print "<td " . $this->tag_attr("td","head") . "$colhtml>";
		print $this->font("head") . $this->header->draw();

		if($this->options["multipage"])
		{
			global $SESSION;

			$pagecount	= ceil($this->rowcount/$this->options["numpp"]);	
			if(!$pagecount)
				$pagecount = 1;

			print " - Page: " . $SESSION->get($this->name . "_page") . "/" . $pagecount;
		}

		print "</font>";
		print "</td></tr>";
	}

	function draw_footer($colspan = false)
	{
		if(!is_object($this->footer))
			return true;

		if(!$colspan)
			$colspan = $this->widget_count;

		$this->style_merge_array($this->footer->style);

		print $this->tag("tr","foot") . "<td colspan=\"" . $colspan . "\"" . $this->tag_attr("td","foot") . ">";
		print $this->font("foot") . $this->footer->draw() . "</td></tr>\n";

		return true;
	}

	function set_page_view()
	{
		global $SESSION;

		if($this->options["multipage"] == false) { return false; }

		/* overwrite the page number if one is stored */
		if($_GET[$this->name . "_page"]) {
			$this->options["page"] = $_GET[$this->name . "_page"];
		}
		elseif($SESSION->exists($this->name . "_page"))
			$this->options["page"] = $SESSION->get($this->name . "_page");

		/* overwrite the number per page if one is stored */
		if($_GET["numpp"])
			$this->options["numpp"] = $_GET[$this->_name . "_numpp"];
		elseif($SESSION->exists($this->name . "_numpp"))
			$this->options["numpp"] = $SESSION->get($this->name . "_numpp");
		$this->pagecount	= ceil($this->rowcount/$this->options["numpp"]);	
		if(!$this->pagecount)
			$this->pagecount = 1;

		/* set page 1 if page is messed up */
		if($this->options["page"] < 1 || $this->options["page"] > $this->pagecount)
			$this->options["page"] = 1;

		$SESSION->set($this->name . "_page",$this->options["page"]);
		$this->rowstart = ($this->options["page"] - 1) * $this->options["numpp"];
		$this->query_limit = " LIMIT " . $this->rowstart . "," . $this->options["numpp"];
	}

	function load_datasource()
	{
		if(!$this->datasource)
		{
			return array();
		}
		if(is_array($this->datasource))
		{
			$this->data = &$this->datasource;
		}
		else
		{
			if(preg_match("/^sql:(.*?)$/s",$this->datasource,$match) || is_resource($this->datasource))
			{
				global $DB;

				if(is_resource($this->datasource))
				{
					$result = $this->datasource;
					$DB->reset($result);
				}
				else
				{

					#set the orderby and sorting options
					$this->set_sql_orderby();

					#need the total number of possible rows so we know how many pages
					#to put in the page selector.
					if($this->options["multipage"]) {

						if($this->options["query_count"])
						{
							$rc = $DB->query($this->options["query_count"]);
							list($this->rowcount) = $DB->fetch_row($rc);
							$DB->free_result($rc);
						}
						else
						{
							$this->rowcount = $this->options["rowcount"];
						}

						$this->set_page_view();	
					}

					$query = $match[1];
					$result = $DB->query($query . $this->options["sql_orderby"] . $this->options["sql_sort"] . $this->query_limit);
				}

				if($DB->num_rows($result) == 0)
				{
					$DB->free_result($result);
				}
				else
				{
					if($this->options["rtype"] == "row")
					{
						$this->data = $DB->fetch_assoc($result);
					}
					elseif($this->options["rtype"] == "keyval")
					{
						while(list($key,$value) = $DB->fetch_row($result))
						{
							$this->data[$key] = $value;
						}
					}
					elseif($this->options["rtype"] == "multirow")
					{
						while($row = $DB->fetch_assoc($result))
						{
							$this->data[] = $row;
						}
					}

					if(!$this->rowcount) {
						$this->rowcount = $DB->num_rows($result);
					}

					if($this->options["sql_freeresult"])
					{
						$DB->free_result($result);
					}
				}	
			}
			elseif(preg_match("/^file:(.*?)/",$datasource,$match))
			{
				$filename = $match[1];

				if(!file_exists($filename))
					return false;

				if(!$fp = fopen($filename, "r"))
					return false;

				while($buffer = fgets($fp, $this->options["file_maxline"]))
				{
					foreach($this->options["file_strip"] as $char)
					{
						if($char == $this->options["file_delimit"])
							continue;

						$buffer=str_replace($char,"",$buffer);
					}

					if(strlen($buffer) == 0)
						continue;

					if($this->options["rtype"] == "row")
					{
						list($key,$value) = split($this->options["file_delimit"],$buffer,$options["file_maxfields"]);
						$this->data[$key] = $value;
					}
					elseif($this->options["rtype"] == "multirow")
					{
						$row = split($options["file_delimit"],$buffer,$options["file_maxfields"]);
						$this->data[] = $row;
					}
				}

				fclose($fp);	
			}
		}

		/* if the user wants to add new fields from a multi row input add a new field,
			called new and handle it special within the particular object */
		if($this->options["draw_new"])
		{
			array_push($this->data,array($this->options["keycol"]=>"new"));	
		}	

		if($this->options["rtype"] != "multirow")
		{
			$this->col = &$this->data;
		}
	}

	function alt_class($region,$tag)
	{
		if($this->attr_val("$region.$tag.class") == $this->attr_val($region . "." . $tag . "1.class")) {
			$this->style("$region.$tag.class",$this->attr_val($region . "." . $tag . "2.class"));
			return;
		}

		$this->style("$region.$tag.class",$this->attr_val($region . "." . $tag . "1.class"));
	}

	function alt_active($region,$tag)
	{
		$this->tmpstyle("$region.$tag.class",$this->attr_val($region . "." . $tag . "active.class"));
		$this->style_state = 1;
	}

	function alt_inactive($region,$tag)
	{
		$this->style("$region.$tag.class",$this->attr_val($region . "." . $tag . ".class"));
	}

	/*
		This is some javascript that highlights the row when you mouse over it.
	*/

	function get_hover_color()
	{
		if($this->options["bghover"] == false)
			return "";

		#if we are alternating row color we have to make sure it goes back to the real one
		if($this->options["altrowcolor"])
		{
			$bgcolor_orig = $this->style["body"]["alt"]["bgcolor$this->rowcolor"];
		}
		else
		{
			$bgcolor_orig = $this->style["body"]["td"]["bgcolor"];
		}

		$bgcolor_hover = $this->style["body"]["alt"]["bghover"];  
		$bghover = " onmouseover=\"setPointerCol(this, '" . $bgcolor_hover . "')\" onmouseout=\"setPointerCol(this, '" . $bgcolor_orig . "')\"";

		return $bghover;
	}

	/*
	void process_tags(*widget)	

	loop through all properties in a widget object and replace
   datasource %[xxx] tags
	*/

	function process_tags(&$widget)
	{
		$widget_props = get_object_vars($widget);

		foreach($widget_props as $prop=>$value)
		{
			if(!$value)
				continue;

			unset($match);

			if(is_array($value))
			{
				$widget_array = &$widget->$prop;

				foreach($widget->$prop as $k=>$v)
				{
					if(!$v)
						continue;

					if(preg_match_all("/%\[(.*?)\]/",$v,$match))
					{
						for($i=0;$i != count($match[1]);$i++)
						{
							$index = $match[1][$i];
							$widget_array[$k] = preg_replace("/%\[($index)\]/",$this->col[$index],$widget_array[$k]);
						}
					}
				}
			}
			else
			{
				if(preg_match_all("/%\[(.*?)\]/",$widget->$prop,$match))
				{
					for($i=0;$i != count($match[1]);$i++)
					{
						$index = $match[1][$i];
						$widget->$prop = preg_replace("/%\[($index)\]/",$this->col[$index],$widget->$prop);
					}
				}
			}
		}
	}

	function x_process_tags()
	{
		if(!is_array($this->widgets)) { return true; }

		foreach ($this->widgets as $k=>$w) {

			#foreach actually makes a copy of the array, so your
			#not really looping through the real array, meaning if
			#your modding values within the array you need to do it
			#like this.  A for loop was workig but we can't gaurantee
			#that the widget is not in an associative array

			$widget = &$this->widgets[$k];
			$widget_props = get_object_vars($widget);

			#if(!is_array($widget_props)) { return 0; }

			foreach($widget_props as $prop=>$value)
			{
				if(!$value)
					continue;

				unset($match);

				if(is_array($value))
				{
					$widget_array = &$widget->$prop;

					foreach($widget->$prop as $k=>$v)
					{
						if(!$v)
							continue;

						if(preg_match_all("/%\[(.*?)\]/",$v,$match))
						{
							for($i=0;$i != count($match[1]);$i++)
							{
								$index = $match[1][$i];
								$widget_array[$k] = preg_replace("/%\[($index)\]/",$this->col[$index],$widget_array[$k]);
							}
						}
					}
				}
				else
				{
					if(preg_match_all("/%\[(.*?)\]/",$widget->$prop,$match))
					{
						for($i=0;$i != count($match[1]);$i++)
						{
							$index = $match[1][$i];
							$widget->$prop = preg_replace("/%\[$index\]/",$this->col[$index],$widget->$prop);
						}
					}
				}
			}
		}
	}

	/* verify the user has access to this container */
	function has_access_to_container()
	{
		global $AUTH;

		$ok = 0;

		if($AUTH->userdata["acclevel"] & $this->options["acclevel"] || $this->options["acclevel"] == 0)
			$ok++;

		if($AUTH->ok && $this->options["auth"] || $this->options["auth"] == false)
			$ok++;

		if($ok == 2)
			return true;

		return false;
	}

	/* verify that the user owns this containter if ownerid is set */
	function owns_this_container()
	{
		#if there is no owner, return true
		if(!$this->options["ownerid"])
			return true;

		if(userid() == $this->options["ownerid"])
			return true;

		return false;
	}

	/*
	void style_merge_array($style = array)

	Temporarily merges a widget's style settings into
			the container style	
			*/

	function style_merge_array($style)
	{
		global $STYLE;

		if(!is_array($style))
			return false;

		foreach ($style as $k=>$v)
		{
			list($region,$tag,$attr) = split("\.",$k,3);

			if(preg_match("/^#\[(.*?)\]$/",$v,$match))
			{
				list($_class,$_region,$_tag,$_attr) = split("\.",$match[1],4);
				$this->style[$region][$tag][$attr] = $STYLE->attr_val($_tag,$_attr,$_class,$_region);
			}
			else
				$this->style[$region][$tag][$attr] = $v;
		}

		$this->style_state = 1;
		return true;
	}

	/*
	void style_merge_array($style = array)

	Permanentely merge a style array into the
	container style array
	*/

	function style_merge_array_perm($style)
	{
		if(!is_array($style))
			return false;

		foreach ($style as $k=>$v)
		{
			list($region,$tag,$attr) = split("\.",$k,3);

			if(preg_match("/^#\[(.*?)\]$/",$v,$match))
			{
				list($_class,$_region,$_tag,$_attr) = split("\.",$match[1],4);
				$this->style_base[$region][$tag][$attr] = $STYLE->attr_val($_tag,$_attr,$_class,$_region);

			}
			else
			{
				$this->style_base[$region][$tag][$attr] = $v;	
			}
		}
		$this->style_state = 1;
		$this->style_reset();

		return true;
	}

	function style_reset()
	{
		if(!$this->style_state) { 
			return 0;
		}

		$this->style_state = 0;
		$this->style = $this->style_base;
	}

	function draw_rowid()
	{
		if($this->options["keycol"])
		{
			$keycol = $this->options["keycol"];
			echo "<input type=\"hidden\" name=\"rows[]\" value=\"" . $this->col[$keycol] . "\">";
		}
	}

	function calculate_width()
	{
		if($this->style["body"]["table"]["width"])
			$width = $this->style["body"]["table"]["width"];
		else $width = "100%";

		if($this->options["draw_cols"] > $this->widget_count)
			$divide = $this->options["draw_cols"];
		else
			$divide = $this->widget_count;

		if($divide < 1)
			return false;

		if(strstr($width,"%"))
		{
			$perc = "%";
			$tmp_width = "100";
		}
		else
			$tmp_width = $width;

		$new_width = sprintf("%d",$tmp_width / $divide);
		$this->style_merge_array_perm(array("body.td.width"=>$new_width . $perc));

		return true;
	}

	function option($string,$value)
	{
		$this->options[$string] = $value;
		return 1;
	}

	function tmpstyle($string,$value)
	{
		list($region,$tag,$attr) = split("\.",$string,3);
		$this->style[$region][$tag][$attr] = $value;
		return true;
	}

	function style($string,$value)
	{
		list($region,$tag,$attr) = split("\.",$string,3);
		$this->style_base[$region][$tag][$attr] = $value;
		$this->style[$region][$tag][$attr] = $value;
		return true;
	}

	function attr_val($string)
	{
		list($region,$tag,$attr) = split("\.",$string);
		return $this->style[$region][$tag][$attr];
	}

	function base_attr_val($string)
	{
		list($region,$tag,$attr) = split("\.",$string);

		if($this->style[$region][$tag][$attr] > 0 && $this->style[$region][$tag][$attr] != "none")
			return $this->base_style[$region][$tag][$attr];

		return false;
	}

	function tag_attr($tag,$region = "body")
	{
		if(is_array($this->style[$region][$tag]))
		{
			foreach($this->style[$region][$tag] as $key=>$value)
			{
				if($key == "none")
					continue;

				$html .= " " . $key . "=" . "\"" . $value . "\"";
			}
		}

		return $html;
	}

	function tag($tag,$region = "body")
	{
		$html = "<$tag";

		if(is_array($this->style[$region][$tag]))
		{
			foreach($this->style[$region][$tag] as $key=>$value)
			{
				if($key == "none")
					continue;

				$html .= " " . $key . "=" . "\"" . $value . "\"";
			}
		}

		$html .= ">";

		return $html;
	}

	function show_style()
	{
		print "<pre>";
		print_r($this->style_base);
		print "</pre>";
	}

	function font($region="body")
	{
		if($this->style[$region]["font"]["class"])
			$html = "<font class=\"" . $this->style[$region]["font"]["class"]. "\">";
		else
			$html = "<font class=\"" . $this->style["body"]["font"]["class"]. "\">";

		return $html;
	}

	function count_widgets()
	{
		$this->widget_count = count($this->widgets);
		return $this->widget_count;
	}

	function draw_module_head()
	{
		$format = $this->options["module_format"];
		$img_path = ATHEME_URL . $this->options["module"];

		#outer table is transparent/static
		print "<table align=\"" . $this->style["body"]["table"]["align"] . "\" width=\"" . $this->style["body"]["table"]["width"] . "\" cellspacing=0 cellpadding=0 border=0>\n";
		print "<tr>";

		#Top Left
		print "<td valign=top align=right>";
		print "<img src=\"" . $img_path . "/topleft." . $format . "\">";
		print "</td>";

		#Top Center
		print "<td valign=top width=\"100%\" background=\"" . $img_path . "/top." . $format . "\">";
		print "";
		print "</td>";

		#Top right
		print "<td valign=top align=left>";
		print "<img src=\"" . $img_path . "/topright." . $format . "\">";
		print "</td>";

		print "</tr><tr>";

		print "<td background=\"" . $img_path . "/left." . $format . "\">";
		print "";
		print "</td>";

		#Center
		print "<td width=\"100%\" background=\"" . $img_path . "/center." . $format . "\">";
		$this->style["body"]["table"]["width"] = "100%";	
	}

	function draw_module_foot()
	{
		$format = $this->options["module_format"];
		$img_path = ATHEME_URL . $this->options["module"];

		print "</td>";

		#Right
		print "<td background=\"" . $img_path . "/right." . $format . "\">";
		print "";
		print "</td>";

		print "</tr><tr>";

		#Botom Left
		print "<td valign=top align=right>";
		print "<img src=\"" . $img_path . "/bottomleft." . $format . "\">";
		print "</td>";

		#Bottom Center
		print "<td width=\"100%\" background=\"" . $img_path . "/bottom." . $format . "\">";
		print "";
		print "</td>";

		#Bottom right
		print "<td valign=top align=left>";
		print "<img src=\"" . $img_path . "/bottomright." . $format . "\">";
		print "</td>";

		print "</tr></table>";	

	}


}
