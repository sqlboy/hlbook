<?
#CLASS:
#TYPE:
#OUT:
#CONSTRUCTOR:
#OPTION:
#.
class Textbox extends Widget
{
	function Textbox($name,$attrs=false,$options=false,$style=false,$valuesource=false)
	{
		$this->name 			= $name;
		$this->valuesource 		= $valuesource;
		$this->style 			= $style;

		$this->attrs["size"]		= 25;
		$this->attrs["maxlength"]	= 25;

		$this->options["class"]    	= "Textbox";
		$this->options["use_font"] 	= false;
		$this->options["write"]		= true;
		$this->options["write_access"]	= 0;

		if(is_array($options))
			$this->options = array_merge($this->options,$options);

		if(is_array($attrs))
			$this->attrs = array_merge($this->attrs,$attrs);

	}

	function draw($value=false,$name_prefix="")
	{
		$this->set_widget_value($value);

		$html .= "<input type=\"text\" name=\"$name_prefix" . $this->name . "\" value=\"" . $this->value . "\" class=\"" . $this->options["class"] . "\"" . aa_to_string($this->attrs) . ">";

		return $html;
	}
}
?>
