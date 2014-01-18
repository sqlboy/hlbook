<?

if(!secure_get_book_by_userid($b,$_GET["bookid"]))
{
	print message("caution","That booking does not exist");
	return 0;
}

if($b["status"] != "P" && $b["status"] != "R") {
	print message("caution","That booking is not in progress");
	return 0;
}

list($ip,$port,$rcon) = get_server_addr($b["serverid"]); 
$rcon = new Rcon($ip,$port,$rcon);
$rcon->status_cmd("players");

$table = new Browser("cmds",
		array("class"=>"Table","draw_cols"=>2,"rtype"=>"row"),
		array("body.table.width"=>"90%"));

$table->header = new SimpleText(false,"Server Control: " . $b["servername"] . " on $ip:$port");

$table->widgets[] = new Submit("change",false,false,"Change Map");
$table->widgets[] = new Select("map",false,array("required"=>true),false,false,"sql:SELECT name,name FROM hlbook_Maps WHERE modid='" . $b["modid"] . "'");

$table->widgets[] = new Submit("change",false,false,"Exec Config");
$table->widgets[] = new Select("config",false,array("required"=>true),false,false,"sql:SELECT filename,title FROM hlbook_Configs WHERE modid='" . $b["modid"] . "'");

$table->widgets[] = new Submit("change",false,false,"Set Server Password");
$table->widgets[] = new TextBox("svpass",array("maxlength"=>"16","size"=>"16"),array("required"=>true));

$players = new SimpleTable("cmds",
		array("class"=>"Table"),
		array("body.table.width"=>"90%"),
		$rcon->players
	);
$players->header = new SimpleText(false,"Players");
$players->widgets[] = new Label("player",false,array("addslashes"=>true,"title"=>"Player"));
$players->widgets[] = new Label("time",false,array("title"=>"Time"));
$players->widgets[] = new Label("frags",false,array("default"=>0,"title"=>"Frags"));
$players->widgets[] = new Checkbox("player[]",array("set_val"=>true,"title"=>"Kick"),false,"%[player]");
$players->footer = new Submit("Submit",false,array("foot.td.align"=>"center"),"Kick");

if($_POST)
{
	if($_POST["Submit"]) {

		if(is_array($_POST["_player"])) {

			foreach ($_POST["_player"] as $player) {

				$wonid = player_exists($b["matchid"],$b["serverid"],$player);

				if($wonid) {
					$rcon->rcon_cmd("kick $wonid");
				}
			}
		}
	}

	if($_POST["change"]) {

		switch($_POST["change"])
		{
			case "Set Server Password":

				if(preg_match("/^([a-z0-9_]+)$/",$_POST["svpass"],$match) && is_svpassword_ok($_POST["svpass"])) {

					$new_pass = $match[1];
					$rcon->rcon_cmd("sv_password $new_pass");
					update_svpassword($b["matchid"],$new_pass);
					$table->rows[] = new Message("ok","ok","Set server connect password");
				}
				else
				{
					$table->rows[] = new Message("err","caution","Invalid server password");
				}
				break;

			case "Change Map":

				if(preg_match("/^([a-z0-9_]+)$/",$_POST["map"],$match)) {
					$new_map = $match[1];
					$rcon->rcon_cmd("changelevel $new_map");
					$table->rows[] = new Message("ok","ok","Map changed to " . $_POST["map"]);
				}
				else
				{
					$table->rows[] = new Message("err","caution","Invalid map");
				}
				break;
		}
	}
}
?>
<table width="100%">
	<tr><td width="50%" valign="top">
	<?
print form("smon");
$table->draw();
print "</form>";
?></td>
<td width="50%" valign="top">
<? 
print form("kick");
$players->draw();
print "</form>";
?>
</td></tr></table>
