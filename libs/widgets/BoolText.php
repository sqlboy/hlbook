<?
#CLASS:BoolText
#TYPE:Text
#OUT:some text indicating a true of false value, defaults to "Yes", and "No"
#CONSTRUCTOR:BoolText($name,$link=false,$options=false,$style=false,$valuesource=false)
#.
Class BoolText extends Widget
{
	function BoolText($name,$link=false,$options=false,$style=false,$valuesource=false)
	{
		$this->name 			= $name;
		$this->link 			= $link;
		$this->style 			= $style;
		$this->valuesource 		= $valuesource;

		$this->options["use_font"] 	= true;

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

		if($this->value > 0) {
			$html .= "Yes";
		}
		else {
			$html .= "No";
		}

		if($this->link)
			$html .= "</a>";

		return $html;
	}
}
