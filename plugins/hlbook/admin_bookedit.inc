<?
$host = get_matchname($_GET["bookid"]);
if(!$host) {
	print message("caution","That booking does not exist");
	return 0;
}

$matrix = new Matrix("bookedit",false,false,"sql:SELECT * FROM hlbook_Bookings WHERE matchid='" . $_GET["bookid"] . "'");
$matrix->header = new SimpleText(false,"Edit booking #" . $_GET["bookid"] . " $host");
$matrix->widgets[] = new TextBox("servername",array("size"=>32,"maxlength"=>64),
		array("title"=>"Server Name","required"=>true,"regexpreset"=>"words","sticky"=>true));
$matrix->widgets[] = new TextBox("svpasswd",array("size"=>16,"maxlength"=>16),
		array("title"=>"Connect Password:","required"=>true,"sticky"=>true));
$matrix->widgets[] = new TextBox("rcon",array("size"=>16,"maxlength"=>16),
		array("title"=>"Rcon","required"=>true,"sticky"=>true));

$matrix->widgets[] = new Select("map",false,
		array("title"=>"Start Map","required"=>true,"sticky"=>true,"notes"=>"The server will enter your booking time on this map"),false,false,"sql:SELECT name,name FROM hlbook_Maps WHERE modid='%[modid]'");
$matrix->widgets[] = new Select("userid",false,
		array("title"=>"Assigned to User","required"=>true,"sticky"=>true,"notes"=>"Change booking ownership"),false,false,"sql:SELECT userid,displayname FROM auth_Users");

$matrix->widgets[] = new Checkbox("notify",
		array("title"=>"Notify Address Book","sticky"=>true,"notes"=>"Checking this box will send a email notifitcation to everyone in your address book with details on this booking.  This information does not include the rcon password.","write"=>false));
$matrix->widgets[] = new TextArea("comments",false,
		array("allow"=>"","title"=>"Comments","sticky"=>true),array("body.td.valign"=>"top"));
$matrix->widgets[] = new Select("status",false,array("notes"=>"Use with caution","title"=>"Status","sticky"=>true,"required"=>true),false,false,array("P"=>"In Progress","R"=>"Restarting","E"=>"Errored","S"=>"Scheduled","C"=>"Closed","K"=>"Kill"));
$matrix->widgets[] = new Checkbox("logopen",
		array("title"=>"Backend log is open.  Change with caution.","sticky"=>true));
$matrix->widgets[] = new Hidden("serverid",array("required"=>true),false,"%[serverid]");

$matrix->footer = new Submit("Submit",false,array("foot.td.align"=>"center"),"Apply");

if($_POST["Submit"])
{
	$input = new RowInput(&$matrix->widgets,"hlbook_Bookings",array("matchid","serverid"),array($_GET["bookid"],$_POST["serverid"]));

	if($input->update())
		$matrix->rows[] = new Message("ok","ok","Booking Updated");
	else
		$matrix->rows[] = new Message("caution","caution",$input->error());
}

print form("editbooking");
$matrix->draw();
print "</form>";
