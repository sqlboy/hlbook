<?

global $DB;

if(!is_valid_midnight($_GET["date"]))
{
	print "<br><p align=\"center\">";
	error("Please, do not tamper with the date.","Date Error",false);
	print "</p>";
	return 0;
}

$result = $DB->query("SELECT 
hlbook_ServerProps.*,hlbook_Servers.*,hlbook_Mods.title
FROM hlbook_ServerProps,hlbook_Servers,hlbook_Mods
WHERE hlbook_Mods.modid=hlbook_Servers.modid && hlbook_ServerProps.serverid=hlbook_Servers.serverid && hlbook_Servers.serverid='" . $_GET["serverid"] . "'");

$r = $DB->fetch_assoc($result);
$date = $_GET["date"];
$next_day = $date + 86400;
$ds = array();
$array_taken = get_booked_times($date,$r["serverid"]);
$show_all = 1;

for($i=$_GET["date"];$i<$next_day;$i=$i+$r["timeblock"])
{
	$hostname = "";
	$link = "";
	$user = "";

	#find the hostname if we got one
	if($array_taken[$i]['servername'])
		$hostname = $array_taken[$i]['servername'];

	$mode = get_mode_by_time($i,$r["serverid"]);

	if($mode == "bookable")
	{
		if($array_taken[$i]['status'] == "C") {
			$status = " <b>(Closed)</b>";
			$user = $array_taken[$i]['displayname'];
			$link = MODE_URL . "&task=bookselect&bookid=" . $array_taken[$i]['matchid'];
		}
		elseif(time() > ($i + $r["timeblock"]))
		{
			$status = " <b>(Closed)</b>";
		}
		elseif($array_taken[$i]['status'] == "P")
		{
			$status = " <b>(In Progress)</b>";
			$link = MODE_URL . "&task=bookselect&bookid=" . $array_taken[$i]['matchid'];
			$user = $array_taken[$i]['displayname'];
		}	
		elseif($array_taken[$i]['status'] == "S")
		{
			$status = " <b>(Scheduled for play)</b>";
			$user = $array_taken[$i]['displayname'];
			$link = MODE_URL . "&task=bookselect&bookid=" . $array_taken[$i]['matchid'];
		}
		elseif($array_taken[$i]['status'] == "R")
		{
			$status = " <font color=\"red\"><b>(Restarting)</b></font>";
			$user = $array_taken[$i]['displayname'];	
		}
		elseif(!$array_taken[$i])
		{
			if($r['disabled'] == 1) {
				$status = "Server is disabled.  Please contact Admin";
			}
			else {
				$status = "Slot Available";
				$link = MODE_URL . "&task=book_reserve&serverid=" . $r["serverid"] . "&date=$i";
			}	
		}

	}
	elseif($mode=="public")
	{
		$status = "Open to Public";
	}
	else
	{
		$status = $mode;
	}

	if($show_all == 1)
	{
		$ds[] = array("initdate"=>$i,"hostname"=>$hostname . $status,"link"=>$link,"user"=>$user);
	}
	else
	{
		if($status == "Slot Available" || $array_taken[$i]['userid'] == userid())
		{
			$ds[] = array("initdate"=>$i,"hostname"=>$hostname . $status,"link"=>$link,"user"=>$user);
		}	
	}
}

$times = new SimpleTable("times",array("class"=>"Table"),array("body.table.width"=>"100%"),$ds);
$times->header = new SimpleText("header","Available Booking Times for: " . date("l, M jS Y",$date));
$times->widgets[] = new Date("initdate",false,array("title"=>"Start Time","format"=>"h:iA"),array("body.td.width"=>"8%"));
$times->widgets[] = new Label("hostname","%[link]",array("title"=>"Host"));
$times->widgets[] = new Label("user",false,array("title"=>"Booked By"));

#####################
?>
<table width="100%" cellspacing="0" cellpadding="0" border="0">
<tr><td width="50%" valign="top" align="left"><?show_server_stats($r["serverid"],"99%");?></td>
<td width="50%" valign="top" align="right"><?show_server_trends($r["serverid"],"99%",0);?></td></tr></table>
<br>
<?
$times->draw();
?>
