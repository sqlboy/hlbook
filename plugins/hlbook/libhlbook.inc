<?
include BASE_PATH . "phprcon/rcon.php";

function secure_get_book_by_userid(&$p_row,$bookid)
{
	global $DB;

	if(!is_numeric($bookid))
		return false;

	if(acclevel() & get_defined_access("hladmin","hlbook"))
		$result = $DB->query("SELECT * FROM hlbook_Bookings WHERE matchid='" . $bookid . "' LIMIT 1");
	elseif(acclevel() & get_defined_access("hlbook","hlbook"))
		$result = $DB->query("SELECT * FROM hlbook_Bookings WHERE matchid='" . $bookid . "' && userid='" . userid() . "' LIMIT 1");
	else
	{
		return false;
	}

	$count = $DB->num_rows($result);
	$p_row = $DB->fetch_assoc($result);	
	$DB->free_result($result);

	if($count) {
		return 1;
	}
	return 0;
}

function get_book(&$p_row,$bookid)
{
	global $DB;

	if(!is_numeric($bookid))
		return false;

	$result = $DB->query("SELECT * FROM hlbook_Bookings WHERE matchid='" . $bookid . "' LIMIT 1");
	$count = $DB->num_rows($result);
	$p_row = $DB->fetch_assoc($result);	
	$DB->free_result($result);

	if($count) {
		return 1;
	}
	return 0;
}

function create_thumb_dir($matchid)
{
	$path = get_option("hlbook","scrn_thumb_path");

	if(is_dir($path) && is_writable($path)) {
		$mk = mkdir($path . "/" . $matchid);
		return $mk;
	}

	return 0;
}

function create_full_dir($matchid)
{
	$path = get_option("hlbook","scrn_thumb_path");

	if(is_dir($path) && is_writable($path)) {
		$mk = mkdir($path . "/" . $matchid);
		return $mk;
	}

	return 0;
}

function is_valid_bookid($bookid)
{
	global $DB;

	if(!is_numeric($bookid))
		return false;

	$result = $DB->query("SELECT count(*) FROM hlbook_Bookings WHERE matchid='" . $bookid . "' LIMIT 1");
	list($count) = $DB->fetch_row($result);
	$DB->free_result($result);

	return $count;
}

function is_owner($bookid)
{
	global $DB;

	$result = $DB->query("SELECT count(*) FROM hlbook_Bookings WHERE matchid='" . $bookid . "' && userid='" . userid() . "' LIMIT 1");
	list($count) = $DB->fetch_row($result);
	$DB->free_result($result);

	if($count)
		return true;

	return false;
}

function is_valid_mod($modid)
{
	global $DB;

	$result = $DB->query("SELECT count(modid) as count FROM hlbook_Mods WHERE modid='" . $modid . "'");
	if(list($count) = $DB->fetch_row($result));

	if($count == 1)
		return true;

	return false;
}

function get_servername($serverid)
{
	global $DB;

	$result = $DB->query("SELECT hostname FROM hlbook_Servers WHERE serverid='" . $serverid  . "'");
	list($name) = $DB->fetch_row($result);
	if($DB->num_rows($result) == 0) {
		return false;
	}

	$DB->free_result($result);
	return $name;
}

function get_matchname($matchid)
{
	global $DB;

	$result = $DB->query("SELECT servername FROM hlbook_Bookings WHERE matchid='" . $matchid  . "'");
	list($name) = $DB->fetch_row($result);
	if($DB->num_rows($result) == 0) {
		return false;
	}

	$DB->free_result($result);
	return $name;
}

function get_mod($modid)
{
	global $DB;

	$result = $DB->query("SELECT title FROM hlbook_Mods WHERE modid='" . $modid . "'");
	list($title) = $DB->fetch_row($result);
	$DB->free_result($result);

	if($title)
		return $title;

	return false;
}

function get_server_modid($serverid)
{
	global $DB;

	$result = $DB->query("SELECT modid FROM hlbook_Servers WHERE serverid='" . $serverid . "'");
	list($modid) = $DB->fetch_row($result);
	$DB->free_result($result);

	if($modid) {
		return $modid;
	}

	return false;
}

function is_valid_midnight($date)
{
	if(!preg_match("/^[0-9]+$/",$date))
		return false;

	$date = date("His",$date);

	if($date == "000000" && strlen($date) == 6)
	{
		return true;
	}

	return false;
}

