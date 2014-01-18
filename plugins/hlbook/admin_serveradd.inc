<?

function create_server_settings($serverid)
{
	global $DB;

	$result = $DB->query("INSERT INTO hlbook_ServerProps (serverid) values ('$serverid')");
	if($result) {
		return true;
	}

	return false;
}

function create_default_mode($serverid,$rcon)
{
	global $DB;

	$svpass = md5($rcon);
	$svpass = substr($svpass,1,10);
	$result = $DB->query("INSERT INTO hlbook_ServerModes(serverid,svpasswd,rcon)
			values ('" . $serverid . "','" . $svpass . "','" . $rcon . "')");

	return true;
}

$matrix = new Matrix("adminserveradd");
$matrix->header = new SimpleText("header","Add New Server");

$matrix->widgets[] = new TextBox("hostname",array("maxlength"=>"48"),array("title"=>"Server Name","sticky"=>true,"required"=>true));
$matrix->widgets[] = new TextBox("ip",array("maxlength"=>"15"),array("title"=>"IP Address","sticky"=>true,"required"=>true,"regexpreset"=>"ip"));
$matrix->widgets[] = new TextBox("port",array("size"=>8,"maxlength"=>"5"),array("default"=>27015,"title"=>"TCP Port","sticky"=>true,"required"=>true,"regexpreset"=>"num"));
$matrix->widgets[] = new Select("modid",false,array("title"=>"Mod","sticky"=>true,"required"=>true),false,false,"sql:SELECT modid,title FROM hlbook_Mods");
$matrix->widgets[] = new Select("protocol",false,array("title"=>"Protocol","sticky"=>true,"required"=>true),false,false,"list:hl/Half-Life;quake/Quake II-III");

$matrix->widgets[] = new TextBox("defrcon",array("maxlength"=>"24"),array("notes"=>"The rcon password the server will default to if it crashes","title"=>"Default Rcon Password","sticky"=>true,"required"=>true,"regexpreset"=>"alphanum"));
$matrix->widgets[] = new TextBox("rcon",array("maxlength"=>"24"),array("notes"=>"Set same as above","title"=>"Rcon Password","sticky"=>true,"required"=>true,"regexpreset"=>"alphanum"));
$matrix->widgets[] = new Select("timeblock",false,array("default"=>"7200","title"=>"Time Block","sticky"=>true,"required"=>true,"regexfilter"=>"^[0-9]{1,5}$"),false,false,"list:1800/30 Minutes;3600/1 Hour;5400/1.5 Hours;7200/2 Hours;10800/3 Hours;14400/4 Hours");

$matrix->footer = new Submit("Submit",false,array("foot.td.align"=>"center"),"Apply");

if($_POST["Submit"])
{
	$input = new RowInput(&$matrix->widgets,"hlbook_Servers","serverid",false);

	if($serverid = $input->update()) {
		$matrix->rows[] = new Message("ok","ok","Server created.  Create another?");

		create_server_settings($serverid);
		create_default_mode($serverid,$_POST["defrcon"]);
	}

	else
		$matrix->rows[] = new Message("caution","caution",$input->error());
}

print form("userid");
$matrix->draw();
print "</form>";
?>
