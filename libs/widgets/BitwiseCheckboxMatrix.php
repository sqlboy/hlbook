<?
#CLASS:BitwiseCheckbox
#TYPE:Form
#OUT:a list of checkboxes with different interger values
#CONSTRUCTOR: BitwiseCheckbox($name,$options=false,$style=false,$valuesource=false,$datasource=false)
#OPTION:class|Checkbox|the checkbox's CSS class
#OPTION:search_type|bitwise|if used in a search object the type defaults to bitwise
#OPTION:regexpreset|int|the value of this widget is always an integer
#OPTION:write|true|this widget is writable
#.
class BitwiseCheckbox extends Widget
{
	function BitwiseCheckbox($name,$options=false,$style=false,$valuesource=false,$datasource=false)
	{
		$this->name					= $name;
		$this->valuesource 		= $valuesource;
		$this->datasource 		= $datasource;
		$this->style 				= $style;

		$this->options["use_font"] 		= true;
		$this->options["class"]    		= "Checkbox";
		$this->options["search_type"]		= "bitwise";
		$this->options["regexpreset"]		= "int";
		$this->options["write"]				= true;

		if(is_array($options))
			$this->options = array_merge($this->options,$options);
	}

	function draw($value=false,$name_prefix="")
	{
		$this->set_widget_value($value);
		$this->get_widget_datasource();

		foreach ($this->data as $key=>$val)
		{
			if(!$key)
				continue;

			if($this->value & $key)
				$checked = " checked";
			else
				$checked = "";

			$html .= "<input type=\"hidden\" name=\"" . $this->name . "_bitrows[]\" value=\"" . $key . "\">\n";
			$html .= "<input type=\"checkbox\" name=\"" . $this->name . "_" . $key . "\" value=\"1\" class=\"" . $this->options["class"] . "\"$checked>";
			$html .= " " . $val . "<br>";
		}

		return $html;
	}
}
?>
