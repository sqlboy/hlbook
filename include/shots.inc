<?

if(!is_valid_bookid($_GET["bookid"]))
	return false;

if(!get_option("hlbook","scrn_allow_uploads"))
	return false;

$bookid = $_GET["bookid"];
$path = get_option("hlbook","scrn_thumb_path");
$format = get_option("hlbook","scrn_format");

list($width,$height) = split("x",get_option("hlbook","scrn_thumb_size"));

$vlist = new VList("shots",array("class"=>"Table"),array("body.table.align"=>"center"));
$vlist->header = new SimpleText(false,"Shots");

if(!file_exists($path) || !$dh = opendir($path))
{
	$vlist->widgets[] = new SimpleText(false,"No Screenshots");
	$vlist->draw();
	return false;
}

$count = 0;
while (false != ($file = readdir($dh)))
{ 
	if(!preg_match("/^(match_" . $bookid . "_[\d]+)\." . $format . "$/",$file,$match))
		continue;

	$count++;
	if($count > 5)
		break;

	$ev_match = $match[1];
	$src = get_option("hlbook","scrn_thumb_url") . $ev_match . "." . $format;

	$vlist->widgets[] = new Image("shot$count",
			SCRIPT_URL . "?mode=hlbook&task=user_viewshot&bookid=$bookid&file=$ev_match",
			array("src"=>$src,"width"=>$width,"height"=>$height),
			false,
			array("body.td.align"=>"center"));
}
closedir($dh);

if($count > 5) {

	if(is_owner($bookid)) {
		$vlist->footer = new SimpleText(false,"See all ($count)",SCRIPT_URL . "?mode=hlbook&task=owner_viewshots&bookid=$bookid");
	}
	else
	{
		$vlist->footer = new SimpleText(false,"See all ($count)",SCRIPT_URL . "?mode=hlbook&task=user_viewshots&bookid=$bookid");
	}
}


$vlist->draw();
?>
