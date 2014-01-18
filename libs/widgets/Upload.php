<?
#CLASS:
#TYPE:
#OUT:
#CONSTRUCTOR:
#OPTION:
#.
class Upload extends Widget
{
	function Upload($name,$attrs=false,$options=false,$style=false,$valuesource=false)
	{
		$this->name 				= $name;
		$this->valuesource 			= $valuesource;
		$this->style 				= $style;

		$this->attrs["size"]			= 32;

		$this->options["class"]    		= "Textbox";
		$this->options["use_font"] 		=  false;

		if(is_array($options))
			$this->options = array_merge($this->options,$options);

		if(is_array($attrs))
			$this->attrs = array_merge($this->attrs,$attrs);
	}

	function draw($value=false,$name_prefix="")
	{

		$html .= "<input type=\"file\" name=\"$name_prefix" . $this->name . "\" class=\"" . $this->options["class"] . "\"" . aa_to_string($this->attrs) . ">";

		return $html;
	}
}

?>
