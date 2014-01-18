<?
class Custom extends Container
{
	var $widget;
	var $cdata;
	var $sclass;

	function Custom($name,$options=false,$style=false,$datasource=false)
	{
		$this->init($name,$datasource);

		$this->options["rtype"]						=		"row";
		$this->options["class"]						=		"Custom";

		$this->options["template"]					=		false;

		if(is_array($options))
			$this->options = array_merge($this->options,$options);

		if(!$this->options["template"]) {
			print message("caution","No template for container: $this->name has been defined");
			return false;
		}

		$this->template = $this->options["template"];
		$this->init_style($style);	

		$this->TAGS = array("DATA","CONTAINER","WIDGET","SCLASS");

		return true;
	}

	function render()
	{
		$this->open_template();

		if($this->options["rtype"] == "multirow") {

			foreach($this->data as $this->col)
			{

				$this->spawn_parser();
			}
		}
		else
		{
			$this->spawn_parser();
		}
	}

	function tag_open($parser,$name,$attrs)
	{
		if(!in_array($name,$this->TAGS)) {

			if(!$attrs["SCLASS"] && $this->sclass) {
				$attrs["SCLASS"] = $this->sclass;
			}

			$this->print_tag($name,$attrs);
			return 1;
		}

		switch($name)
		{
			case "CONTAINER":
				break;

			case "DATA":
				$name = $attrs['NAME'];
				print $this->col[$name];
				break;

			case "WIDGET":
				$name = $attrs['NAME'];
				$widget = &$this->widgets[$name];
				print $widget->draw($this->col[$widget->name]);
				break;

			case "SCLASS":
				#default SCLASS
				$this->sclass = $attrs['NAME'];
				break;
		}
	}

	function tag_close($parser,$name)
	{
		if(!in_array($name,$this->TAGS)) {
			print "</$name>";
		}

		switch($name)
		{
			case "CONTAINER":
				break;
		}
	}

	function cdata_handler($parser,$data)
	{
		print $data;
	}

	function clear_cdata()
	{
		$this->cdata = "";
	}

	#
	# need to think about making either 1 or 2 standard
	# widget constructors
	function create_widget($attrs)
	{
		#all widgets should only require a name and a title, hopefully.
		#since we know the default constructor, we create the widget, then
		#use a set commands to update it.

		$type 	= $attrs["TYPE"];
		$name 	= $attrs["NAME"];

		$this->widget = new $type($name);
	}
}	
?>
