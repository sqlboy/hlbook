<?

$matrix = new Matrix("adminmail",array("left_width"=>"20%"));
$matrix->header = new SimpleText(false,"Mass Email Users");
$matrix->rows[] = new SimpleText(false,"The quickest way to to get in touch with all your
			users about HLb problems, updates, etc, is here.  You must have SMTP/Sendmail enabled in your PHP
			configuration for this to work.");

$matrix->widgets[] = new TextBox("subject",array("maxlength"=>32),array("required"=>true,"title"=>"Subject","sticky"=>false));
$matrix->widgets[] = new TextArea("message",array("cols"=>50,"rows"=>20,"wrap"=>"off"),array("required"=>true,"title"=>"Message","sticky"=>false),array("body.td.valign"=>"top"));
$matrix->footer = new Submit("Submit",false,array("foot.td.align"=>"center"),"Send");

if($_POST["Submit"] && $_POST["subject"] && $_POST["message"]) {

	$total = email_all_users($_POST["subject"],$_POST["message"]);
	$message = new VList("message",array("class"=>"Table"));
	$message->header = new Message("ok","ok","Message Sent to $total user(s)");
	$message->draw();
}
else{

	if($_POST)
		$matrix->rows[] = new Message("caution","caution","You need to fill in the email subject and message");

	print form("mail");
	$matrix->draw();
	print "</form>\n";	
}


?>
