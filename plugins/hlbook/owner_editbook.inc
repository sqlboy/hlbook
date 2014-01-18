<?

if(!secure_get_book_by_userid(&$book,$_GET["bookid"]))
{
	print message("caution","You do not have access to this server booking or this booking does not exist.");
	return false;
}

if($book["status"] != "S") {
	print message("caution","You cannot edit a book that is already closed or in progress");
	return 0;	
}

$bookid = $book["matchid"];
$midnight = set_date_midnight($book["initdate"]);

$form = new Matrix("editbook",
		array("draw_head"=>true,"draw_labels"=>false,"rtype"=>"row"),
		array("body.table.width"=>"100%"),
		$book);

$form->header = new SimpleText("header","Modify Book " . $book["servername"]);
$form->widgets[] = new Label("userid",false,array("title"=>"Booked By"),fasle,"sql:SELECT displayname FROM auth_Users WHERE userid='%[userid]'");
$form->widgets[] = new Date("initdate",false,array("title"=>"Booked On","format"=>"l, M jS Y g:i A"));
$form->widgets[] = new TextBox("servername",array("size"=>32,"maxlength"=>64),array("title"=>"Server Name","required"=>true,"regexpreset"=>"words","sticky"=>true));
$form->widgets[] = new TextBox("svpasswd",array("size"=>16,"maxlength"=>16),array("title"=>"Connect Password:","required"=>true,"sticky"=>true,"regexpreset"=>"words"));
$form->widgets[] = new TextBox("rcon",array("size"=>16,"maxlength"=>16),array("title"=>"Rcon","required"=>true,"sticky"=>true,"regexpreset"=>"words"));
$form->widgets[] = new Select("map",false,array("title"=>"Start Map","required"=>true,"sticky"=>true,"notes"=>"The server will enter your booking time on this map"),false,false,"sql:SELECT name,name FROM hlbook_Maps WHERE modid='" . $book["modid"] . "'");
$form->widgets[] = new Select("config",false,array("title"=>"Configuration","required"=>false,"sticky"=>true,"notes"=>"The server will default to this configuration"),false,false,"sql:SELECT filename,title FROM hlbook_Configs WHERE modid='" . $book["modid"] . "'");


$form->footer = new Submit("Submit",false,array("foot.td.align"=>"center"),"Apply");

if($_POST["Submit"] == "Apply")
{
	$input = new RowInput(&$form->widgets,"hlbook_Bookings","matchid",$bookid);

	if($input->update())
	{
		print $input->query;
		$form->rows[] = new Message("ok","ok","Your booking has been updated");
	}
	else
	{
		$form->rows[] = new Message("caution","caution",$input->error());
	}
}
print form("edit",FULL_URL);
$form->draw();
print "</form>";
