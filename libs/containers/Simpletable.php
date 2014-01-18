<?
class SimpleTable extends Container
{
	var $rows = array();

	function SimpleTable($name,$options=false,$style=false,$datasource=false)
	{
		$this->init($name,$datasource);

		$this->options["class"]					=		"Table";

		$this->options["draw_labels"]			=		true;
		$this->options["draw_module"]			=		false;

		$this->options["sort_track"]			=		false;				// track sort settings between page loads
		$this->options["sql_orderby"]			=		"";					// col to order query results by
		$this->options["sql_sort"]				=		"";					// asc or desc (ascedning or decending)

		$this->options["rtype"]					=		"multirow";
		$this->options["rids"]					=		true;
		$this->options["keycol"]				=		false;;

		if(is_array($options))
			$this->options = array_merge($this->options,$options);

		$this->init_style($style);	
	}

	function render()
	{
		if($this->options["draw_module"]) {  $this->draw_module_head();}
		print "\n" . $this->tag("table") . "\n";
		if($this->header) { $this->draw_header($this->widget_count); }

		foreach($this->rows as $widget)
		{
			$this->style_merge_array($widget->style);

			print $this->tag("tr","row");
			print "<td " . $this->tag_attr("td","row") . " colspan=\"" . $this->widget_count . "\">";

			if($widget->options["use_font"]) { print $this->font(); }

			print $widget->draw();
			print "</td></tr>";

			$this->style_reset();		
		}

		if($this->options["draw_labels"])
		{
			if(!$this->options["baseurl"])
				$this->options["baseurl"] = TASK_URL;

			print $this->tag("tr","labels");

			foreach($this->widgets as $widget)
			{
				if($widget->options["hidden"]) { continue; }
				$this->style_merge_array($widget->style);

				print $this->tag("td","labels");
				print $this->font("labels") . $widget->options["title"];

				if($widget->options["sort"] && $this->options["baseurl"])
				{
					print "&nbsp;";
					print "<a href=\"" . $this->options["baseurl"] . "&orderby=" . $this->name . "_" . $widget->name . "A\"><img src=\"" . IMAGE_URL . "icons/toolbar/sort_asc.png\" border=0></a>";
					print "<a href=\"" . $this->options["baseurl"] . "&orderby=" . $this->name . "_" . $widget->name . "D\"><img src=\"" . IMAGE_URL . "icons/toolbar/sort_desc.png\" border=0></a>";
				}

				print "</td>";

				/* reset the style */
				if(is_array($widget->style))
					$this->style_reset();		
			}
		}

		// data rows
		foreach($this->data as $this->col)
		{
			if($this->options["rids"] && $this->options["keycol"])
			{
				$keycol = $this->options["keycol"];
				print "<input type=\"hidden\" name=\"rows[]\" value=\"" . $this->col[$keycol] . "\">";
			}

			print $this->tag("tr");

			// columns
			foreach ($this->widgets as $widget)
			{
				$this->process_tags(&$widget);

				if($widget->options["hidden"]) {
					print $widget->draw($this->col[$widget->name],$this->col[$keycol] . "_");
					continue;
				}

				$this->style_merge_array($widget->style);

				/* DRAW */
				print $this->tag("td");
				if($widget->options["use_font"]) { print $this->font(); }

				if($widget->options["exempt_new"] && $this->col[$keycol]=="new")
					print "&nbsp;";
				else
					print $widget->draw($this->col[$widget->name],$this->col[$keycol] . "_");

				print "</td>";
				$this->style_reset();	
			}

			print "</tr>";
		}

		if($this->footer) { $this->draw_footer(); }
		print "</table>";

		if($this->options["draw_module"]) { $this->draw_module_foot();	}
	}
}
?>
