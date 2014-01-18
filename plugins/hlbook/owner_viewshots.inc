<?
$bookid = $_GET["bookid"];
if(!is_valid_bookid($bookid) || !get_option("hlbook","scrn_allow_uploads"))
{
	print message("stop","That is not a valid bookid.");
	return false;
}

$path = get_option("hlbook","scrn_thumb_path");
if(!file_exists($path)) {
	print message("caution","The thumbnail path does not exist");
	return false;
}

if(!$dh = opendir($path))
	return false;

$browser = new Browser("screenshots",array("draw_cols"=>4,"class"=>"Table"),array("body.td.align"=>"center","body.table.width"=>"100%"));
$browser->header = new SimpleText("header","Screenshots");

while (false != ($file = readdir($dh)))
{ 
	if(!preg_match("/^(match_" . $bookid . "_[\d]+)\.(" . get_option("hlbook","scrn_format") . ")$/",$file,$match))
		continue;

	$ev_file = $match[1];
	$ev_format = $match[2];
	$filename = $ev_file . "." . $ev_format;

	$src = get_option("hlbook","scrn_thumb_url") . "/" . $filename;

	$browser->widgets[] = new Image("$ev_file",
			SCRIPT_URL . "?mode=hlbook&task=user_viewshot&bookid=$bookid&file=" . $ev_file,
			array("src"=>$src,"target"=>"screenshot"));
}

closedir($dh);
$browser->draw();
