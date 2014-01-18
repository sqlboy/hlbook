<?
#CLASS:Bargraph2
#TYPE:Image
#OUT:a horizontal image depicting a percentage/level of a value using a single image
#CONSTRUCTOR:Bargraph($name,$options=false,$style=false,$valuesource=false)
#OPTION:max|100|the maximum value.  used to calculate percentage
#OPTION:shave|.20|amount of length to shave off the line for formatting reasons
#.
class Bargraph2 extends Widget
{
	function Bargraph2($name,$attrs=false,$options=false,$style=false,$valuesource=false)
	{
		$this->name				= 	$name;
		$this->style			=	$style;
		$this->valuesource	=	$valuesource;

		$this->options["max"] 		= 	"100";
		$this->options["shave"]		=	.20;
		$this->options["use_font"] =	false;
		$this->options["show_perc"]=  false;
		$this->options["show_value"] = true;

		$this->attrs["src"]			= IMAGE_URL . "widgets/Bargraph2/red.png";

		if(is_array($options))
			$this->options = array_merge($this->options,$options);

		if(is_array($attrs))
			$this->attrs = array_merge($this->attrs,$attrs);
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
			$this->attrs["width"] = $perc - ($perc * $this->options["shave"]);
			$maxwidth = 100 - (100 * $this->options["shave"]);
		}
		else
		{
			$this->attrs["width"] = $perc;
			$maxwidth = "100";
		}

		if($this->link) {
			$html .= $this->mk_link();
		}

		$html .= "<img " . aa_to_string($this->attrs) . ">";

		if($this->link)
			$html .= "</a>";

		if($this->options["show_perc"]) {
			$html .= "&nbsp;&nbsp;" . $perc . "%";
		}

		if($this->options["show_value"]) {
			$html .=  "&nbsp;&nbsp;" . $this->value . "%";
		}
		return $html;
	}
}
?>
