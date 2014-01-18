<?

if(!secure_get_book_by_userid(&$book,$_GET["bookid"]))
{
	print message("caution","You do not have access to this server booking or this booking does not exist.");
	return false;
}

if($book["status"] != "S") {
	print message("caution","You cannot delete a book that is already closed or in progress");
	return 0;	
}


if($_POST)
{
	global $DB;
	$matchid = $_POST["bookid"];
	$tables = array(
			"hlbook_Bookings",
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
else {

	$list = new VList("delbooking",array("class"=>"Table"));
	$list->header = new Message("warn","caution","You are about to delete this booking: " . $book["servername"]);
	$list->widgets[] = new SimpleText(false,"This will delete all instances of this booking from HLBook.");
	$list->widgets[] = new Hidden("bookid",false,false,$_GET["bookid"]);
	$list->footer = new Submit("Submit",false,array("foot.td.align"=>"center"),"Delete");

	print form("delbooking");
	$list->draw();
	print "</form>";
}
