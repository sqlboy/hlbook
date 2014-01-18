<?

if($_POST) {

	global $DB;
	$map = $_GET["mapname"];
	$tables = array(
			"hlbook_Maps"
		);

	$list = new VList("delmap",array("class"=>"Table"));
	$list->header = new Message("warn","caution","Completed Delete Operations:");

	foreach ($tables as $table)
	{
		$result	= $DB->query("DELETE FROM $table WHERE name='" . $map . "'");
		$rows		= $DB->affected();
		$opt		= $DB->query("OPTIMIZE TABLE $table");

		$list->widgets[] = new SimpleText(false,"Deleted $rows entries for map $map from $table");
	}

	$list->draw();
	return 1;
}

$list = new VList("delserver",array("class"=>"Table"));
$list->header = new Message("warn","caution","You are about to delete the map: " . $_GET["mapname"]);
$list->widgets[] = new Hidden("mapname",false,false,$_GET["mapname"]);
$list->footer = new Submit("Submit",false,array("foot.td.align"=>"center"),"Delete");

print form("delmap");
$list->draw();
print "</form>";
