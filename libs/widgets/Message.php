<?
#CLASS:
#TYPE:
#OUT:
#CONSTRUCTOR:
#OPTION:
#.
Class Message
{
	function Message($name,$type,$message)
	{
		/* Default Options */
		$this->options["use_font"] 		= true;
		$this->options["class"]				= "Table";

		/* Constructzorz */
		$this->name = $name;
		$this->type = $type;
		$this->message = $message;
		$this->class = $class;
	}

	function draw($value = false)
	{
		$html .= message($this->type,$this->message);
		return $html;
	}
}
?>
