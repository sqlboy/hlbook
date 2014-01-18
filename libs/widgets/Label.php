<?
#CLASS:
#TYPE:
#OUT:
#CONSTRUCTOR:
#OPTION:
#.
Class Label extends Widget
{
	function Label($name,$link=false,$options=false,$style=false,$valuesource=false)
	{
		$this->name 			= $name;
		$this->link 			= $link;
		$this->style 			= $style;
		$this->valuesource 		= $valuesource;

		$this->options["use_font"] 	= true;
		$this->options["class"]		= "Label";

		if(is_array($options))
			$this->options = array_merge($this->options,$options);
	}

	function draw($value = false)
	{
		$this->set_widget_value(&$value);

		if($this->link)
		{
			$html .= $this->mk_link();
		}

		$html .= $this->value;

		if($this->link)
			$html .= "</a>";

		return $html;
	}
}
?>
