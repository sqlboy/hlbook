<?
#CLASS:
#TYPE:
#OUT:
#CONSTRUCTOR:
#OPTION:
#.
class Radio extends Widget
{
	function Radio($name,$options=false,$style=false,$valuesource=false)
	{
		$this->name = $name;
		$this->style = style;
		$this->valuesource = $valuesource;

		$this->options["use_font"] 		= false;
		$this->options["required"]		= false;
		$this->options["class"]    		= "Radio";
		$this->options["write"]			= true;
		$this->options["write_access"]		= 0;

		if($this->options["label"]) { $this->options["use_font"] = true; }

		if(is_array($options))
			$this->options = array_merge($this->options,$options);
	}

	function draw($value,$name_prefix="")
	{
		$this->set_widget_value($value);
		$html = "<input type=\"radio\" name=\"" . $this->name . "\" value=\"" . $this->value . "\" class=\"" . $this->options["class"] . "\"$sel_on>";

		if($this->options["label"])
			$html .= " $this->title";

		return $html;
	}
}
