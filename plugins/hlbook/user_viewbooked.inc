<?
global $DB;

$result = $DB->query("SELECT hlbook_Bookings.*,
		hlbook_Mods.title,
		auth_Users.displayname,auth_Users.email,
		hlbook_Servers.*,CONCAT(hlbook_Servers.ip,':',hlbook_Servers.port) as ipaddr
		FROM hlbook_Bookings,auth_Users,hlbook_Servers,hlbook_Mods
		WHERE hlbook_Bookings.matchid='" . $_GET["bookid"] . "' 
			&& hlbook_Servers.modid=hlbook_Mods.modid
			&& hlbook_Bookings.userid=auth_Users.userid
			&& hlbook_Bookings.serverid=hlbook_Servers.serverid LIMIT 1");

$r = $DB->fetch_assoc($result);
$DB->reset($result);

if($r['status'] != "S")
{
	print message("stop","This booking is not in scheduled mode.");
	return false;
}

$details = new Matrix("details",
		array("draw_head"=>true,"draw_labels"=>false),
		array("body.table.width"=>"100%"),
		$result);

$details->header = new SimpleText("header","Booking Details");
$details->widgets[] = new Label("servername",false,array("title"=>"Server Name:"));
$details->widgets[] = new Date("initdate",false,array("title"=>"Start Time:","format"=>"l, M jS Y g:i A"));
$details->widgets[] = new Date("initdate",false,array("title"=>"End Time:","out_add"=>"%[timeblock]","format"=>"l, M jS Y g:i A"));
$details->widgets[] = new Date("posted",false,array("title"=>"Booked At:","format"=>"l, M jS Y g:i A"));
$details->widgets[] = new Label("userid",false,array("title"=>"Booked By:"),false,"sql:SELECT displayname FROM auth_Users WHERE userid='%[userid]'");
$details->widgets[] = new Label("ipaddr",false,array("title"=>"IP Address:"));
$details->widgets[] = new Label("title",false,array("title"=>"Mod:"));
$details->draw();
?>
