<?

if(!get_config_by_id($cfg,$_GET["cfgid"]))
{
	print message("caution","That server configuration does not exist.");
	return 0;
}


if($_POST) {

	global $DB;
	$configid = $_GET["cfgid"];
	$tables = array(
			"hlbook_Configs");

	$list = new VList("delconfig",array("class"=>"Table"));
	$list->header = new Message("warn","caution","Completed Delete Operations:");

	foreach ($tables as $table)
	{
		$result	= $DB->query("DELETE FROM $table WHERE configid='" . $configid . "'");
		$rows		= $DB->affected();
		$opt		= $DB->query("OPTIMIZE TABLE $table");

		$list->widgets[] = new SimpleText(false,"Deleted $rows entries for configid $configid from $table");
	}

	$list->draw();
	return 1;
}

$list = new VList("delserver",array("class"=>"Table"));
$list->header = new Message("warn","caution","You are about to delete the config: " . $cfg["title"]);
$list->widgets[] = new Hidden("cfgid",false,false,$cfg["configid"]);
$list->footer = new Submit("Submit",false,array("foot.td.align"=>"center"),"Delete");

print form("delconfig");
$list->draw();
print "</form>";