function show_server_stats($serverid,$width="100%") {

	$stats = new Matrix("stats",false,
			array("body.table.width"=>$width),
			"sql:SELECT 
	CONCAT(hlbook_Servers.ip,':',hlbook_Servers.port) as ipaddr,
	hlbook_Servers.hostname,
	hlbook_ServerProps.os,
	hlbook_ServerProps.cpu,
	hlbook_ServerProps.connection,
	hlbook_ServerProps.location

	FROM hlbook_Servers,hlbook_ServerProps
	WHERE hlbook_Servers.serverid='" . $serverid . "'
	&& hlbook_Servers.serverid=hlbook_ServerProps.serverid");

	$stats->header = new SimpleText("header","Current Server");
	$stats->widgets[] = new Label("hostname",false,array("title"=>"Server Name"));
	$stats->widgets[] = new Label("ipaddr",false,array("title"=>"Ip Address"));
	$stats->widgets[] = new Label("cpu",false,array("title"=>"Cpu"));
	$stats->widgets[] = new Label("os",false,array("title"=>"Os"));
	$stats->widgets[] = new Label("connection",false,array("title"=>"Connection"));
	$stats->widgets[] = new Label("location",false,array("title"=>"Location"));
	#$stats->footer = new Label(false,MODE_URL . "&task=book_emailserver&serverid=$serverid",false,false,"Email These Settings");
	$stats->draw();
}

function show_server_trends($serverid,$width="100%",$cutoff=604800)
{
	$stats = new Matrix("stats",false,
			array("body.table.width"=>$width));

	$stats->header = new SimpleText("header","Server Stats");
	$stats->widgets[] = new Label("totalbooks",false,array("default"=>"None","title"=>"Total Books"),false,"sql:SELECT count(*) FROM hlbook_Bookings WHERE serverid='$serverid'");
	$stats->widgets[] = new Label("bestime",false,array("default"=>"None Booked","title"=>"Most Popular Time"),false,get_most_popular_time($serverid,$cutoff));
	$stats->widgets[] = new Label("besuser",false,array("default"=>"None","title"=>"Most Frequent User"),false,get_most_frequent_user($serverid,$cutoff));
	$stats->widgets[] = new Label("mode",false,array("title"=>"Current Mode"),false,get_mode_by_time(time(),$serverid));
	$stats->widgets[] = new BoolText("available",false,array("title"=>"Available Immediately"),false,is_server_available_now($serverid));
	$stats->widgets[] = new Label("timeleft",false,array("title"=>"Time Left on Current Slot"),false,time_left_on_current_slot($serverid));
	$stats->draw();
}

function time_left_on_current_slot($serverid)
{
	global $DB;

	$result = $DB->query("SELECT timeblock FROM hlbook_Servers WHERE serverid='" . $serverid . "'");
	list($timeblock) = $DB->fetch_row($result);
	$DB->free_result($result);

	$midnight = set_date_midnight(0);
	$nextday = $midnight+86400;

	for($i=$midnight;$i<$nextday;$i=$i+$timeblock)
	{
		if($i+$timeblock > time()) {

			$left = time() - ($i + $timeblock);
			return sprintf("%02d Minutes",$left / -60);
		}
	}

	return 1;
}


function get_most_popular_time($serverid,$cutoff = 604800)
{
	global $DB;
	$times = array();
	$highcount = 0;
	$besttime = "None";

	if($cutoff == 0) {
		$result = $DB->query("SELECT initdate FROM hlbook_Bookings");
	}
	else {
		$result = $DB->query("SELECT initdate FROM hlbook_Bookings WHERE initdate > " . (time() -$cutoff));
	}

	while(list($initdate) = $DB->fetch_row($result)){
		$tm = date("h:i A",$initdate);

		if($times[$tm]) {
			$times[$tm]++;

			if($times[$tm] > $highcount) {
				$highcount = $times[$tm];
				$besttime = $tm;
			}
		}
		else
		{
			$times[$tm] = 1;
		}
	}

	unset($times);
	$DB->free_result($result);

	return $besttime . " ($highcount)";
}

function get_total_bookings()
{
	global $DB;

	$result = $DB->query("SELECT count(*) FROM hlbook_Bookings");
	list($count) = $DB->fetch_row($result);
	$DB->free_result($result);

	return $count;
}

function get_most_frequent_user($serverid,$cutoff = 604800)
{
	global $DB;
	$users = array();
	$highcount = 0;
	$bestuser = "None";

	if($cutoff == 0) {
		$result = $DB->query("SELECT userid FROM hlbook_Bookings");
	}
	else {
		$result = $DB->query("SELECT userid FROM hlbook_Bookings WHERE initdate > " . (time() - $cutoff));
	}

	while(list($userid) = $DB->fetch_row($result)){

		if($users[$userid]) {
			$users[$userid]++;

			if($users[$userid] > $highcount) {
				$highcount = 	$users[$userid];
				$bestuser = $userid;
			}
		}
		else
		{
			$users[$userid]=1;
		}
	}
	$DB->free_result($result);

	if($bestuser == "None") { return "None (0)"; }

	$dpname = whatis_displayname($bestuser);	
	return $dpname . " ($highcount)";

}

function get_server($serverid) {

	global $DB;

	if(!is_numeric($serverid)) {
		return 0;
	}

	$result = $DB->query("SELECT hlbook_Servers.*,hlbook_Mods.title FROM hlbook_Servers,hlbook_Mods
		WHERE hlbook_Mods.modid=hlbook_Servers.modid && hlbook_Servers.serverid='" . $serverid . "'");

	if($DB->num_rows($result) == 1) {
		return $result;
	}

	return 0;
}

function get_server_addr($serverid) {

	global $DB;

	$result = $DB->query("SELECT ip,port,rcon FROM hlbook_Servers WHERE serverid='" . $serverid . "'");
	if($DB->num_rows($result) == 1) {
		$ary = $DB->fetch_row($result);
		$DB->free_result($result);
		return $ary;
	}
	$DB->free_result($result);
	return false;
}

function get_booked_times($date,$serverid)
{
	global $DB;

	$next_day = $date+86400;

	$result = $DB->query("
		SELECT 
		hlbook_Bookings.matchid,
		hlbook_Bookings.initdate,
		hlbook_Bookings.servername,
		hlbook_Bookings.status,
		hlbook_Bookings.userid,
		auth_Users.displayname
		FROM 
		hlbook_Bookings,auth_Users 
		WHERE 
		hlbook_Bookings.userid = auth_Users.userid
		&& hlbook_Bookings.initdate >= $date 
		&& hlbook_Bookings.initdate < $next_day 
		&& hlbook_Bookings.serverid='" . $serverid . "'");

	while($rec = $DB->fetch_assoc($result))
	{
		$key = $rec["initdate"];
		$array[$key] = $rec;
	}
	$DB->free_result($result);
	return $array;
}

function get_mode_by_time($date,$serverid)
{
	global $DB;

	$time = date("H:i:s",$date);
	$result = $DB->query("SELECT action FROM hlbook_ServerModes WHERE serverid='" . $serverid . "' && start<='$time' ORDER BY start desc LIMIT 1");

	if($DB->num_rows($result) == 1)
	{	
		list($action) = $DB->fetch_row($result);
		return $action;
	}

	return "bookable";
}

function set_date_midnight($timestamp)
{
	if(!$timestamp) { $timestamp = time(); }

	list($month,$day,$year) = split(",",date("n,j,Y",$timestamp));
	$midnight = mktime(0,0,0,$month,$day,$year);

	return $midnight;
}

function is_valid_bookable_date($date,$timeblock)
{
	if(!is_date_new($date))
		return false;

	$midnight = set_date_midnight($date);
	$maxdate = $midnight + 86400;

	for($i=$midnight;$i<=$maxdate;$i=$i+$timeblock)
	{
		if($i == $date)
			return true;
	}

	return false;
}

function is_date_old($timestamp,$timeblock)
{
	if($timestamp < time() - $timeblock)
		return false;
	else
		return true;
}

function get_prebook()
{
	global $AUTH;

	$prebook = $AUTH->get_ccfgvar("prebook");

	if(!$prebook) {
		$prebook = get_option("hlbook","prebook");
	}

	if($prebook) {
		return $prebook;
	}

	error("Your prebook setting must be greater than 0","HLBookings Config Error");
	return false;
}

function is_date_new($timestamp)
{
	$prebook = get_prebook();

	if($timestamp > time() + ($prebook * 86400))
		return false;
	else
		return true;
}

function is_server_disabled($serverid) {

	global $DB;

	$result = $DB->query("SELECT disabled FROM hlbook_Servers WHERE serverid='" . $serverid . "'");
	list($disabled) = $DB->fetch_row($result);
	$DB->free_result($result);

	if($disabled == 1) {
		return true;
	}

	return false;
}


function is_server_available_now($serverid) {

	global $DB;

	$mode = get_mode_by_time(time(),$serverid);
	if($mode != "bookable") {
		return false;
	}

	if(is_server_disabled($serverid)) {
		return false;
	}

	$result = $DB->query("SELECT hlbook_Bookings.status FROM hlbook_Servers,hlbook_Bookings
		WHERE hlbook_Servers.serverid=hlbook_Bookings.serverid 
		&& hlbook_Bookings.serverid='" . $serverid . "'
		&& hlbook_Bookings.initdate + hlbook_Bookings.timeblock > " . time());

	list($status) = $DB->fetch_row($result);

	if($status == "P" || $status=="R")
		return false;

	return true;
}

function is_date_available($timestamp,$serverid)
{
	global $DB;

	$result = $DB->query("SELECT count(*) FROM hlbook_Bookings WHERE initdate='" . $timestamp . "' && serverid='" . $serverid . "' LIMIT 1");
	list($count) = $DB->fetch_row($result);

	$DB->free_result($result);

	if($count == 1)
		return false;
	else
		return true;
}

function is_maxbooks_exceeded()
{
	global $DB,$AUTH;

	$maxbooks = $AUTH->get_ccfgvar("maxbooks");

	if(!$maxbooks)
		return false;

	$result = $DB->query("SELECT count(*) FROM hlbook_Bookings WHERE userid='" . userid() . "'");
	list($count) = $DB->fetch_row($result);
	$DB->free_result($result);

	if($count >= $maxbooks)
		return true;

	return false;
}

function is_maxscheduled_exceeded()
{
	global $DB,$AUTH;

	$max = $AUTH->get_ccfgvar("maxscheduled");

	if(!$max)
		return false;

	$result = $DB->query("SELECT count(id) FROM hlbook_Bookings WHERE status!='C'");
	list($count) = $DB->fetch_row($result);
	$DB->free_result($result);

	if($count >= $max)
		return true;

	return false;
}

function is_svpassword_ok($password)
{
	if(!$password || $password == "none")
		return false;

	return true;
}

function update_svpassword($matchid,$password)
{
	global $DB;

	$result = $DB->query("UPDATE hlbook_Bookings SET svpasswd='" . $password . "' WHERE matchid='" . $matchid . "'");
	if($DB->dberror()) {
		return 0;
	}

	return 1;
}

function is_user_expired()
{
	global $AUTH;

	$expdate = $AUTH->get_ccfgvar("expdate");

	if(!$expdate)
		return false;

	if($expdate < time())
		return true;

	return false;
}

function is_acclevel_good()
{
	global $AUTH;

	$req_acclevel = get_defined_access("hlbook","hlbook");

	if(check_access($AUTH->get_cfgvar("acclevel"),$req_acclevel))
		return true;

	return false;
}

function email_all_users($subject,$message) {

	global $AUTH,$DB;
	$count=0;

	$result = $DB->query("SELECT email FROM auth_Users WHERE massmail=1 && disabled=0");
	$sendmail = new Sendmail($AUTH->get_cfgvar("email"),$subject);
	$sendmail->body = $message;

	while(list($email) = $DB->fetch_row($result)) {
		$sendmail->append_to($email);
		$count++;
	}

	$sendmail->send();

	foreach($sendmail->errors as $error) { print $error . "<br>"; }
	return $count;
}

function secure_email_address_book(&$match)
{
	global $DB,$AUTH;

	if(check_access(get_defined_access("hlbook","hlbook"))
			|| check_access(get_defined_access("hladmin","hlbook"))) {

		$result = $DB->query("SELECT email FROM hlbook_AddrBook WHERE userid='" . userid() . "'");

		if($DB->num_rows($result) == 0)
			return false;

		$sendmail = new Sendmail($AUTH->get_cfgvar("email"),get_option("hlbook","email_subject"),get_option("hlbook","email_matchtmpl"));
		$sendmail->tags[] = new Tags("MATCHID",$match["matchid"]);
		$sendmail->tags[] = new Tags("DATE",date("l, M d Y h:i A",$match["initdate"]));
		$sendmail->tags[] = new Tags("IP",$match["ip"]);
		$sendmail->tags[] = new Tags("PORT",$match["port"]);
		$sendmail->tags[] = new Tags("LENGTH", sprintf("%0.2f hours",$match["timeblock"]/3600));
		$sendmail->tags[] = new Tags("SVPASS",$match["svpasswd"]);
		$sendmail->tags[] = new Tags("SERVERNAME",$match["servername"]);
		$sendmail->tags[] = new Tags("COMMENTS",$match["comments"]);
		$sendmail->tags[] = new Tags("DISPLAYNAME",$match["displayname"]);
		$sendmail->tags[] = new Tags("MOD",$match["modid"]);
		$sendmail->tags[] = new Tags("SITENAME",get_option("base","page_title"));
		$sendmail->tags[] = new Tags("SITECONTACT",get_option("base","page_contact_email"));
		$sendmail->tags[] = new Tags("USEREMAIL",$AUTH->get_cfgvar("email"));

		while(list($email) = $DB->fetch_row($result)) {
			$sendmail->append_to($email);
		}

		$sendmail->send();
	}
}

function show_status_key($class="SKey")
{
	$key = new HList("key",array("class"=>"$class"),
			array("body.table.width"=>"50%","body.table.align"=>"center","body.td.align"=>"center"));

	$key->widgets[] = new VButton (
			"main_btn0",
			false,
			array("src"=>IMAGE_URL . "plugins/hlbook/C.png"),
			false,
			false,
			"Closed"
		);

	$key->widgets[] = new VButton (
			"main_btn0",
			false,
			array("src"=>IMAGE_URL . "plugins/hlbook/P.png"),
			false,
			false,
			"In Progress"
		);

	$key->widgets[] = new VButton (
			"main_btn0",
			false,
			array("src"=>IMAGE_URL . "plugins/hlbook/R.png"),
			false,
			false,
			"Restarting"
		);

	$key->widgets[] = new VButton (
			"main_btn0",
			false,
			array("src"=>IMAGE_URL . "plugins/hlbook/S.png"),
			false,
			false,
			"Scheduled"
		);

	$key->widgets[] = new VButton (
			"main_btn0",
			false,
			array("src"=>IMAGE_URL . "plugins/hlbook/E.png"),
			false,
			false,
			"Error"
		);

	$key->draw();
	unset($key);
}

function show_booking_status_key($class="SKey")
{
	$key = new HList("key",array("class"=>"$class","draw_cols"=>2),
			array("body.table.width"=>"50%","body.table.align"=>"center","body.td.align"=>"center"));

	$key->widgets[] = new VButton (
			"main_btn0",
			false,
			array("src"=>IMAGE_URL . "plugins/hlbook/S.png"),
			false,
			false,
			"Available Now"
		);

	$key->widgets[] = new VButton (
			"main_btn0",
			false,
			array("src"=>IMAGE_URL . "plugins/hlbook/P.png"),
			false,
			false,
			"Unavailable Now"
		);

	$key->draw();
	unset($key);	
}

function log_file_exists($matchid)
{
	$path = get_option("hlbook","logpath") . "/match_" . $matchid . ".zip";
	if(file_exists($path)) {

		$size = filesize($path);
		return $size;
	}

	return false;
}

function player_exists($matchid,$serverid,$name)
{
	global $DB;

	$result = $DB->query("SELECT wonid FROM hlbook_Players WHERE name='" . $name . "' LIMIT 1");
	list($wonid) = $DB->fetch_row($result);
	$DB->free_result($result);

	if($wonid > 0) {
		return $wonid;
	}

	return 0;
}

function set_book_opt($matchid,$name,$value)
{
	global $DB;

	$result = $DB->query("UPDATE hlbook_Bookings SET $name='" . $value . "' WHERE matchid='"  . $matchid . "'");
	$affected = $DB->affected_rows();

	return $affected;
}

function get_config_by_id(&$cvar,$cfgid)
{
	global $DB;

	$result = $DB->query("SELECT * FROM hlbook_Configs WHERE configid='" . $cfgid . "' LIMIT 1");

	$count = $DB->num_rows($result);
	$cvar = $DB->fetch_assoc($result);
	$DB->free_result($result);

	if($count>0) {
		return 1;
	}

	return 0;
}

function config_exists($cfgid)
{
	global $DB;

	$result = $DB->query("SELECT count(*) FROM hlbook_Configs WHERE configid='" . $cfgid . "' LIMIT 1");
	if($DB->num_rows($result))
		return true;

	return false;
}

function map_exists($map)
{
	global $DB;

	$result = $DB->query("SELECT count(*) FROM hlbook_Maps WHERE name='" . $map . "' LIMIT 1");
	if($DB->num_rows($result))
		return true;

	return false;
}

function update_shotcount($matchid,$val)
{
	global $DB;
	$result = $DB->query("UPDATE hlbook_Bookings SET shots='" . $val . "' WHERE matchid='" . $matchid . "'");

	return true;
}
