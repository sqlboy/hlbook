<?

$displayname = whatis_displayname($_GET["userid"]);
if(!$displayname) {
	print message("caution","That user was not found");
	return 0;
}

$matrix = new Matrix("adminuseredit",false,false,"sql:SELECT * FROM auth_Users WHERE userid='" . $_GET["userid"] . "'");
$matrix->header = new SimpleText("header","Edit $displayname's Auth settings");

$matrix->widgets[] = new Label("logins",false,array("title"=>"Logins"));
$matrix->widgets[] = new Date("lastlogin",false,array("title"=>"Last Login"));
$matrix->widgets[] = new TextBox("username",array("maxlength"=>"32"),array("title"=>"Username","sticky"=>true,"required"=>true,"regexpreset"=>"alphanum"));
$matrix->widgets[] = new TextBox("password",array("maxlength"=>"32"),array("in_crypt"=>true,"hide"=>1,"title"=>"Password","sticky"=>true,"required"=>true,"regexpreset"=>"alphanum"));
$matrix->widgets[] = new TextBox("displayname",array("maxlength"=>"32"),array("title"=>"Display Name","sticky"=>true,"required"=>true));
$matrix->widgets[] = new TextBox("email",array("maxlength"=>"64"),array("title"=>"Email","sticky"=>true,"required"=>true,"regexpreset"=>"email"));
$matrix->widgets[] = new Checkbox("disabled",array("title"=>"Disable Account"));
$matrix->widgets[] = new BitwiseCheckbox("acclevel",array("title"=>"Access Level","sticky"=>true),array("body.td.valign"=>"top"),false,"sql:SELECT acclevel,title FROM auth_Acclevels WHERE plugin='hlbook'"); 
$matrix->footer = new Submit("Submit",false,array("foot.td.align"=>"center"),"Apply");

if($_POST["Submit"])
{
	$input = new RowInput(&$matrix->widgets,"auth_Users","userid",$_GET["userid"]);

	if($input->update())
		$matrix->rows[] = new Message("ok","ok","User Updated");
	else
		$matrix->rows[] = new Message("caution","caution",$input->error());
}

print form("userid");
$matrix->draw();
print "</form>";
