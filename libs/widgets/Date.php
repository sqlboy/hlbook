<?
#CLASS:Date
#TYPE:Text
#OUT:A formatted date
#CONSTRUCTOR:Date($name,$link=false,$options=false,$style=false,$valuesource=false)
#OPTION:format|l, F jS Y H:i A|a php date string
#OPTION:timezone|false|use the user timezone offset
#.
Class Date extends Widget
{
	function Date($name,$link=false,$options=false,$style=false,$valuesource=false)
	{
		$this->name 			= 	$name;
		$this->link 			= 	$link;
		$this->style 			= 	$style;
		$this->valuesource 	= 	$valuesource;

		$this->options["use_font"] 	= 	true;
		$this->options["format"]		=	"l, F jS Y H:i A";
		$this->options["timezone"]		= 	0;

		if(is_array($options))
			$this->options = array_merge($this->options,$options);
	}

	function draw($value = false)
	{
		$this->set_widget_value(&$value);

		if($this->link) {
			$html .= $this->mk_link();
		}

		if($this->value > 0)
		{
			if($this->options["timezone"] == 1)
				$html .= gmdate($this->options["format"],$this->value);
			else
				$html .= date($this->options["format"],$this->value);
		}
		else
		{
			$html .= "None";
		}

		if($this->link)
			$html .= "</a>";

		return $html;
	}
}
?>
