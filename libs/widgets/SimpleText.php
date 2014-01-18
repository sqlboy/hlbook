<?
#CLASS:
#TYPE:
#OUT:
#CONSTRUCTOR:
#OPTION:
#.
Class SimpleText
{
	var $name;
	var $link;
	var $value;
	var $style;
	var $options;

	function SimpleText($name,$value,$link=false,$style=false)
	{
		$this->name 				= $name;
		$this->value 				= $value;
		$this->link					= $link;
		$this->style 				= $style;

		$this->options["use_font"] 		= true;

		if(is_array($options))
			$this->options = array_merge($this->options,$options);
	}

	function draw($value = false)
	{
		if($this->link){
			$html .= "<a href=\"" . $this->link . "\">" . $this->value . "</a>";
			return $html;
		}

		$html .= $this->value;

		return $html;
	}
}
?>
