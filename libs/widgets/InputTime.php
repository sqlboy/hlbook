<?
#CLASS:
#TYPE:
#OUT:
#CONSTRUCTOR:
#OPTION:
#.
class InputTime extends Widget
{
	function InputTime($name,$options=false,$style=false,$valuesource=false)
	{
		/* Default Options */
		$this->options["class"]    	=	"Select";
		$this->options["use_font"] 	=	false;
		$this->options["write"]		=	true;
		$this->options["write_access"]	=	0;
		$this->options["convert"]	=	true;
		$this->options["regexpreset"]	=	"int";
		$this->options["basetime"]	=	time();

		$this->name 			= $name;
		$this->valuesource 		= $valuesource;
		$this->style 			= $style;

		if(is_array($options))
			$this->options = array_merge($this->options,$options);
	}

	function draw($value=false,$name_prefix="")
	{
		#looking for a timestamp
		$this->set_widget_value($value);

		#get the selected values
		if($this->value)
		{
			$hour_sel 	= gmdate("H",$this->value + toffset());
			$min_sel 	= gmdate("i",$this->value + toffset());
			$sec_sel 	= gmdate("s",$this->value + toffset());
		}

		$html = "<select name=\"" . $this->name . "_hour\" class=\"" . $this->options["class"] . "\">\n";
		for($i=0;$i<=23;$i++)
		{
			if($i == $hour_sel)
				$selected = " selected";
			else
				$selected = "";

			$html .="<option value=\"$i\"$selected>$i</option>\n";
		}
		$html .= "</select>: ";


		$html .= "<select name=\"" . $this->name . "_min\" class=\"" . $this->options["class"] . "\">\n";
		for($i=0;$i<=59;$i++)
		{
			if($i == $min_sel)
				$selected = " selected";
			else
				$selected = "";

			$html .="<option value=\"$i\"$selected>$i</option>\n";
		}
		$html .= "</select>:";


		$html .= "<select name=\"" . $this->name . "_sec\" class=\"" . $this->options["class"] . "\">\n";
		for($i=0;$i<=59;$i++)
		{
			if($i == $sec_sel)
				$selected = " selected";
			else
				$selected = "";

			$html .="<option value=\"$i\"$selected>$i</option>\n";
		}
		$html .= "</select>";

		return $html;
	}

	function convert()
	{
		$hour = $_POST[$this->name . "_hour"];
		$min = $_POST[$this->name . "_min"];
		$sec = $_POST[$this->name . "_sec"];
		list($day,$month,$year) = split(",",date("j,n,Y",$this->options["basetime"]));

		$value = mktime($hour,$min,$sec,$month,$day,$year);

		if(is_numeric($value))
		{
			$_POST[$this->name] = $value;
			return $value;
		}
	}
}

?>
