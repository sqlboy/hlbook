<?

$servername = get_servername($_GET["serverid"]);
if(!$servername) {
	print message("caution","That server does not exist");
	return 0;
}

$matrix = new Matrix("adminserveredit",false,false,"sql:SELECT * FROM hlbook_Servers WHERE serverid='" . $_GET["serverid"] . "'");
$matrix->header = new SimpleText("header","Edit Server");

$matrix->widgets[] = new TextBox("hostname",array("maxlength"=>"48"),array("title"=>"Server Name","sticky"=>true,"required"=>true,"regexpreset"=>"words"));
$matrix->widgets[] = new TextBox("ip",array("maxlength"=>"15"),array("title"=>"IP Address","sticky"=>true,"required"=>true,"regexpreset"=>"ipaddr"));
$matrix->widgets[] = new TextBox("port",array("size"=>8,"maxlength"=>"5"),array("default"=>27015,"title"=>"TCP Port","sticky"=>true,"required"=>true,"regexpreset"=>"num"));
$matrix->widgets[] = new Select("modid",false,array("title"=>"Mod","sticky"=>true,"required"=>true),false,false,"sql:SELECT modid,title FROM hlbook_Mods");
$matrix->widgets[] = new Select("protocol",false,array("title"=>"Protocol","sticky"=>true,"required"=>true),false,false,"list:hl/Half-Life;quake/Quake II-III");
$matrix->widgets[] = new TextBox("defrcon",array("maxlength"=>"24"),array("notes"=>"The rcon password the server will default to if it crashes","title"=>"Default Rcon Password","sticky"=>true,"required"=>true,"regexpreset"=>"alphanum"));
$matrix->widgets[] = new TextBox("rcon",array("maxlength"=>"24"),array("notes"=>"The servers current RCON password.","title"=>"Rcon Password","sticky"=>true,"required"=>true,"regexpreset"=>"alphanum"));
$matrix->widgets[] = new Select("timeblock",false,array("default"=>"7200","title"=>"Time Block","sticky"=>true,"required"=>true,"regexfilter"=>"^[0-9]{1,5}$"),false,false,"list:1800/30 Minutes;3600/1 Hour;5400/1.5 Hours;7200/2 Hours;10800/3 Hours;14400/4 Hours");
$matrix->widgets[] = new Checkbox("disabled",array("default"=>'0',"null"=>true,"sticky"=>true,"title"=>"Disabled?"));

$matrix->footer = new Submit("Submit",false,array("foot.td.align"=>"center"),"Apply");

if($_POST["Submit"])
{
	$input = new RowInput(&$matrix->widgets,"hlbook_Servers","serverid",$_GET["serverid"]);

	if($input->update())
		$matrix->rows[] = new Message("ok","ok","Server config updated");
	else
		$matrix->rows[] = new Message("caution","caution",$input->error());
}

print form("addserver");
$matrix->draw();
print "</form>";
?>
