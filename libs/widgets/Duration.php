<?
#CLASS:Duration
#TYPE:Text
#OUT:An amount of time in D M H S format
#CONSTRUCTOR:Duration($name,$link=false,$options=false,$style=false,$valuesource=false)
#OPTION:
#.
Class Duration extends Widget
{
	function Duration($name,$link=false,$options=false,$style=false,$valuesource=false)
	{
		$this->name 			= 	$name;
		$this->title 			= 	$title;
		$this->link 			= 	$link;
		$this->style 			= 	$style;
		$this->valuesource 	= 	$valuesource;

		$this->options["use_font"] 	= true;
		$this->options["start"]		= 0;
		$this->options["mode"]		= 1;
		$this->options["timezone"]	= 0;

		if(is_array($options))
			$this->options = array_merge($this->options,$options);

		if($this->options["mode"] == 2)
			$this->options["timezone"]			= 0;	
	}

	function draw($value = false)
	{
		$this->set_widget_value(&$value);

		if($this->link) {
			$html.=$this->mk_link();
		}

		#the value should be subtraced from current time
		if($this->options["mode"] == 2)
		{
			if($this->options["start"])
			{
				$time = $this->options["start"] - $this->value;
			}
			else
			{
				$time = time() - $this->value;
			}
		}
		else { $time = $this->value; } #the value is the offset

		$sec 	= intval($time % 60);
		$min  = intval(($time % 3600)/60);
		$hour = intval(($time % 86400) / 3600);
		$days = intval($time / 86400);

		if(!min)
			$min = 0;

		if(!hour)
			$hour = 0;

		if(!$this->value)
			$html .= "Never";
		else
			$html .= $days . "d " . $hour . "h " . $min . "m " . $sec . "s";

		if($this->link)
			$html .= "</a>";

		return $html;
	}
}
?>
