<?

if(!get_option("hlbook","scrn_allow_uploads"))
{	
	print message("caution","The admin has disabled screen shot uploads.");
	return false;
}

if(!secure_get_book_by_userid(&$r,$_GET["bookid"]))
{
	print message("caution","That booking does not exist.");
	return false;
}	

$bookid = $r["matchid"];
$action = SCRIPT_URL . "?mode=hlbook&task=owner_ulshots&bookid=$bookid";

$ul = new ScreenShotUploadGD(
		get_option("hlbook","scrn_full_path"),
		get_option("hlbook","scrn_thumb_path"),
		"match_" . $r["matchid"],$r["shots"]);

$ul->set(
	array(
		"uploads"=>get_option("hlbook","scrn_upload_count"),
		"max_bytes"=>get_option("hlbook","scrn_max_bytes"),
		"full_size"=>get_option("hlbook","scrn_full_size"),
		"max_size"=>get_option("hlbook","scrn_max_size"),
		"thumb_size"=>get_option("hlbook","scrn_thumb_size"),
		"mimetype"=>get_option("hlbook","scrn_mimetype"),
		"format"=>get_option("hlbook","scrn_format")
	));

if($_POST && count($_FILES>0)) {

	if(!$ul->check_paths())
	{
		print message("caution","Your destination directory does not exist or is not writable.");
		return false;
	}

	$return = $ul->process_uploads();

	$list = new VList("ulresult",array("class"=>"Table"));
	$list->header = new SimpleText(false,"Upload Results:");

	switch($return)
	{
		case "3":
			print message("caution","You did not upload anything!");
			print form("uploads",$action,"POST","multipart/form-data");
			$ul->show_form();
			print "</form>";
			break;

		case "2":
			$list->widgets[] = new SimpleText("events",$ul->event());
			$list->widgets[] = new SimpleText("errors",$ul->error());
			break;

		case "1";
			$list->widgets[] = new SimpleText("events",$ul->event());
			break;

		default:
			$list->widgets[] = new SimpleText("errors",$ul->error());
			break;
	}

	$list->draw();

	if($ul->num > $r["shots"]) {
		update_shotcount($r["matchid"],$ul->num);
	}

}
else
{
	print form("uploads",$action,"POST","multipart/form-data");
	$ul->show_form();
	print "</form>";
}
