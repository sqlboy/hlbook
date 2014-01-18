<?
class Browser extends Container
{
	function Browser($name,$options=false,$style=false,$datasource=false)
	{
		$this->init($name,$datasource);

		$this->options["rtype"]						=		"row";
		$this->options["class"]						=		"Browser";

		$this->options["draw_cols"]				=		4;
		$this->options["draw_module"]				=		true;
		$this->options["calcwidth"]				=		true;

		if(is_array($options))
			$this->options = array_merge($this->options,$options);

		$this->init_style($style);	
	}

	function render()
	{
		if($this->options["draw_module"])
			$this->draw_module_head();

		print "\n" . $this->tag("table") . "\n";

		if($this->header)
			$this->draw_header($this->options["draw_cols"]);

		// static rows
		foreach($this->rows as $widget)
		{
			if(is_array($widget->style))
				$this->style_merge_array($widget->style);

			if($this->options["alt_rowcolor"])
				$this->alt_rowcolor();

			print "<tr " . $this->tag_attr("tr") . ">";

			print "<td " . $this->tag_attr("td") . " colspan=\"" . $this->options["draw_cols"] . "\">";
			if($widget->options["use_font"])
				print $this->font();

			print $widget->draw();

			print "</td></tr>";

			if(is_array($widget->style))
				$this->style_reset();		
		}

		if($this->options["rtype"] == "multirow")
		{
			/* set init values */
			$col = 0;
			$dcount = count($this->data);

			if($dcount == 0) {
				$max = 0;
			}elseif($dcount > $this->options["draw_cols"]) {
				$rows = ceil($dcount/$this->options["draw_cols"]);
				$max = ($rows * $this->options["draw_cols"]) - $dcount;
			}else {
				$max = $this->options["draw_cols"] - $dcount;
			}

			/*
			For each line of data in the result, we are going to
			make a column plus the amount of fakes (max)
			*/

			for($i=0;$i!=($dcount+$max);$i++)
			{
				if($i >= $dcount)
				{
					print "\n<!-- Fake Column ->\n";
					print $this->tag("td") . "&nbsp;</td>\n";
					continue;
				}

				$this->col = $this->data[$i];

				#if its a form we need rids
				if($this->options["rids"] && $this->options["keycol"])
				{
					$keycol = $this->options["keycol"];
					print "<input type=\"hidden\" name=\"rows[]\" value=\"" . $this->col[$keycol] . "\">";
				}

				#decide to draw new row
				if($col == 0)
				{
					print "<!-- Draw a row ->\n";
					print $this->tag("tr") . "\n";
				}
				$col++;

				print "<td" . $this->tag_attr("td") . ">\n";

				foreach ($this->widgets as $widget)
				{
					if($widget->style)
						$this->style_merge_array($widget->style);

					# draw the font
					if($widget->options["use_font"])
						print $this->font();

					/* draw the widget */
					$this->process_tags(&$widget);
					print $widget->draw($this->col[$widget->name],$this->col[$keycol] . "_");

					if($widget->options["notes"])
					{
						print "</div>" . $this->font("notes") . "<blockquote>" . $widget->options["notes"] . "</blockquote></div>";
					}
				}

				if($widget->style)
					$this->style_reset();

				print "</td>\n";

				/* draw the end row if needed and reset the col counter */
				if($col == $this->options["draw_cols"]) {	
					print "</tr>\n";	
					$col = 0;
				}
			}
		}
		else
		{
			/* set init values */
			$col = 0;
			if($this->widget_count == 0) {
				$max = 0;
			}elseif($this->widget_count > $this->options["draw_cols"]) {
				$rows = ceil($this->widget_count/$this->options["draw_cols"]);
				$max = ($rows * $this->options["draw_cols"]) - $this->widget_count;
			}else {
				$max = $this->options["draw_cols"] - $this->widget_count;
			}

			for($i=0;$i!=($this->widget_count+$max);$i++)
			{
				$widget = &$this->widgets[$i];

				/* if there is no widget we know its a filler, draw a col
				and continue */

				if(!$widget)
				{
					print "\n<!-- Fake Column ->\n";
					print "<td" . $this->tag_attr("td") . ">&nbsp;</td>\n";
					continue;
				}

				/* we have a healthy widget, merge its style vars into the
					container style */

				if($widget->style)
					$this->style_merge_array($widget->style);

				/* if col is 0, draw a row tag */
				if($col == 0)
				{
					if($this->options["alt_rowcolor"])
						$this->alt_rowcolor();

					print "<!-- Draw a row ->\n";
					print "\n<tr" . $this->tag_attr("tr") . ">\n";

				}
				$col++;

				print "<!-- draw a real col ->\n";
				print "<td" . $this->tag_attr("td") . $this->get_hover_color() . ">\n";

				# draw the font, only pass override if it exists
				if($widget->options["use_font"])
					print $this->font();

				/* draw the widget */
				$this->process_tags(&$widget);
				print $widget->draw($this->data[$widget->name]);
				if($widget->options["notes"])
				{
					print "</div>" . $this->font("notes") . "<blockquote>" . $widget->options["notes"] . "</blockquote></div>";
				}
				print "</td>\n";

				/* draw the end row if needed and reset the col counter */
				if($col == $this->options["draw_cols"])
				{	
					print "</tr>\n";	
					$col = 0;
				}

				/* reset the style */
				if($widget->style)
					$this->style_reset();
			}
		}

		/* close the row just to be all html nice and stuff */
		if($col != $this->options["draw_cols"])
			print "</tr>";

		if($this->footer)
			$this->draw_footer($this->options["draw_cols"]);

		print "</table>";

		if($this->options["draw_module"])
			$this->draw_module_foot();
	}		
}
?>
