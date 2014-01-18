<?

$host = get_servername($_GET["serverid"]);
if(!$host) {
	print message("caution","That server does not exist");
	return 0;
}

if($_POST) {

	global $DB;
	$serverid = $_POST["serverid"];
	$tables = array(
			"hlbook_Bookings",
			"hlbook_Players",
			"hlbook_ServerModes",
			"hlbook_ServerProps",
			"hlbook_Servers"
		);

	$list = new VList("delserver",array("class"=>"Table"));
	$list->header = new Message("warn","caution","Completed Delete Operations:");

	foreach ($tables as $table)
	{
		$result	= $DB->query("DELETE FROM $table WHERE serverid='" . $serverid . "'");
		$rows		= $DB->affected();
		$opt		= $DB->query("OPTIMIZE TABLE $table");

		$list->widgets[] = new SimpleText(false,"Deleted $rows entries for serverid $serverid from $table");
	}

	$list->draw();
	return 1;
}

$list = new VList("delserver",array("class"=>"Table"));
$list->header = new Message("warn","caution","You are about to delete the server: $host");
$list->widgets[] = new SimpleText(false,"This will delete all instances of this server from HLBook.  If you want to save historical information about pask bookings, try disabling the server instead");
$list->widgets[] = new SimpleText(false,"This operation will also:<br>Delete all bookings associated with this server.<br>Delete all records of players connecting to this server.");
$list->widgets[] = new Hidden("serverid",false,false,$_GET["serverid"]);
$list->footer = new Submit("Submit",false,array("foot.td.align"=>"center"),"Delete");

print form("delserver");
$list->draw();
print "</form>";
