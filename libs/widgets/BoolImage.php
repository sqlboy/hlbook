<?
#CLASS:BoolImage
#TYPE:Image
#OUT:an image indicating a true of false value
#CONSTRUCTOR:BoolImage($name,$link=false,$attrs=false,$options=false,$style=false,$valuesource=false)
#OPTION:image_url|IMAGE_URL . "icons/toolbar/"|the url location of the images
#OPTION:true_image|true.png|show this image if the widget value > 0
#OPTION:false_image|false.png|show this image if the widget value <= 0
#.
class BoolImage extends Widget
{
	function BoolImage($name,$link=false,$attrs=false,$options=false,$style=false,$valuesource=false)
	{
		$this->name				=	$name;
		$this->link				=	$link;
		$this->style			=	$style;
		$this->valuesource	=	$valuesource;

		$this->attrs["border"]		= 	0;
		$this->attrs["hspace"]		= 	0;
		$this->attrs["align"]		= 	"center";

		$this->options["use_font"]		=	false;
		$this->options["reverse"]			=	false;
		$this->options["image_url"] = IMAGE_URL . "icons/toolbar/";
		$this->options["true_image"]	= "true.png";
		$this->options["false_image"]	= "false.png";

		if(is_array($attrs))
			$this->attrs = array_merge($this->attrs,$attrs);

		if(is_array($options))
			$this->options = array_merge($this->options,$options);
	}

	function draw($value = false)
	{
		$this->set_widget_value(&$value);

		if($this->options["reverse"] == true) {

			if($this->value) {
				$image = $this->options["false_image"];
			}
			else {
				$image = $this->options["true_image"];
			}
		}
		else
		{
			if($this->value) {
				$image = $this->options["true_image"];
			}
			else {
				$image = $this->options["false_image"];
			}
		}

		$src="src=\"" . $this->options["image_url"] . $image . "\"";

		if($this->link)
			$html .= $this->mk_link();

		$html .= "<img " . $src . " " . aa_to_string($this->attrs) . ">";

		if($this->link)
			$html .= "</a>";

		return $html;
	}
}
?>
