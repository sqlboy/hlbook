<?
if(!is_valid_bookid($_GET["bookid"])) {	
	return false;
}

$bookid = $_GET["bookid"];
$path = get_option("hlbook","log_path") . "/match_" . $bookid . ".zip";

if(!file_exists($path))
{
	if(is_owner($bookid))
		header("location: " . SCRIPT_URL . "?mode=hlbook&task=owner_viewclosed&bookid=$bookid");
	else
		header("location: " . SCRIPT_URL . "?mode=hlbook&task=user_viewclosed&bookid=$bookid");

	exit();
}

$url = BASE_URL . get_option("hlbook","log_url") . "/match_" . $bookid . ".zip";
header("location: " . $url);
exit();
