<?
class AdvancedTable extends Container
{
	var $rows = array();
	var $query_limit;
	var $rowstart;

	function AdvancedTable($name,$options=false,$style=false,$datasource=false)
	{
		$this->init($name,$datasource);

		$this->options["class"]					=		"Table";

		$this->options["draw_labels"]			=		true;
		$this->options["draw_head"]			=		true;
		$this->options["draw_foot"]			=		true;
		$this->options["draw_new"]				=		false;
		$this->options["draw_module"]			=		true;
		$this->options["draw_altbg"]			=		true;
		$this->options["draw_linenums"]		= 	false;
		$this->options["sort_track"]			=		false;				// track sort settings between page loads
		$this->options["sql_orderby"]			=		"";					// col to order query results by
		$this->options["sql_sort"]				=		"";					// asc or desc (ascedning or decending)

		$this->options["rtype"]					=		"multirow";
		$this->options["rids"]					=		true;
		$this->options["keycol"]				=		false;

		/* Multipage Settings */
		$this->options["multipage"]			=		false;
		$this->options["baseurl"]				=		TASK_URL;			// defined in layout, includes mode and task
		$this->options["page"]					=		1;
		$this->options["numpp"]					=		50;
		$this->options["maxpagenum"]			=		100;

		if(is_array($options))
			$this->options = array_merge($this->options,$options);

		$this->init_style($style);
	}

	function render()
	{
		if($this->options["draw_module"])
			$this->draw_module_head();

		print $this->tag("table");

		if($this->header)
			$this->draw_header();

		// static rows
		foreach($this->rows as $widget)
		{
			if(is_array($widget->style))
				$this->style_merge_array($widget->style);

			print "<tr" . $this->tag_attr("tr","row") . ">";
			print "<td" . $this->tag_attr("td","row") . " colspan=\"" . $this->widget_count . "\">";

			if($widget->options["use_font"])
				print $this->font();

			print $widget->draw();

			print "</td></tr>";

			if(is_array($widget->style))
				$this->style_reset();		
		}

		if($this->options["draw_labels"])
		{
			print $this->tag("tr","labels");

			foreach($this->widgets as $widget)
			{
				if($widget->options["hidden"]) {
					continue;
				}

				$this->style_merge_array($widget->style);

				print $this->tag("td","labels");
				print $this->font("labels") . $widget->options["title"];


				if($widget->options["sort"] && $this->options["baseurl"])
				{
					print "<br>";
					print "<a href=\"" . $this->options["baseurl"] . "&orderby=" . $this->name . "_" . $widget->name . "A\"><img src=\"" . IMAGE_URL . "icons/toolbar/sort_asc.png\" border=0></a>&nbsp;";
					print "<a href=\"" . $this->options["baseurl"] . "&orderby=" . $this->name . "_" . $widget->name . "D\"><img src=\"" . IMAGE_URL . "icons/toolbar/sort_desc.png\" border=0>&nbsp;</a>";
				}

				print "&nbsp;</td>";
				$this->style_reset();		
			}
		}

		// data rows
		foreach($this->data as $this->col)
		{
			if($this->options["rids"] && $this->options["keycol"]) {
				$keycol = $this->options["keycol"];
				print "<input type=\"hidden\" name=\"rows[]\" value=\"" . $this->col[$keycol] . "\">";
			}

			print $this->tag("tr");
			if($this->options["draw_altbg"]) { $this->alt_class("body","td"); }

			foreach ($this->widgets as $widget)
			{
				if($widget->options["hidden"]) {
					print $widget->draw($this->col[$widget->name],$this->col[$keycol] . "_");
					continue;
				}

				$this->style_merge_array($widget->style);
				$this->process_tags(&$widget);

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

		if($this->options["multipage"])
			$this->draw_page_chooser();

		if($this->footer)
			$this->draw_footer();

		print "</table>";

		if($this->options["draw_module"]) { $this->draw_module_foot(); }
	}

	function draw_page_chooser()
	{
		if(!$this->options["multipage"] || !$this->options["baseurl"]) {
			return false;
		}

		$_section = $this->options["page"] / $this->options["maxpagenum"];

		if(is_int($_section))
			$section = $_section;
		else
			$section = intval($_section) +1;

		$page_end 	= $this->options["maxpagenum"] * $section;
		$page_start = $page_end - $this->options["maxpagenum"]+1;
		$pagecount	= $this->pagecount;

		print $this->tag("tr","foot") . "<td align=\"right\" colspan=\"" . $this->widget_count . "\"" . $this->tag_attr("td","foot") . ">";
		print $this->font("body");


		print "Page: ";

		if($section > 1)
		{
			#show the previous section
			$prev = $section -1;
			$prev_end = $this->options["maxpagenum"] * $prev;
			$prev_str = $prev_end - $this->options["maxpagenum"]+1;

			$first_page_url = "<a href=\"" . $this->options["baseurl"] . "&" . $this->name . "_page=1\">";
			$prev_section_url = "<a href=\"" . $this->options["baseurl"] . "&" . $this->name . "_page=$prev_str\">";

			print "&lt;" . $first_page_url . "First</a>&gt;&lt;" . $prev_section_url . $prev_str . ".." . $prev_end . "</a>&gt; ";
		}

		for($i=$page_start;$i<=$page_end;$i++)
		{
			if($i > $pagecount)
				break;

			if($i == $this->options["page"])
				print "<b>$i</b> ";
			else
				print "<a href=\"" . $this->options["baseurl"] . "&" . $this->name . "_page=$i\">$i</a> ";
		}

		if($section >= 1)
		{
			#show the previous section
			$next = $section + 1;
			$next_end = $this->options["maxpagenum"] * $next;
			$next_str = $next_end - $this->options["maxpagenum"]+1;

			$last_page_url = "<a href=\"" . $this->options["baseurl"] . "&" . $this->name . "_page=$pagecount\">";

			if($pagecount > $next_str)
				print "&lt;<a href=\"" . $this->options["baseurl"] . "&" . $this->name . "_page=$next_str\">" . $next_section_url . $next_str . ".." . $next_end . "</a>&gt;";

			if($pagecount != $this->options["page"] && $pagecount > $page_end )
				print "&lt;" . $last_page_url . "Last" . "</a>&gt;";
		}

		print "</td></tr>";
	}
}
?>
