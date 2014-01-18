<?
global $DB,$AUTH;

$result = $DB->query("SELECT 
hlbook_ServerProps.*,hlbook_Servers.*,hlbook_Mods.title
FROM hlbook_ServerProps,hlbook_Servers,hlbook_Mods
WHERE hlbook_Mods.modid=hlbook_Servers.modid 
&& hlbook_ServerProps.serverid=hlbook_Servers.serverid 
&& hlbook_Servers.serverid='" . $_GET["serverid"] . "'");

$r = $DB->fetch_assoc($result);

if($r["disabled"] == 1)
{
	print message("caution","This server has been disabled");
	return 0;
}

/* some basic checks we do here */
if(!is_valid_bookable_date($_GET["date"],$r["timeblock"]))
{
	print message("caution","Please, do not tamper with the date.");
	return 0;
}

if(!is_acclevel_good())
{
	print message("caution","You do not have the required access to book servers.");
	return 0;
}

if(get_mode_by_time($_GET["date"],$r["serverid"]) != "bookable")
{
	print message("caution","This server is not bookable during that time frame.");
	return 0;
}

/* Set some stuff */
$date = $_GET["date"];
$midnight = set_date_midnight($date);
$serverid = $r["serverid"];

$stats = new Matrix("stats",array("clean"=>false),array("body.table.width"=>"100%"),$result);
$stats->header = new SimpleText("hostname",false,$r["hostname"] . " (" . $r["title"] . ")");
$stats->widgets[] = new Label("cpu","CPU:");
$stats->widgets[] = new Label("os","OS:");
$stats->widgets[] = new Label("connection","Connection:");
$stats->widgets[] = new Label("location","Location:");

#form
$form = new Matrix("reserve",false,array("body.table.width"=>"100%"));
$form->header = new SimpleText("header","Booking Server for: " . date("l, M jS Y g:i A",$date) . " for " . sprintf("%0.2f",$r["timeblock"]/3600) . " hours");
$form->widgets[] = new TextBox("servername",array("size"=>32,"maxlength"=>64),array("title"=>"Server Name","required"=>true,"regexpreset"=>"words","sticky"=>true));
$form->widgets[] = new TextBox("svpasswd",array("size"=>16,"maxlength"=>16),array("title"=>"Connect Password:","required"=>true,"sticky"=>true,"regexpreset"=>"words"));
$form->widgets[] = new TextBox("rcon",array("size"=>16,"maxlength"=>16),array("title"=>"Rcon","required"=>true,"sticky"=>true,"regexpreset"=>"words"));
$form->widgets[] = new Select("map",false,array("title"=>"Start Map","required"=>true,"sticky"=>true,"notes"=>"The server will enter your booking time on this map"),false,false,"sql:SELECT name,name FROM hlbook_Maps WHERE modid='" . $r["modid"] . "'");
$form->widgets[] = new Select("config",false,array("title"=>"Configuration","required"=>false,"sticky"=>true,"notes"=>"The server will default to this configuration"),false,false,"sql:SELECT filename,title FROM hlbook_Configs WHERE modid='" . $r["modid"] . "'");
$form->widgets[] = new Checkbox("notify",array("title"=>"Notify Address Book","sticky"=>true,"notes"=>"Checking this box will send a email notifitcation to everyone in your address book with details on this booking.  This information does not include the rcon password.","write"=>false));
$form->widgets[] = new TextArea("comments",false,array("allow"=>"","title"=>"Comments","sticky"=>true),array("body.td.valign"=>"top"));
$form->widgets[] = new Hidden("initdate",array("required"=>true),false,$date);
$form->widgets[] = new Hidden("posted",array("required"=>true),false,time());
$form->widgets[] = new Hidden("serverid",array("required"=>true),false,$r["serverid"]);
$form->widgets[] = new Hidden("userid",array("required"=>true),false,userid());
$form->widgets[] = new Hidden("modid",array("required"=>true),false,$r["modid"]);
$form->widgets[] = new Hidden("timeblock",array("required"=>true),false,$r["timeblock"]);

$form->footer = new Submit("Submit",false,array("foot.td.align"=>"center"),"Reserve Server");

if($_POST["Submit"] == "Reserve Server")
{
	$input = new RowInput(&$form->widgets,"hlbook_Bookings","id",false);

	/* All the Date checks */
	if(!is_valid_bookable_date($_POST["initdate"],$r["timeblock"])) {
		$input->errors[] = "Your booking date is invalid.  Please do not tamper with the URL";
	}

	if(!is_date_old($_POST["initdate"],$r["timeblock"])) {
		$input->errors[] = "You can't book that date, the slot has already passed.";
	}

	if(!is_date_new($_POST["initdate"])) {
		$input->errors[] = "That date is too new.  Try booking a date within your prebook time.";
	}

	if(!is_date_available($_POST["initdate"],$_POST["serverid"])) {
		$input->errors[] = "That date is already reserved by someone else.";
	}

	/* User Checks */
	if(is_maxbooks_exceeded()) {
		$input->errors[] = "Your maximum number of overall slots has already been booked: " . $AUTH->get_ccfgvar("maxbooks") . " slot";
	}

	if(is_maxscheduled_exceeded()) {
		$input->errors[] = "You can only have " . $AUTH->get_ccfgvar("maxscheduled") . " outstanding booking(s) at a time.";
	}

	if(is_user_expired()) {
		$input->errors[] = "Your bookings account is expired.";
	}

	if(get_mode_by_time($_POST["initdate"],$r["serverid"]) != "bookable") {
		$input->errors[] = "This server is not bookable during this time frame.";
	}

	#fix this
	if($bookid = $input->update()){

		$result = $DB->query("
			SELECT hlbook_Bookings.*,
				auth_Users.displayname,auth_Users.email,
				CONCAT(hlbook_Servers.ip,':',hlbook_Servers.port) as ipaddr,
				hlbook_Mods.title
			FROM hlbook_Bookings,auth_Users,hlbook_Servers,hlbook_Mods
			WHERE hlbook_Bookings.matchid='" . $bookid . "' 
					&& hlbook_Bookings.userid='" . userid() . "' 
					&& hlbook_Bookings.userid=auth_Users.userid
					&& hlbook_Mods.modid=hlbook_Bookings.modid
					&& hlbook_Bookings.serverid=hlbook_Servers.serverid");

		if($DB->num_rows($result) && $bookid)
		{
			$confirm = new Matrix("exit",
					array("draw_head"=>true,"draw_labels"=>false,"rtype"=>"row"),
					array("body.table.width"=>"100%"),
					$result);

			$confirm->header = new SimpleText("header","Sucessfully booked server " . date("l, M jS Y g:i A",$date));

			$confirm->widgets[] = new Label("displayname",false,array("title"=>"Booked By"));
			$confirm->widgets[] = new Date("initdate",false,array("title"=>"Start Time","format"=>"l, M jS Y g:i A"));
			$confirm->widgets[] = new Date("initdate",false,array("title"=>"End Time","out_add"=>"%[timeblock]","format"=>"l, M jS Y g:i A"));
			$confirm->widgets[] = new Date("posted",false,array("title"=>"Booked On","format"=>"l, M jS Y g:i A"));
			$confirm->widgets[] = new Label("ipaddr",false,array("title"=>"Ip Address"));
			$confirm->widgets[] = new Label("modid",false,array("title"=>"Mod/Game"),false,"sql:SELECT title FROM hlbook_Mods WHERE modid='%[modid]'");
			$confirm->widgets[] = new Label("servername",false,array("title"=>"Server Name"));
			$confirm->widgets[] = new Label("svpasswd",false,array("title"=>"Server Password"));
			$confirm->widgets[] = new Label("rcon",false,array("title"=>"Rcon Password"));

			$confirm->draw();

			if($_POST["notify"] && get_option("hlbook","email_allow")){
				$DB->reset($result);
				$bdat = $DB->fetch_assoc($result);
				secure_email_address_book(&$bdat);
			}
			return 1;
		}
		else
		{
			#if the book goes thourgh but we can't select the data afterward...something strange happened.
			error("There was a strange and mysterious error booking your time slot.","Fatal Booking Error");
			return 0;
		}
	}
	else
		$form->rows[] = new SimpleText("message",message("caution",$input->error()));
}
?>
<table width="100%" cellspacing="0" cellpadding="0" border="0">
<tr><td width="50%" valign="top" align="left"><?show_server_stats($r["serverid"],"99%");?></td>
<td width="50%" valign="top" align="right"><?show_server_trends($r["serverid"],"99%",0);?></td></tr></table>
<br>
<?
print form("reserve");
$form->draw();
print "</form>";
