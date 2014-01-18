<?
class HList extends Container
{
	function HList($name,$options=false,$style=false,$datasource=false)
	{
		$this->init($name,$datasource);

		$this->options["rtype"]						=		"row";
		$this->options["class"]						=		"List";

		$this->options["draw_module"]				=		false;
		$this->options["draw_cols"]				=		4;

		$this->options["calcwidth"]				=		true;
		$this->options["draw_allcols"]			=		true;

		if(is_array($options))
			$this->options = array_merge($this->options,$options);

		$this->init_style($style);
	}

	function render()
	{
		if($this->options["draw_module"]) { $this->draw_module_head(); }
		print $this->tag("table") . "\n";

		if($this->options["rtype"] == "multirow")
		{
			// if the data result has more rows than draw_cols, we increase draw_cols
			if(count($this->data) > $this->options["draw_cols"]) {
				$this->options["draw_cols"] = count($this->data);

				if($this->options["calcwidth"])
					$this->calculate_width();
			}

			if($this->header) { $this->draw_header($this->options["draw_cols"]); }
			print $this->tag("tr") . "\n";

			for($i=0;$i != $this->options["draw_cols"];$i++)
			{
				$do_col = true;
				if($i >= count($this->data)) {

					if($this->options["draw_allcols"]) {

						print "\n<!-- Fake Column ->\n";
						print $this->tag("td") . "&nbsp;</td>\n";
						continue;
					}
					else { break; }
				}

				$this->col = $this->data[$i];
				foreach($this->widgets as $widget)
				{	

					$this->process_tags(&$widget);
					if($widget->options["hidden"]) {
						print $widget->draw($this->col[$widget->name]);
						continue;
					}

					if($do_col == true) {
						$this->style_merge_array($widget->style);
						print $this->tag("td");
						$do_col = false;
					}


					if($widget->options["use_font"]) { print $this->font(); }
					print $widget->draw($this->col[$widget->name]);

					if($widget->options["notes"]) {
						print "</div>" . $this->font("notes") . "<blockquote>" . $widget->options["notes"] . "</blockquote></div>";
					}
				}

				print "</td>\n";
			}	

		}
		else
		{
			/* some math for determining how many extra cols we have to draw */
			if($this->widget_count > $this->options["draw_cols"]) {
				$this->options["draw_cols"] = $this->widget_count;

				if($this->options["calcwidth"])
					$this->calculate_width();	
			}

			if($this->header) { $this->draw_header($this->options["draw_cols"]); }
			print $this->tag("tr");

			for($i=0;$i!=$this->options["draw_cols"]; $i++)
			{
				if($i>=$this->widget_count)
				{
					if($this->options["draw_allcols"]) {
						print "\n<!-- Fake Column ->\n";
						print "<td" . $this->tag_attr("td") . ">&nbsp;</td>\n";
						continue;
					}
					else { break; }
				}

				$widget = &$this->widgets[$i];
				if($widget->options["hidden"]){
					print $widget->draw($this->col[$widget->name]);
					continue;
				}

				if($widget->style) { $this->style_merge_array($widget->style); }

				if(TASK == $widget->name && $this->attr_val("body.tdactive.class")) {
					$this->alt_active("body","td");
				}

				print "<td" . $this->tag_attr("td") . ">";
				if($widget->options["use_font"]){ print $this->font();}
				print $widget->draw($this->data[$widget->name]);

				if($widget->options["notes"]) {
					print "</div>" . $this->font("notes") . "<blockquote>" . $widget->options["notes"] . "</blockquote></div>";
				}

				print "</td>\n";
				$this->style_reset();
			}
		}

		$this->draw_footer();

		print "</tr></table>\n";
		if($this->options["draw_module"])
			$this->draw_module_foot();
	}
}
?>
