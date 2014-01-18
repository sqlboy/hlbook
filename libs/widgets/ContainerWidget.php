<?
#CLASS:ContainerWidget
#TYPE:Other
#OUT:Encapulates a container object inside a widget object
#CONSTRUCTOR:ContainerWidget($name,$object=false,$options=false,$style=false)
#.
class ContainerWidget
{
	function ContainerWidget($name,$object=false,$options=false,$style=false)
	{
		$this->name 			=		$name;
		$this->object			= 		$object;
		$this->style 			=		$style;

		$this->options["use_font"] 	=  		false;

		if(is_array($options))
			$this->options = array_merge($this->options,$options);
	}

	function draw($value=false,$name_prefix = false)
	{
		ob_start();
		$this->object->draw();
		$html = ob_get_contents();
		ob_end_clean();
		return $html;
	}
}
?>
