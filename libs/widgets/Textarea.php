<?
#CLASS:
#TYPE:
#OUT:
#CONSTRUCTOR:
#OPTION:
#.
class TextArea extends Widget
{
	function TextArea($name,$attrs=false,$options=false,$style=false,$valuesource=false)
	{
		$this->name 			= $name;
		$this->valuesource 		= $valuesource;
		$this->style 			= $style;

		$this->attrs["cols"]		=		40;
		$this->attrs["rows"]		=		10;
		$this->attrs["wrap"]		=		"soft"; // off, soft,hard

		$this->options["class"]    		= "Textarea";
		$this->options["use_font"] 		=  false;
		$this->options["write"]			= true;
		$this->options["write_access"]		= 0;
		$this->options["linebreaks"]		= 1;
		$this->options["tags"]			= false;
		$this->options["allow"]			= "<B><I><P><A><LI><OL><UL><EM><BR><TT><STRONG><BLOCKQUOTE><DIV><ECODE>";

		if(is_array($options))
			$this->options = array_merge($this->options,$options);

		if(is_array($attrs))
			$this->attrs = array_merge($this->attrs,$attrs);	
	}

	function draw($value=false)
	{
		$this->set_widget_value($value);

		if($this->options["linebreaks"])
		{
			$this->value = ereg_replace("<br>","\n",$this->value);
		}

		$html .= "<textarea name=\"" . $this->name . "\"" . aa_to_string($this->attrs) . " class=\"" . $this->options["class"] . "\">";
		$html .= $this->value;
		$html .= "</textarea>";

		return $html;
	}
}
?>
