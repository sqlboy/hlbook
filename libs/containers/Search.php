<?
class SimpleSearch
{
	var $search = array();
	var $results = array();

	var $tables;
	var $options;

	var $query;
	var $errors;

	function SimpleSearch($name,$table,$options=false,$style=false)
	{
		$this->name 		= $name;
		$this->table		= $table;
		$this->style		= $style;

		$this->options["numpp"]			=	50;
		$this->options["multipage"]	= 	true;
		$this->options["sort_track"]	=  true;
		$this->options["baseurl"]		=  TASK_URL;
		$this->options["title"]			=	"Search";

		if(is_array($options)) {
			$this->options = array_merge($this->options,$options);
		}
	}

	function draw_search_table($class = "Table")
	{
		$smatrix = new Matrix("smatrix",false,array("body.table.width"=>"100%"));

		if($this->options["title"])
			$smatrix->header = new SimpleText("header",$this->options["title"]);

		$smatrix->footer = new Submit("Submit",false,array("foot.td.align"=>"center"),"Search");
		$smatrix->widgets = &$this->search;

		print form("search",$this->options["baseurl"]);
		$smatrix->draw();
		print "</form>\n";
	}

	function draw()
	{
		if($_GET[$this->name] == "new") {
			$this->del_cookie();
		}

		if($_POST["Submit"] == "Search") {
			$this->generate_query();
			$this->set_cookie();
			$this->draw_search_results();
		}
		elseif(session_get($this->name))
			$this->draw_search_results();
		else
		{
			$this->draw_search_table();	
		}
	}

	function draw_search_results($class = "Table")
	{
		$tresult = new AdvancedTable($this->name . "tbl_results",
				array("orderby"=>$this->options["orderby"],"numpp"=>$this->options["numpp"],"multipage"=>$this->options["multipage"],"sort_track"=>$this->options["sort_track"],"baseurl"=>$this->options["baseurl"]),
				$this->style,
				"sql:" . session_get($this->name));

		$tresult->header = new SimpleText("header","Search Results");
		$tresult->widgets = &$this->results;

		$toolbar = new HList("result_info",array("class"=>"Table","clean"=>true));
		$toolbar->widgets[] = new Label("count",$this->options["baseurl"] . "&$this->name=new",array("title"=>"Result Count"),false,"New Search");
		$tresult->rows[] = new ContainerWidget("toolbar",$toolbar);

		$tresult->draw();
		print "<br>";	

	}

	function generate_query()
	{
		$where = "";

		foreach($this->search as $widget)
		{	
			#first run convert if it exists
			if($widget->options["convert"] && method_exists($widget,"convert")) {
				$_POST[$widget->name] = $widget->convert();
			}

			if(!$_POST[$widget->name] && $widget->options["search_type"]!= "bitwise") {
				continue;
			}

			if($where && $widget->options["search_type"]!= "bitwise") { 
				$where .= " && "; 
			}

			switch($widget->options["search_type"])
			{
				case "regex":

					$where .= $widget->name . " REGEXP \"" . $_POST[$widget->name] . "\"";
					break;

				case "bitwise":

					$value = 0;
					foreach ($_POST[$widget->name . "_bitrows"] as $bitrow)
					{   
						if ($_POST[$widget->name . "_" . $bitrow])
							$value = $value + $bitrow;
					}

					if($value) {
						if($where) { $where .=" && "; }
						$where .= $widget->name . " & " . $value;
					}

					break;

				case "exact":

					$where .= $widget->name . " ='" . $_POST[$widget->name] . "'";

					break;

				case "gteq":

					$where .= $widget->name . " >='" . $_POST[$widget->name] . "'";
					break;

				default:
					break;
			}

		}

		if($where) {

			#I need to fixed the loop above to get rid of this.
			if(preg_match("/ \&\& $/",$where)) {
				preg_replace("/ \&\&$/","",$where);
			}

			$where = "WHERE $where";
		}
		if($this->options["limit"])
			$limit = " LIMIT " . $this->options["limit"];

		$this->query = "SELECT * FROM $this->table $where" . $limit;
	}

	function set_cookie()
	{
		global $SESSION;

		$SESSION->set($this->name,$this->query);
		return 1;
	}

	function del_cookie()
	{
		global $SESSION;
		$SESSION->del($this->name);
	}	

}
?>
