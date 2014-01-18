<?

$matrix = new Matrix("baseoptions",array("rtype"=>"keyval"),
		false,"sql:SELECT keyname,value FROM base_Options WHERE plugin='auth'");

$matrix->header = new SimpleText("header","Auth PHPStep Options");

$matrix->widgets[] = new TextBox("session_expire",array("maxlength"=>8),
		array("regexpreset"=>"num","notes"=>"Number of hours of inactivity before a session expires","title"=>"Session Expire Time","required"=>true));

$matrix->widgets[] = new Checkbox("session_verify_ip",
		array("notes"=>"Verify the IP address of a session cookie holder with IP that created the actual session. (makes sessions more secure)","title"=>"Verify Session IP"));

$matrix->widgets[] = new TextBox("session_cleanup",array("maxlength"=>3),
		array("maxval"=>"100","regexpreset"=>"num","notes"=>"Percent chance that the session cleanup function will be called. Max is 100","title"=>"Session Cleanup","required"=>true));

$matrix->footer = new Submit("Submit",false,array("foot.td.align"=>"center"),"Apply");

if($_POST["Submit"])
{
	$input = new KeyValInput(&$matrix->widgets,"base_Options","keyname","value",false);

	if($input->update()) {
		$matrix->rows[] = new Message("ok","ok","PHPStep Auth Options Updated");
	}

	else
		$matrix->rows[] = new Message("caution","caution",$input->error());
}

print form("authoptions");
$matrix->draw();
print "</form>";
?>
