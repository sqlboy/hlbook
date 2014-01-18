<?

$matrix = new Matrix("ownerprofile",
		array("draw_head"=>true,"draw_labels"=>false,"rtype"=>"row"),
		array("body.table.width"=>"100%"),
		"sql:SELECT * FROM auth_Users WHERE userid='" . userid() . "'");
$matrix->header = new SimpleText("header","Your User Profile");
$matrix->widgets[] = new Label("userid",false,array("title"=>"Userid"));
$matrix->widgets[] = new Label("username",false,array("title"=>"Username"));
$matrix->widgets[] = new Label("logins",false,array("title"=>"Logins"));
$matrix->widgets[] = new Date("lastlogin",false,array("title"=>"Last Login"));
$matrix->widgets[] = new BitwiseText("acclevel",false,array("title"=>"Booking Access"),false,false,get_defined_access("hlbook","hlbook"));
$matrix->widgets[] = new BitwiseText("acclevel",false,array("title"=>"Admin Access"),false,false,get_defined_access("hladmin","hlbook"));

$stats = new Matrix("ownerstats",
		array("draw_head"=>true,"draw_labels"=>false,"rtype"=>"row","left_width"=>"90%","right_width"=>"10%"),
		false,
		"sql:SELECT * FROM hlbook_Users WHERE userid='" . userid() . "'");

$stats->header = new SimpleText("header","Your Booking Profile");
$stats->widgets[] = new Date("expdate",false,array("title"=>"Expiration Date"));
$stats->widgets[] = new Label("maxscheduled",false,
		array("default"=>"Unlimited","title"=>"Max Scheduled","notes"=>"Maximum number of outstanding scheduled bookings"));

$stats->widgets[] = new Label("maxbooks",false,array("default"=>"Unlimited","title"=>"Max Books","Maximum number of bookings for this account."));
$stats->widgets[] = new Label("prebook",false,array("default"=>get_option("hlbook","prebook"),"title"=>"Prebook Time","notes"=>"Number of days before hand you may prebook a server"),
		array("body.td.align"=>"left"));

$form = new Matrix("editownerprofile",
		array("draw_head"=>true,"draw_labels"=>false,"rtype"=>"row"),
		array("body.table.width"=>"100%"),
		"sql:SELECT * FROM auth_Users WHERE userid='" . userid() . "'");

$form->header = new SimpleText("header","Profile Options");

$form->widgets[] = new TextBox("displayname",array("size"=>32,"maxlength"=>32),array("title"=>"Display Name or Alias","required"=>true,"regexpreset"=>"words"));
$form->widgets[] = new Password("password",array("size"=>32,"maxlength"=>32),array("in_crypt"=>true,"hide"=>true,"title"=>"Login Password","required"=>true));
$form->widgets[] = new TextBox("email",array("size"=>32,"maxlength"=>64),array("title"=>"Email Address","required"=>true,"regexpreset"=>"email"));
$form->widgets[] = new Checkbox("massmail",array("title"=>"Send me bookings updates via email when posted"));
$form->footer = new Submit("Submit",false,array("foot.td.align"=>"center"),"Apply");

if($_POST["Submit"] == "Apply")
{
	$input = new RowInput(&$form->widgets,"auth_Users","userid",userid());

	if($input->update())
	{
		$form->rows[] = new Message("ok","ok","Your profile has been updated");
	}
	else
	{
		$form->rows[] = new Message("caution","caution",$input->error());
	}
}

?>
<table width="100%" cellpadding="0">
<tr><td width="50%" valign="top" style="padding-right: 3px;"><? $matrix->draw(); ?></td>
<td width="50%" valign="top" style="padding-left: 3px;"><? $stats->draw(); ?></td>
</tr></table><br>
<?
print form("userprofile");

$form->draw();
print "</form>";

?>
