<?
class Style
{
	var $name;
	var $STYLE;
	var $BSTYLE;

	function Style($name)
	{
		$this->name 		= $name;
		$this->load();
	}

	function load()
	{
		if(!$fp = fopen(THEME_PATH . $this->name . "/" . $this->name . ".style","r")) {
			error("Unable to open style: $this->name","Style Load Error");
			exit();
		}

		while($buffer = fgets($fp,1024))
		{
			if(ereg("^#",$buffer) || ereg("^;",$buffer))
				continue;

			if(strlen($buffer) < 2)
				continue;

			$buffer=str_replace("\n","",$buffer);
			$buffer=str_replace("\r","",$buffer);
			$buffer=str_replace(" ","",$buffer);
			$buffer=str_replace("\t","",$buffer);

			if(preg_match("/^(.*)\.(.*?)\.(.*?)\.(.*?)=(.*?);$/",$buffer,$match))
			{
				if($match[5] == "none") { $match[5] = '0'; }

				if($match[5] == "random") {
					$match[5] = $this->random_color(); 
				}

				$p_class = $match[1];
				$p_region = $match[2];
				$p_tag = $match[3];
				$p_att = $match[4];
				$p_val = $match[5];

				if($p_att  == "background")
					$p_val = THEME_URL . $this->name . "/backgrounds/" . $p_val;

				$this->BSTYLE[$p_class][$p_region][$p_tag][$p_att] = $p_val;
			}
		}
		fclose($fp);

		define("THEME",$this->name);
		define("ATHEME_URL",THEME_URL . $this->name . "/");
		define("ATHEME_PATH",THEME_PATH . $this->name . "/");
		$this->reset();

	}

	function reset()
	{
		$this->STYLE=$this->BSTYLE;
	}

	function export($class)
	{
		if(is_array($this->STYLE[$class])) {
			return $this->STYLE[$class];
		}

		return false;
	}

	function attr_val($string)
	{
		list($class,$region,$tag,$attr) = split("\.",$string,4);
		return $this->BSTYLE[$class][$region][$tag][$attr];
	}

	function tag($tag,$class,$region="body",$merge=false)
	{
		$tag = strtolower($tag);
		$tstyle = $this->BSTYLE[$class][$region][$tag];

		if(is_array($merge)) {

			foreach ($merge as $k=>$v)
			{
				$k = strtolower($k);
				$tstyle[$k] = $v;
			}
		}

		$html = "<$tag";

		if(is_array($tstyle))
		{
			foreach($tstyle as $key=>$value)
			{
				$html .= " " . $key . "=" . "\"" . $value . "\"";
			}
		}

		$html .= ">";

		return $html;
	}

	function tag_attr($tag,$class,$region="body",$merge=false)
	{
		$tag = strtolower($tag);
		$tstyle = $this->BSTYLE[$class][$region][$tag];

		if(is_array($merge))
		{
			foreach ($merge as $k=>$v)
			{
				$k = strtolower($k);
				$tstyle[$k] = $v;
			}
		}

		if(is_array($tstyle))
		{
			foreach($tstyle as $key=>$value)
			{
				$html .= " " . $key . "=" . "\"" . $value . "\"";
			}
		}

		return $html;
	}

	function font($class,$region="body")
	{
		$val = $this->BSTYLE[$class][$region]["font"]["class"];

		if($val)
			$html = "<font class=\"" . $val . "\">";

		return $html;
	}	

	function random_color()
	{
		$choices = array("a","b","c","d","e","f",1,2,3,4,5,6,7,8,9,0);

		for($x=0;$x<6;$x++)
		{
			$int = mt_rand(0,15);
			$color .= $choices[$int];
		}
		return "#" . $color;
	}


}

function tag_attr($tag,$class,$region = "body",$merge=false)
{
	global $STYLE;
	return $STYLE->tag_attr($tag,$class,$region,$merge);
}

function tag($tag,$class,$region="body",$merge=false)
{
	global $STYLE;
	return $STYLE->tag($tag,$class,$region,$merge);
}

function attr_val($info)
{
	global $STYLE;

	list($class,$region,$tag,$attr) = split("\.",$info);
	return $STYLE->attr_val($tag,$attr,$class,$region);
}

function font($class,$region="body")
{
	global $STYLE;
	return $STYLE->font($class,$region);
}

function p($options=false)
{
	global $STYLE;

	if(is_array($options))
	{
		foreach ($options as $k=>$v)
		{
			if($k == "class")
			{
				list($class,$region) = split(",",$v);
				$v = $STYLE->attr_val("font","class",$class,$region);
			}

			$str .= " " . $k . "=\"" . $v . "\"";
		}
	}

	$html = "<p$str>";

	return $html;
}

function endfont()
{
	return "</div>";
}

function br($count)
{
	for($x=0; $x != $count;$x++)
	{
		print "<br>";
	}
}
