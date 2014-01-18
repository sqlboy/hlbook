<?
#CLASS:Bargraoh
#TYPE:Image
#OUT:a horizontal image depicting a percentage/level of a value
#CONSTRUCTOR:Bargraph($name,$options=false,$style=false,$valuesource=false)
#OPTION:max|100|the maximum value.  used to calculate percentage
#OPTION:shave|.20|amount of length to shave off the line for formatting reasons
#.
class Bargraph extends Widget
{
	function Bargraph($name,$options=false,$style=false,$valuesource=false)
	{
		$this->name				= 	$name;
		$this->style			=	$style;
		$this->valuesource	=	$valuesource;

		$this->options["max"] 		= 	"100";
		$this->options["shave"]		=	.20;
		$this->options["use_font"] =	false;
		$this->options["show_perc"]= false;
		$this->options["show_value"] = true;

		if(is_array($options))
			$this->options = array_merge($this->options,$options);
	}

	function draw($value = false)
	{
		// bars are based on the % of the total.

		$this->set_widget_value(&$value);
		$perc = ($this->value / $this->options["max"]) * 100;
		$perc = sprintf("%01.0f",$perc);

		#perc is the real %

		if($this->options["shave"])
		{
			$width = ($perc - ($perc * $this->options["shave"]));
			$maxwidth = 100 - (100 * $this->options["shave"]);
		}
		else
		{
			$width = $perc;
			$maxwidth = "100";
		}


		$html .= "<img src=\"" . IMAGE_URL . "widgets/Bargraph/bar";

		if ($perc > 50)
			$html .= "6";
		elseif ($perc > 30)
			$html .= "5";
		elseif ($perc > 20)
			$html .= "4";
		elseif ($perc > 10)
			$html .= "3";
		elseif ($colval > 5)
			$html .= "2";
		else
			$html .= "1";

		$html .= ".gif\" width=\"";

		if ($perc < 1)
			$html .= "1%";
		elseif ($perc >= 100)
			$html .= "$maxwidth%";
		else
			$html .= $width . "%";

		$html .= "\" height=10 border=0 alt=\"$perc%\" title=\"$perc%\">";

		if($this->options["show_perc"]) {
			$html .= "&nbsp;&nbsp;" . $perc . "%";
		}

		if($this->options["show_value"]) {
			$html .= "&nbsp;&nbsp;" . $this->value;
		}


		return $html;
	}
}
?>
