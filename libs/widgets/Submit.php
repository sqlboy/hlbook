<?
#CLASS:
#TYPE:
#OUT:
#CONSTRUCTOR:
#OPTION:
#.
class Submit extends Widget
{
	function Submit($name,$options=false,$style=false,$valuesource=false)
	{
		$this->name 				= $name;
		$this->style 				= $style;
		$this->valuesource			= $valuesource;

		$this->options["class"]    		= "Submit";
		$this->options["use_font"] 		=  false;

		if(is_array($options))
			$this->options = array_merge($this->options,$options);
		if(is_array($attrs))
			$this->attrs = array_merge($this->attrs,$attrs);
	}

	function draw($value=false)
	{
		$this->set_widget_value($value);

		$html .= "<input type=\"submit\" name=\"" . $this->name . "\" value=\"" . $this->value . "\" class=\"" . $this->options["class"] . "\">";

		return $html;
	}
}

?>
