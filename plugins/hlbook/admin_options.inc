<?

$matrix = new Matrix("hlbookoptions",array("rtype"=>"keyval"),
		false,"sql:SELECT keyname,value FROM base_Options WHERE plugin='hlbook'");

$matrix->header = new SimpleText("header","HLBook Default Options");
$matrix->widgets[] = new TextBox("prebook",array("maxlength"=>3),
		array("regexpreset"=>"num","notes"=>"Number of days before hand a user can prebook a match","title"=>"Match PreBook","required"=>true));

$matrix->widgets[] = new Checkbox("email_allow",
		array("notes"=>"Allows users to notify their address book of booked matches.  Requires you have PHP setup with an SMTP server to relay mail.","title"=>"Allow Email Notify"));

$matrix->widgets[] = new TextBox("email_subject",array("size"=>"45","maxlength"=>128),
		array("regexpreset"=>"path","notes"=>"Subject for emails that go out for booking notofications","title"=>"Email Subject","required"=>false));

$matrix->widgets[] = new TextBox("email_matchtmpl",array("size"=>"45","maxlength"=>128),
		array("regexpreset"=>"path","notes"=>"File system location of email template","title"=>"Email Template","required"=>false));

$matrix->widgets[] = new Select("logstore",false,
		array("notes"=>"Are you storing log downloads on this machine, a remote machine, or none at all","title"=>"Server Logs","required"=>true),
		false,
		false,
		array("none"=>"Disabled","local"=>"Stored Locally","remote"=>"Stored Remotely")
	);

$matrix->widgets[] = new TextBox("logpath",array("size"=>"45","maxlength"=>128),
		array("notes"=>"File system location where zipped log files will be stored","title"=>"Log Path","required"=>false));

$matrix->widgets[] = new TextBox("logurl",array("size"=>"45","maxlength"=>128),
		array("notes"=>"URL where zipped log files will be stored.  Always include ending / oon URLs.","title"=>"Log Url","required"=>false));

$matrix->footer = new Submit("Submit",false,array("foot.td.align"=>"center"),"Apply");

if($_POST["Submit"])
{
	$input = new KeyValInput(&$matrix->widgets,"base_Options","keyname","value",false);

	if($input->update()) {
		$matrix->rows[] = new Message("ok","ok","HLBook Options Updated");
	}

	else
		$matrix->rows[] = new Message("caution","caution",$input->error());
}

print form("hlbookoptions");
$matrix->draw();
print "</form>";
?>
