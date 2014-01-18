<?
#CLASS:
#TYPE:
#OUT:
#CONSTRUCTOR:
#OPTION:
#.
class RadioImageSelect extends Widget
{
	function RadioImageSelect($name,$options=false,$style=false,$valuesource=false,$datasource=false)
	{
		$this->name 				= $name;
		$this->style				= $style;
		$this->datasource 			= $datasource;
		$this->valuesource 			= $valuesource;

		$this->options["use_font"] 		= false;
		$this->options["required"]		= false;
		$this->options["class"]    		= "Radio";
		$this->options["novalue"]    		= "";
		$this->options["nolabel"]		= "None";
		$this->options["write"]			= true;
		$this->options["write_access"]		= 0;
		$this->options["cols"]			= 6;
		$this->options["url"]			= "";

		if(is_array($options))
			$this->options = array_merge($this->options,$options);
	}

	function draw($value = false,$name_prefix="")
	{
		$this->set_widget_value($value);
		$this->get_widget_datasource();

		$colwidth = sprintf("%d",100 / $this->options["cols"]);
		$cols = 1;

		$html = "<table width=\"100%\" cellspacing=0 cellpadding=0 border=0>";

		foreach($this->data as $v)
		{
			if($cols == 1)
				$html .= "<tr>";

			if($v == $this->value)
				$sel_on = " CHECKED";
			else
				$sel_on = "";

			$html .= "<td width=\"" . $colwidth . "%\" align=\"center\">";
			$html .= "<img src=\"" . $this->options["url"] . "$v\">";
			$html .= "<br><input type=\"radio\" name=\"" . $this->name . "\" value=\"" . $v . "\" class=\"" . $this->options["class"] . "\"$sel_on>";
			$html .= "</td>";

			if($cols == $this->options["cols"])
			{
				$html .= "</tr>";
				$cols = 1;
			}
			else
				$cols++;
		}

		$html .= "</tr></table>";

		return $html;
	}
}
