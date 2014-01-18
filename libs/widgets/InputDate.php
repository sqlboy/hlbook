<?
#CLASS:
#TYPE:
#OUT:
#CONSTRUCTOR:
#OPTION:
#.
class InputDate extends Widget
{
	function InputDate($name,$options=false,$style=false,$valuesource=false)
	{
		/* Default Options */
		$this->options["class"]    		= "Select";
		$this->options["use_font"] 		=  false;
		$this->options["write"]			=	true;
		$this->options["write_access"]		=	0;
		$this->options["set_midnight"]		=	true;
		$this->options["convert"]		=	true;
		$this->options["regexpreset"]		=	"int";

		$this->name 				= $name;
		$this->valuesource 			= $valuesource;
		$this->style			 	= $style;

		if(is_array($options))
			$this->options = array_merge($this->options,$options);
	}

	function draw($value=false,$name_prefix="")
	{

		$months = array("January","February","March","April","May","June","July","August","September","October","November","December");

		#looking for a timestamp
		$this->set_widget_value($value);

		#get the selected values
		if($this->value)
		{
			if($this->options["set_midnight"])
				$this->value = midnight($this->value);

			$day_selected = date("j",$this->value);
			$mon_selected = date("n",$this->value);
			$year_selected = date("Y",$this->value);
		}

		$html = "<select name=\"" . $this->name . "_month\" class=\"" . $this->options["class"] . "\">\n";
		for($i=1;$i<=12;$i++)
		{
			if($i == $mon_selected)
				$selected = " selected";
			else
				$selected = "";
			$html .= "<option value=\"$i\"$selected>" . $months[$i-1] . "</option>\n";
		}
		$html .= "</select> / ";


		$html .= "<select name=\"" . $this->name . "_day\" class=\"" . $this->options["class"] . "\">\n";
		for($i=1;$i<=31;$i++)
		{
			if($i == $day_selected)
				$selected = " selected";
			else
				$selected = "";

			$html .="<option value=\"$i\"$selected>$i</option>\n";
		}
		$html .= "</select> / ";


		$html .= "<select name=\"" . $this->name . "_year\" class=\"" . $this->options["class"] . "\">\n";
		$start_year = date("Y",time());
		$end_year = $start_year+3;
		for($i=$start_year;$i<$end_year;$i++)
		{
			if($i == $year_selected)
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
		$month = $_POST[$this->name . "_month"];
		$day = $_POST[$this->name . "_day"];
		$year = $_POST[$this->name . "_year"];

		$value = mktime(0,0,0,$month,$day,$year);

		if(is_numeric($value))
		{
			$_POST[$this->name] = $value;
			return $value;
		}
	}
}

?>
