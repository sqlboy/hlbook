<?

$displayname = whatis_displayname($_GET["userid"]);
if(!$displayname) {
	print message("caution","That user was not found");
	return 0;
}

$matrix = new Matrix("adminuseredit",false,false,"sql:SELECT * FROM hlbook_Users WHERE userid='" . $_GET["userid"] . "'");
$matrix->header = new SimpleText("header","Edit $displayname's Booking settings");

$matrix->widgets[] = new TextBox("prebook",array("size"=>4,"maxlength"=>3),array("default"=>0,"notes"=>"Number of days before hand this user can prebook a match.  Setting this to zero will set the user to the HLBooking default of <b>" . get_option("hlbook","prebook") . "</b> days","title"=>"Prebook","sticky"=>true,"required"=>false,"regexpreset"=>"num"));
$matrix->widgets[] = new TextBox("maxscheduled",array("size"=>4,"maxlength"=>3),array("default"=>0,"notes"=>"The number of matches this user can have pre-scheduled at any given time.  Setting this to 0 will allow unlimited.","title"=>"Max Scheduled","sticky"=>true,"required"=>false,"regexpreset"=>"num"));
$matrix->widgets[] = new TextBox("maxbooks",array("size"=>4,"maxlength"=>3),array("default"=>0,"notes"=>"The number of total slots a user can book before he can't book anymore,ever. Setting this to zero will allow an unlimited number of bookings.  This is good for demo accounts and such.","title"=>"Max Total Bookings","sticky"=>true,"required"=>false,"regexpreset"=>"num"));

$matrix->footer = new Submit("Submit",false,array("foot.td.align"=>"center"),"Apply");

if($_POST["Submit"])
{
	$input = new RowInput(&$matrix->widgets,"hlbook_Users","userid",$_GET["userid"]);

	if($input->update())
		$matrix->rows[] = new Message("ok","ok","User Updated");
	else
		$matrix->rows[] = new Message("caution","caution",$input->error());
}

print form("userid");
$matrix->draw();
print "</form>";
