<?

global $DB;

if(acclevel() & get_defined_access("hladmin","hlbook"))
{
	$result = $DB->query("SELECT hlbook_Bookings.*,
			auth_Users.displayname,auth_Users.email,
			hlbook_Servers.*,CONCAT(hlbook_Servers.ip,':',hlbook_Servers.port) as ipaddr
			FROM hlbook_Bookings,auth_Users,hlbook_Servers 
			WHERE hlbook_Bookings.matchid='" . $_GET["bookid"] . "' 
				&& hlbook_Bookings.userid=auth_Users.userid
				&& hlbook_Bookings.serverid=hlbook_Servers.serverid LIMIT 1");
}
else
{
	$result = $DB->query("
			SELECT hlbook_Bookings.*,
			auth_Users.displayname,auth_Users.email,
			hlbook_Servers.*,CONCAT(hlbook_Servers.ip,':',hlbook_Servers.port) as ipaddr
			FROM hlbook_Bookings,auth_Users,hlbook_Servers 
			WHERE hlbook_Bookings.matchid='" . $_GET["bookid"] . "' 
				&& hlbook_Bookings.userid='" . userid() . "' 
				&& hlbook_Bookings.userid=auth_Users.userid
				&& hlbook_Bookings.serverid=hlbook_Servers.serverid LIMIT 1");	
}

if($DB->num_rows($result) != 1)
{
	print message("stop","This match does not exist.");
	return 0;
}

$r = $DB->fetch_assoc($result);
$DB->reset($result);

if($r['status'] != "C")
{
	print message("stop","This booking is not closed yet.");
	return false;
}

$details = new Matrix("details",
		array("draw_head"=>true,"draw_labels"=>false,"rtype"=>"row"),
		array("body.table.width"=>"100%"),
		$result);

$details->header = new SimpleText("header","Booking Details");
$details->widgets[] = new Label("servername",false,array("title"=>"Hostname"));
$details->widgets[] = new Date("initdate",false,array("title"=>"Start Time","format"=>"l, M jS Y g:i A"));
$details->widgets[] = new Date("initdate",false,array("title"=>"End time","out_add"=>"%[timeblock]","format"=>"l, M jS Y g:i A"));
$details->widgets[] = new Date("posted",false,array("title"=>"Booked On","format"=>"l, M jS Y g:i A"));
$details->widgets[] = new Label("ipaddr",false,array("title"=>"Server IP Address"));
$details->widgets[] = new Label("players",false,array("title"=>"Player Count"),false,"sql:SELECT count(*) FROM hlbook_Players WHERE matchid='%[matchid]'");

$logfile = "match_" . $r["matchid"] . ".zip";

# if logs are stored on web server
if(get_option("hlbook","logstore") == "local") {

	$size = log_file_exists($r["matchid"]);

	if($size) {

		$details->widgets[] = new Label("size",false,array("title"=>"Log File Size"),false,convert_file_size($size));
		$details->widgets[] = new Label(
				"link",
				get_option("hlbook","logurl") . $logfile,
				array("title"=>"Download Logs"),
				false,
				$logfile);
	}
}
elseif(get_option("hlbook","logstore") == "remote")  {

	$details->widgets[] = new Label(
			"link",
			get_option("hlbook","logurl") . $logfile,
			array("title"=>"Download Logs"),
			false,
			$logfile);

}	

$details->draw();

print br(1);

$endmatch = $r["initdate"] + $r["timeblock"];
$players = new SimpleTable("details",
		false,
		array("body.table.width"=>"100%"),
		"sql:SELECT * FROM hlbook_Players WHERE matchid='" . $r["matchid"] . "' && serverid='" . $r["serverid"] . "'");

$players->header = new SimpleText("header","Players");
$players->widgets[] = new Label("wonid",false,array("title"=>"Wonid"),array("body.td.width"=>"10%"));
$players->widgets[] = new Label("name",false,array("title"=>"Name"));
$players->widgets[] = new Label("frags",false,array("title"=>"Frags","default"=>'0'),array("body.td.align"=>"center","labels.td.align"=>"center"));
$players->widgets[] = new Label("deaths",false,array("title"=>"Deaths","default"=>'0'),array("body.td.align"=>"center","labels.td.align"=>"center"));
$players->draw();



?>
