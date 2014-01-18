<?

function create_default_config($userid)
{
	global $DB;

	$prebook =  get_option("hlbook","prebook");
	if(!$prebook) { $prebook = 14; }

	$result = $DB->query("INSERT INTO hlbook_Users (userid,prebook) VALUES ('" . $userid . "','" . $prebook . "')");

	return 1;
}


$matrix = new Matrix("adminuseradd");
$matrix->header = new SimpleText("header","Add New User");

$matrix->widgets[] = new TextBox("username",array("maxlength"=>"32"),array("title"=>"Username","sticky"=>true,"required"=>true,"regexpreset"=>"alphanum"));
$matrix->widgets[] = new TextBox("password",array("maxlength"=>"32"),array("in_crypt"=>true,"hide"=>1,"title"=>"Password","sticky"=>true,"required"=>true,"regexpreset"=>"alphanum"));
$matrix->widgets[] = new TextBox("displayname",array("maxlength"=>"32"),array("title"=>"Display Name","sticky"=>true,"required"=>true));
$matrix->widgets[] = new TextBox("email",array("maxlength"=>"64"),array("title"=>"Email","sticky"=>true,"required"=>true,"regexpreset"=>"email"));
$matrix->widgets[] = new Checkbox("disabled",array("title"=>"Disable Account"));
$matrix->widgets[] = new BitwiseCheckbox("acclevel",array("title"=>"Access Level","sticky"=>true),array("body.td.valign"=>"top"),false,"sql:SELECT acclevel,title FROM auth_Acclevels WHERE plugin='hlbook'"); 
$matrix->footer = new Submit("Submit",false,array("foot.td.align"=>"center"),"Apply");

$toolbar = new HList("toolbar",array("draw_cols"=>1),array("body.td.align"=>"left"));
$toolbar->widgets[] = new SimpleText("note","* All new users inherit whatever the site wide default booking options are.  After adding a user, you can change that users individual booking options.");
$matrix->rows[] = new ContainerWidget("note",$toolbar);

if($_POST["Submit"])
{
	$input = new RowInput(&$matrix->widgets,"auth_Users","userid",false);

	if($userid = $input->update()) {

		create_default_config($userid);
		$matrix->rows[] = new Message("ok","ok","User created.  Add another user.");
	}
	else
		$matrix->rows[] = new Message("caution","caution",$input->error());
}

print form("userid");
$matrix->draw();
print "</form>";
?>
