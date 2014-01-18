<?
#CLASS:
#TYPE:
#OUT:
#CONSTRUCTOR:
#OPTION:
#.
class StatusImage extends Widget
{
	function StatusImage($name,$link=false,$img=false,$options=false,$style=false,$valuesource=false)
	{
		$this->name = $name;
		$this->link = $link;
		$this->style = $style;
		$this->valuesource = $valuesource;

		$this->img["border"]					= 	0;
		$this->img["hspace"]					= 	0;
		$this->img["align"]					= 	"center";

		$this->options["font"]				=	false;

		if(is_array($img))
			$this->img = array_merge($this->img,$img);

		if(is_array($options))
			$this->options = array_merge($this->options,$options);
	}

	function draw($value = false)
	{
		$this->set_widget_value(&$value);

		if($this->value == 2)
			$src="src=\"" . IMAGE_URL . "icons/toolbar/caution.png\"";
		elseif($this->value ==1)
			$src="src=\"" . IMAGE_URL . "icons/toolbar/ok.png\"";
		else
			$src="src=\"" . IMAGE_URL . "icons/toolbar/cancel.png\"";

		if($this->link)
			$html .= "<a href=\"" . $this->link . "\">";

		$html .= "<img " . $src . " " . aa_to_string($this->img) . ">";

		if($this->link)
			$html .= "</a>";

		return $html;
	}
}
?>
