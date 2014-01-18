<?
class Matrix extends Container
{
	function Matrix($name,$options=false,$style=false,$datasource=false)
	{
		$this->init($name,$datasource);

		$this->options["rtype"]						=		"row";
		$this->options["class"]						=		"Table";

		$this->options["draw_labels"]				=		false;
		$this->options["draw_module"]				=		false;
		$this->options["draw_altbg"]				=		true;

		$this->options["left_label"]				=		"Key";
		$this->options["right_label"]				=		"Value";
		$this->options["right_width"]				=		"50%";
		$this->options["left_width"]				=		"50%";

		if(is_array($options))
			$this->options = array_merge($this->options,$options);

		$this->init_style($style);	
	}

	function render()
	{
		if($this->options["draw_module"]) { $this->draw_module_head(); }
		print $this->tag("table");
		if(is_object($this->header)) { $this->draw_header(2); }

		// draw the rows if any.
		foreach($this->rows as $widget)
		{
			$this->style_merge_array($widget->style);
			print "<tr" . $this->tag_attr("tr","row") . ">";
			print "<td" . $this->tag_attr("td","row") . " colspan=\"2\">";

			if($widget->options["use_font"]) { print $this->font("row"); }
			print $widget->draw();

			print "</td></tr>";
			$this->style_reset();		
		}

		// draw col labels
		if($this->options["draw_labels"])
		{
			print $this->tag("tr","labels");

			print $this->tag("td","labels") . $this->font("labels") . $this->options["left_label"] . "</td>";
			print $this->tag("td","labels") . $this->font("labels") . $this->options["right_label"] . "</td>";

			print "</tr>";
		}

		foreach ($this->widgets as $widget)
		{
			$required = "";

			if($widget->options["hidden"]) {
				print $widget->draw($this->col[$widget->name]);
				continue;
			}

			$this->style_merge_array($widget->style);

			print $this->tag("tr");

			if($this->options["draw_altbg"]) { $this->alt_class("body","td"); }
			print "<td" . $this->tag_attr("td") . " width=\"" . $this->options["left_width"] . "\">";
			if($widget->options["required"])
				print "<font style=\"color: red; font-weight: bold;\">*&nbsp;</font>";

			print $this->font() . $required . $widget->options["title"] . "</font>";

			if($widget->options["notes"])
				print "<blockquote>" . $this->font("notes") . $widget->options["notes"] . "</font></blockquote>";
			print "</td>";

			#the right side
			print "<td" . $this->tag_attr("td") . " width=\"" . $this->options["right_width"] . "\">";

			if($widget->options["use_font"]) { print $this->font(); }
			print $widget->draw($this->col[$widget->name]) . "</td>";
			if($widget->options["use_font"]) { print "</font>"; }

			print "</tr>";

			$this->style_reset();
		}

		if($this->footer) { $this->draw_footer(2); }
		print "</table>\n";
		if($this->options["draw_module"]) { $this->draw_module_foot(); }
	}
}
?>
