<?
#CLASS:BoolText
#TYPE:Text
#OUT:some text indicating a true of false value, defaults to "Yes", and "No"
#CONSTRUCTOR:BoolText($name,$link=false,$options=false,$style=false,$valuesource=false)
#.
Class BitwiseText extends Widget
{
	function BitwiseText($name,$link=false,$options=false,$style=false,$valuesource=false,$datasource=false)
	{
		$this->name 			= $name;
		$this->link 			= $link;
		$this->style 			= $style;
		$this->valuesource 		= $valuesource;
		$this->datasource 		= $datasource;

		$this->options["use_font"] 	= true;

		if(is_array($options))
			$this->options = array_merge($this->options,$options);
	}

	function draw($value = false)
	{
		$this->set_widget_value(&$value);
		$this->get_widget_datasource();

		if($this->link)
		{
			$html .= $this->mk_link();
		}

		if($this->data[0] == 0) {
			$html .= "No";
		}
		elseif($this->value & $this->data[0]){
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
?>
