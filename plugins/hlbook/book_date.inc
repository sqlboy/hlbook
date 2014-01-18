<?
/* Here we show the default server pic or the custom pic and the detailed server stuff */
global $DB;
global $AUTH;

$result = get_server($_GET["serverid"]);
if(!$result) {
	print message("caution","That server does not exist.");
	return 0;
}

$r = $DB->fetch_assoc($result);

#date stuff
list($month,$day,$year) = split(",",date("m,d,Y",time()));

$dates = new Browser("dates",array("draw_cols"=>2,"class"=>"Table"));
$dates->header = new SimpleText("header","Available Booking Dates");

$prebook = get_prebook();
for($i=0;$i<$prebook;$i++)
{
	$date = strtotime($month . "/" . $day . "/" . $year . " 00:00:00");
	$day++;

	$dates->widgets[] = new Date("date",SCRIPT_URL . "?mode=hlbook&task=book_time&serverid=" . $r["serverid"] . "&date=$date",array("format"=>"l, M jS Y"),false,$date);
}

?>
<table width="100%" cellspacing="0" cellpadding="0" border="0">
<tr><td width="50%" valign="top" align="left"><?show_server_stats($r["serverid"],"99%");?></td>
<td width="50%" valign="top" align="right"><?show_server_trends($r["serverid"],"99%",0);?></td></tr></table>
<br>
<?
$dates->draw();
?>
