<?
#CLASS:
#TYPE:
#OUT:
#CONSTRUCTOR:
#OPTION:
#.
class HButton extends Widget
{
	function HButton($name,$link=false,$img=false,$options=false,$style=false,$valuesource=false)
	{
		$this->name 			= 	$name;
		$this->link 			= 	$link;
		$this->valuesource 		= 	$valuesource;
		$this->style 			= 	$style;

		$this->options["use_font"]	=	true;

		$this->img["border"]		= 	0;
		$this->img["hspace"]		= 	5;
		$this->img["title"]		=	$options["tooltip"];
		$this->img["align"]		= 	"center";

		if(is_array($img))
			$this->img = array_merge($this->img,$img);

		if(is_array($options))
			$this->options = array_merge($this->options,$options);
	}

	function draw($value = false)
	{
		$this->set_widget_value(&$value);

		if($this->link) {
			$html .= $this->mk_link();
		}

		$html .= "<img " . aa_to_string($this->img) . ">";

		$html .= $this->value;

		if($this->link)
			$html .= "</a>";

		return $html;
	}
}
?>
