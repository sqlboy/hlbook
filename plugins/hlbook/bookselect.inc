<?

/* first we find out if the user is admin, owner, or regular user
	we'll treat owners and admins basically the same  

	we're just using this page to find out where to go, not as any kind of real security check
*/

global $DB;

if(!is_numeric($_GET["bookid"]) || !is_valid_bookid($_GET["bookid"]))
{
	print message("stop","That is not a valid booking.");
	return false;
}

if(check_access(get_defined_access("hladmin","hlbook")))
{
	$result = $DB->query("SELECT matchid,status FROM hlbook_Bookings WHERE matchid='" . $_GET["bookid"] . "' LIMIT 1");
	list($bookid,$status) = $DB->fetch_row($result);
	$DB->free_result($result);

	switch($status)
	{
		case "P":
			#match is in progress
			header("location:" . MODE_URL . "&task=owner_viewinprog&bookid=$bookid");
			break;

		case "C":
			header("location:" . MODE_URL . "&task=owner_viewclosed&bookid=$bookid");
			break;

		case "R":
			header("location:" . MODE_URL . "&task=index");
			break;

		default:
			header("location:" . MODE_URL . "&task=owner_editbook&bookid=$bookid");
	}
}
elseif(secure_get_book_by_userid($r,$_GET["bookid"]))
{
	$status = $r["status"];
	$bookid = $r["matchid"];

	switch($status)
	{
		case "P":
			#match is in progress
			header("location:" . MODE_URL . "&task=owner_servermon&bookid=$bookid");
			break;

		case "C":
			header("location:" . MODE_URL . "&task=owner_viewclosed&bookid=$bookid");
			break;

		case "R":
			header("location:" . MODE_URL . "&task=index");
			break;

		default:
			header("location:" . MODE_URL . "&task=owner_editbook&bookid=$bookid");
	}
}
else
{
	$result = $DB->query("SELECT matchid,status FROM hlbook_Bookings WHERE matchid='" . $_GET["bookid"] . "' LIMIT 1");
	list($bookid,$status) = $DB->fetch_row($result);
	$DB->free_result($result);

	switch($status)
	{
		case "P":
			header("location:" . MODE_URL . "&task=user_viewprog&bookid=$bookid");
			break;

		case "C":
			header("location:" . MODE_URL . "&task=user_viewclosed&bookid=$bookid");
			break;

		case "R":
			header("location:" . MODE_URL . "&task=user_viewprog&bookid=$bookid");
			break;

		case "E":
			header("location:" . MODE_URL . "&task=user_viewprog&bookid=$bookid");
			break;

		case "S":
			header("location:" . MODE_URL . "&task=user_viewbooked&bookid=$bookid");
			break;
	}
}
