<?
#CLASS:
#TYPE:
#OUT:
#CONSTRUCTOR:
#OPTION:
#.
Class Nav extends Widget
{
	function Nav($name,$links,$img=false,$options=false,$style=false)
	{
		/* Default Options */
		$this->options["use_font"] 		= true;
		$this->options["delimit"]			= " :: ";
		$this->options["prefix"]			=	"";

		if(is_array($img))
		{
			$this->img["border"]					= 	0;
			$this->img["hspace"]					= 	5;
			$this->img["align"]					= 	"center";

			$this->img = array_merge($this->img,$img);
		}
		else
			$this->img = false;

		/* Constructzorz */
		$this->name 							= $name;
		$this->title							= $title;
		$this->links 							= $links;
		$this->style 							= $style;

		if(is_array($options))
			$this->options = array_merge($this->options,$options);

		if(is_array($style))
			$this->style = array_merge($this->style,$style);	
	}

	function draw($value = false)
	{
		$count = 0;

		if($this->img)
			$html .= "<img " . aa_to_string($this->img) . ">";

		$html .= $this->options["prefix"];

		foreach ($this->links as $title=>$url)
		{
			if($count > 0)
				$html .= $this->options["delimit"];

			$count++;

			if($url)
				$html .= "<a href=\"" . $url . "\">";

			$html .= $title;

			if($url)
				$html .= "</a>";
		}

		return $html;
	}
}
?>
