<?

$host = get_matchname($_GET["bookid"]);
if(!$host) {
	print message("caution","That booking does not exist");
	return 0;
}

if($_POST) {

	global $DB;
	$matchid = $_POST["bookid"];
	$tables = array(
			"hlbook_Bookings",
			"hlbook_Players"
		);

	$list = new VList("delbooking",array("class"=>"Table"));
	$list->header = new Message("warn","caution","Completed Delete Operations:");

	foreach ($tables as $table)
	{
		$result	= $DB->query("DELETE FROM $table WHERE matchid='" . $matchid . "'");
		$rows		= $DB->affected();
		$opt		= $DB->query("OPTIMIZE TABLE $table");

		$list->widgets[] = new SimpleText(false,"Deleted $rows entries for bookid $matchidfrom $table");
	}

	$list->draw();
	return 1;
}

$list = new VList("delbooking",array("class"=>"Table"));
$list->header = new Message("warn","caution","You are about to delete this booking: $name");
$list->widgets[] = new SimpleText(false,"This will delete all instances of this booking from HLBook.");
$list->widgets[] = new SimpleText(false,"This operation will also:<br>Delete all records of players who used this booking.");
$list->widgets[] = new Hidden("bookid",false,false,$_GET["bookid"]);
$list->footer = new Submit("Submit",false,array("foot.td.align"=>"center"),"Delete");

print form("delbooking");
$list->draw();
print "</form>";
