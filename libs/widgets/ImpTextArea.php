<?
#CLASS:
#TYPE:
#OUT:
#CONSTRUCTOR:
#OPTION:
#.
class ImpTextArea extends Widget
{
	function ImpTextArea($name,$attrs=false,$options=false,$style=false,$valuesource=false)
	{
		/* Default Attributes */
		$this->attrs["cols"]					=		40;
		$this->attrs["rows"]					=		10;
		$this->attrs["wrap"]					=		"soft"; // off, soft,hard

		/* Default Options */
		$this->options["class"]    		= "Textarea";
		$this->options["use_font"] 		=  false;
		$this->options["required"]			=	false;
		$this->options["write"]				=	true;
		$this->options["write_access"]	=	0;
		$this->options["linebreaks"]		=	true;
		$this->options["tags"]				= false;
		$this->options["allowtags"]		= "<B><I><P><A><LI><OL><UL><EM><BR><TT><STRONG><BLOCKQUOTE><DIV><ECODE>";

		$this->name 							= $name;
		$this->title 							= $title;
		$this->style 							= $style;
		$this->valuesource 					= $valuesource;

		if(is_array($options))
			$this->options = array_merge($this->options,$options);

		if(is_array($attrs))
			$this->attrs = array_merge($this->attrs,$attrs);	

		$this->build_notes();	
	}

	function draw($value=false)
	{
		$this->set_widget_value($value);

		$html .= "<textarea name=\"" . $this->name . "\"" . aa_to_string($this->attrs) . " class=\"" . $this->options["class"] . "\">";
		$html .= $this->value;
		$html .= "</textarea>\n";

		return $html;
	}

	function build_notes()
	{
		$notes = $this->options["allow"];
		$char = 0;
		$buffer = "";
		$out = "";
		$count = 0;

		while($char != strlen($this->options["allow"]))
		{
			#find end of tag
			if($notes[$char] == ">")
			{
				$out .= htmlentities($buffer . ">");
				$buffer = "";
				$count++;

				if($count >= 3) {
					$out .= "<br>";
					$count = 0;
				}
			}
			else { $buffer .= $notes[$char]; }

			$char++;
		}

		$this->options["notes"] = "Allowed Tags: <br>" . $out;
	}
}
?>
