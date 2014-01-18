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

list($ip,$port) = get_server_addr($b["serverid"]); 

$rcon = new Rcon($ip,$port);
$rcon->status_cmd("info");
$rcon->status_cmd("players");

$table = new Matrix("cmds",
		array("class"=>"Table"),
		array("body.table.width"=>"90%"),
		$rcon->info);

$table->header = new SimpleText(false,"Server Status for $ip:$port");
$table->rows[] = new Image("Map",false,array("src"=>BASE_URL . "hlbook/hlmaps/" . $rcon->info["map"] . ".jpg"),false,array("row.td.align"=>"center"));
$table->widgets[] = new Label("Server IP",false,array("title"=>"IP Address"));
$table->widgets[] = new Label("Hostname",false,array("title"=>"Hostname"));
$table->widgets[] = new Label("map",false,array("title"=>"Map"));
$table->widgets[] = new Label("Password",false,array("title"=>"Password On"));
$table->widgets[] = new Label("Mod",false,array("title"=>"Mod"),false,"sql:SELECT title FROM hlbook_Mods WHERE modid='%[Mod]'");
$table->widgets[] = new Label("Cur. player",false,array("default"=>"0","title"=>"Current Players"));
$table->widgets[] = new Label("Max. player",false,array("title"=>"Max Players"));

$players = new SimpleTable("cmds",
		array("class"=>"Table"),
		array("body.table.width"=>"90%"),
		$rcon->players
	);
$players->header = new SimpleText(false,"Players");
$players->widgets[] = new Label("player",false,array("title"=>"Player"));
$players->widgets[] = new Label("time",false,array("title"=>"Time"));
$players->widgets[] = new Label("frags",false,array("default"=>0,"title"=>"Frags"));

?>

<table width="100%">
<tr><td width="50%" valign="top">
<?
$table->draw();
?></td>
<td width="50%" valign="top">
<? $players->draw(); 
?>
</td></tr></table>
