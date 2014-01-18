<?
#CLASS:
#TYPE:
#OUT:
#CONSTRUCTOR:
#OPTION:
#.
class Select extends Widget
{
	function Select($name,$attrs=false,$options=false,$style=false,$valuesource=false,$datasource=false)
	{
		$this->name 				= $name;
		$this->datasource 		= $datasource;
		$this->valuesource 		= $valuesource;

		$this->attrs["size"]			= 1;

		$this->options["use_font"] 		= false;
		$this->options["class"]    		= "Select";
		$this->options["novalue"]    		= "";
		$this->options["nolabel"]			= "None";
		$this->options["write"]				= true;
		$this->options["write_access"]	= 0;

		if(is_array($attrs))
			$this->attrs = array_merge($this->attrs,$attrs);

		if(is_array($options))
			$this->options = array_merge($this->options,$options);
	}

	function draw($value = false,$name_prefix="")
	{
		$this->set_widget_value($value);
		$this->get_widget_datasource();

		$html .= "<select name=\"$name_prefix" . $this->name . "\" class=\"" . $this->options["class"] . "\"" . aa_to_string($this->attrs) . ">";

		if(!$this->options["required"])
			$html .= "<option value=\"" . $this->options["novalue"] . "\">" . $this->options["nolabel"] . "</option>\n";

		if(!is_array($this->data))
		{
			$html .= "</select>\n";
			return $html;
		}

		foreach ($this->data as $key=>$val)
		{
			if($this->value == $key)
				$selected = " selected";
			else
				$selected = "";

			if(!$key) { $key = "0"; }
			if(!$val) { $val = "0"; }

			$html .= "<option value=\"" . $key . "\" $selected>" . $val . "</option>\n";
		}	

		$html .= "</select>\n";

		return $html;		
	}
}
?>
