<?
#CLASS:Checkbox
#TYPE:Form
#OUT:A textbox form widget
#CONSTRUCTOR: Checkbox($name,$options=false,$style=false,$valuesource=false)
#OPTION:class|Checkbox|the widget's CSS class
#OPTION:selected|1|if the widget value is equal to this, its checked
#OPTION:write|true|this widget is writable
#.
class Checkbox extends Widget
{
	function Checkbox($name,$options=false,$style=false,$valuesource=false)
	{
		$this->name 			=		$name;
		$this->valuesource	=		$valuesource;
		$this->style			=		$style;

		$this->options["use_font"] 	=		false;
		$this->options["class"]    	=		"Checkbox";
		$this->options["selected"]		=		1;
		$this->options["write"]			=		true;
		$this->options["set_val"]		= false;

		if(is_array($options))
			$this->options = array_merge($this->options,$options);
	}

	function draw($value=false,$name_prefix="")
	{
		$this->set_widget_value($value);

		if($this->value == $this->options["selected"])
			$selected = " checked";

		if($this->options["set_val"])
			$html .= "<input type=\"checkbox\" name=\"$name_prefix" . $this->name . "\" value=\"" . $this->value . "\" class=\"" . $this->options["class"] . "\"$selected>";
		else
			$html .= "<input type=\"checkbox\" name=\"$name_prefix" . $this->name . "\" value=\"1\" class=\"" . $this->options["class"] . "\"$selected>";

		return $html;
	}
}
?>
