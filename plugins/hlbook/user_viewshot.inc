<?

if(!get_option("hlbook","scrn_allow_uploads"))
	return false;

if(!preg_match("/^([a-zA-Z0-9_]+)$/",$_GET["file"]))
{
	print message("caution","That screenshot was not found.");
	return false;
}

if(!is_numeric($_GET["bookid"]))
{
	print message("caution","That screenshot was not found.");
	return false;
}

$bookid = $_GET["bookid"];
$file = $_GET["file"];
$format = get_option("hlbook","scrn_format");

$src = get_option("hlbook","scrn_full_url") . "/" . $file . "." . $format;

if(!file_exists(get_option("hlbook","scrn_full_path") . "/" . $file . "." . $format )) {
	print message("caution","That screenshot was not found.");
	return false;
}
else {
	print "<img src=\"" . $src . "\">";
}
?>
