<?
#CLASS:
#TYPE:
#OUT:
#CONSTRUCTOR:
#OPTION:
#.
class Hidden extends Widget
{	
	function Hidden($name,$options=false,$style=false,$valuesource=false)
	{
		$this->name 				=	$name;
		$this->valuesource 			= 	$valuesource;
		$this->style 				= 	$style;

		$this->options["use_font"] 		=  	false;
		$this->options["required"]		=	false;
		$this->options["write"]			=	true;
		$this->options["write_access"]		=	0;
		$this->options["hidden"]		=	true;

		if(is_array($options))
			$this->options = array_merge($this->options,$options);
	}

	function draw($value=false,$name_prefix="")
	{
		$this->set_widget_value($value);

		$html .= "<input type=\"hidden\" name=\"$name_prefix" . $this->name . "\" value=\"" . $this->value . "\">";

		return $html;
	}
}
?>
