<?
# Delete a user and all his records.
$name = whatis_displayname($_GET["userid"]);
if(!$name || !$_GET["userid"]) {
	print message("caution","That user was not found");
	return 0;
}

if($_POST) {

	global $DB;
	$userid = $_POST["userid"];
	$tables = array(
			"hlbook_Bookings",
			"hlbook_Users",
			"hlbook_AddrBook",
			"hlbook_Players",
			"auth_Sessions",
			"auth_Users"
		);

	$list = new VList("deluser",array("class"=>"Table"));
	$list->header = new Message("warn","caution","Completed Delete Operations:");

	foreach ($tables as $table)
	{
		$result	= $DB->query("DELETE FROM $table WHERE userid='" . $userid . "'");
		$rows		= $DB->affected();
		$opt		= $DB->query("OPTIMIZE TABLE $table");

		$list->widgets[] = new SimpleText(false,"Deleted $rows entries for $name from $table");
	}

	$list->draw();
	return 1;
}

$list = new VList("deluser",array("class"=>"Table"));
$list->header = new Message("warn","caution","You are about to delete the user: $name");
$list->widgets[] = new SimpleText(false,"This will delete all instances of this user from HLBook.  If you want to save historical information about pask bookings or activities, try disabling the user instead");
$list->widgets[] = new SimpleText(false,"This operation will also:<br>Delete all bookings associated with this server.<br>Delete all records of players connecting to this server.");
$list->widgets[] = new Hidden("userid",false,false,$_GET["userid"]);
$list->footer = new Submit("Submit",false,array("foot.td.align"=>"center"),"Delete");

print form("deluser");
$list->draw();
print "</form>";
