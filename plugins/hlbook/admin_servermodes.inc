<?
$host = get_servername($_GET["serverid"]);
if(!$host) {
	print message("caution","That server does not exist");
	return 0;
}

$modid = get_server_modid($_GET["serverid"]);

$table = new AdvancedTable("servermodes",array("keycol"=>"modeid","draw_new"=>true),false,"sql:SELECT * FROM hlbook_ServerModes WHERE serverid='" . $_GET["serverid"] . "' ORDER BY start ASC");
$table->header = new SimpleText(false,"Server Modes for $host");
$table->rows[] = new SimpleText(false,"Server modes explained.  Server modes make it possible to setup part
time bookable servers.  All servers default to the bookable server mode.  Using a 24 hour time format, you
can specify times in which the server should change modes, what mode to change to, new rcon and server passwords,
and what config and map to execute on the change.");

$table->widgets[] = new Select("action",false,array("title"=>"Mode","required"=>true),false,false,array("bookable"=>"bookable","private"=>"private","public"=>"public","offline"=>"offline"));
$table->widgets[] = new TextBox("start",array("size"=>10,"maxlength"=>8),array("title"=>"Start Time","required"=>true,"regexfilter"=>"^[\d]{2}:[\d]{2}:[\d]{2}$"));
$table->widgets[] = new TextBox("svpasswd",array("size"=>"16","maxlength"=>16),array("title"=>"Server Password","required"=>false,"regexpreset"=>"alphanum"));
$table->widgets[] = new TextBox("rcon",array("size"=>"16","maxlength"=>16),array("title"=>"Rcon Password","required"=>true,"regexpreset"=>"alphanum"));
$table->widgets[] = new Select("map",false,array("title"=>"Map","required"=>true,"regexpreset"=>"alphanum"),false,false,"sql:SELECT name,name as map FROM hlbook_Maps WHERE modid='$modid'");
$table->widgets[] = new Select("config",false,array("title"=>"Config","required"=>false),false,false,"sql:SELECT filename,title FROM hlbook_Configs WHERE modid='$modid'");

$table->widgets[] = new Checkbox("delete",
		array("title"=>"Delete","write"=>false,"exempt_new"=>true),
		array("body.td.align"=>"center","labels.td.align"=>"center"));

$table->widgets[] = new Hidden("serverid",false,false,$_GET["serverid"]);

$table->footer = new Submit("Submit",false,array("foot.td.align"=>"center"),"Apply");

if($_POST)
{
	$input = new MultiRowInput(&$table->widgets,"hlbook_ServerModes","modeid",array("owner"=>userid(),"new_allow"=>true,"new_activate"=>"start","allow_del"=>true));

	if($input->update())
		$table->rows[] = new Message("ok","ok","Server modes updated");
	else
		$table->rows[] = new Message("cation","caution",$input->error());
}


print form("modes");
$table->draw();
print "</form>";
