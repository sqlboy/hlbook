<?
#CLASS:
#TYPE:
#OUT:
#CONSTRUCTOR:
#OPTION:
#.
class Image extends Widget
{
	function Image($name,$link=false,$img=false,$options=false,$style=false,$valuesource=false)
	{
		$this->name 			= 	$name;
		$this->link 			= 	$link;
		$this->style 			= 	$style;
		$this->valuesource 		= 	$valuesource;

		$this->img["border"]		= 	0;
		$this->img["hspace"]		= 	0;
		$this->img["align"]		= 	"center";

		$this->options["use_font"]		=	false;
		$this->options["link_target"]		=	false;

		if(is_array($img))
			$this->img = array_merge($this->img,$img);

		if(is_array($options))
			$this->options = array_merge($this->options,$options);
	}

	function draw($value = false)
	{
		if($this->link) {
			$html .= $this->mk_link();
		}

		$html .= "<img " . aa_to_string($this->img) . ">";

		if($this->link)
			$html .= "</a>";

		return $html;
	}
}
?>
