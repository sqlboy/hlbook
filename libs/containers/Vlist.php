<?
class VList extends Container
{
	function VList($name,$options=false,$style=false,$datasource=false)
	{
		$this->init($name,$datasource);

		$this->options["class"]						=		"Menu";
		$this->options["rtype"]						=		"row";

		$this->options["draw_module"]				=		true;
		$this->options["append"]					=		false;

		if(is_array($options))
			$this->options = array_merge($this->options,$options);

		$this->init_style($style);
	}

	function render()
	{
		if($this->options["draw_module"]) { $this->draw_module_head(false); }
		print "\n" . $this->tag("table") . "\n";
		if($this->header) { $this->draw_header(); }

		if($this->options["rtype"] == "multirow")
		{
			for($i=0;$i != count($this->data);$i++)
			{
				$this->col = $this->data[$i];
				$do_col = true;

				foreach($this->widgets as $widget)
				{
					$this->process_tags(&$widget);

					if($widget->options["hidden"]) {
						print $widget->draw($this->col[$widget->name]);
						continue;
					}

					if($do_col == true) {
						$this->style_merge_array($widget->style);
						print $this->tag("tr"); 
						$this->alt_rowcolor();
						print $this->tag("td");
						$do_col = 0; 
					}

					if($widget->options["use_font"]) { print $this->font(); }
					print $widget->draw($this->col[$widget->name]);
					if($widget->options["use_font"]) { print "</font>"; }
				}

				if($widget->options["notes"]) { 
					print "</div>" . $this->font("notes") . "<blockquote>" 
					. $widget->options["notes"] . "</blockquote></div>";
				}

				print "</td></tr>";
			}	
		}
		else
		{
			foreach ($this->widgets as $widget)
			{
				$this->style_merge_array($widget->style);

				if($widget->options["hidden"])
				{
					print $widget->draw($this->col[$widget->name]);
					continue;
				}

				print $this->tag("tr");
				$this->alt_rowcolor;
				print $this->tag("td");

				if($widget->options["use_font"]) { print $this->font(); }
				print $widget->draw($this->col[$widget->name]);
				print "</td></tr>";

				$this->style_reset();	
			}

			if($this->options["append"] == false)
			{
				$this->close_table();
			}
		}	
	}

	function close_table()
	{
		if($this->footer)
			$this->draw_footer();

		print "</table>\n";

		if($this->options["draw_module"])
			$this->draw_module_foot();
	}
}
?>
