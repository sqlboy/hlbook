<?

$table = new AdvancedTable("addressbook",array("keycol"=>"addrid","draw_new"=>true),false,"sql:SELECT * FROM hlbook_AddrBook WHERE userid='" . userid() . "'");
$table->header = new SimpleText(false,"Address Book");

$table->widgets[] = new TextBox("label",array("maxlength"=>32),array("title"=>"Name","required"=>true));
$table->widgets[] = new TextBox("email",array("maxlength"=>64),array("title"=>"Email Address","required"=>true,"regexpreset"=>"email"));

$table->widgets[] = new Checkbox("delete",
		array("title"=>"Delete","write"=>false,"exempt_new"=>true),
		array("body.td.align"=>"center","labels.td.align"=>"center"));

$table->widgets[] = new Hidden("userid",false,false,userid());
$table->footer = new Submit("Submit",false,array("foot.td.align"=>"center"),"Apply");

if($_POST)
{
	$input = new MultiRowInput(&$table->widgets,"hlbook_AddrBook","addrid",
			array("update_where"=>"&& userid='" . userid() . "'","new_allow"=>true,"new_activate"=>"label","allow_del"=>true));

	if($input->update())
		$table->rows[] = new Message("ok","ok","Address Book Updated");
	else
		$table->rows[] = new Message("cation","caution",$input->error());
}


print form("modes");
$table->draw();
print "</form>";
