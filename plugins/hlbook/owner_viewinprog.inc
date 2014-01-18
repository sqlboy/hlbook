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

$details = new Matrix("details",
		array("draw_head"=>true,"draw_labels"=>false,"rtype"=>"row"),
		array("body.table.width"=>"100%"),
		$b);


$details->header = new SimpleText("header","Booking Details");
$details->widgets[] = new Label("servername",false,array("title"=>"Hostname"));
$details->widgets[] = new Label("userid",false,array("title"=>"Booked By"),false,"sql:SELECT displayname FROM auth_Users WHERE userid='%[userid]'");
$details->widgets[] = new Date("posted",false,array("title"=>"Booked On","format"=>"l, M jS Y g:i A"));
$details->widgets[] = new Date("initdate",false,array("title"=>"Start Time","format"=>"l, M jS Y g:i A"));
$details->widgets[] = new Date("initdate",false,array("title"=>"End time","format"=>"l, M jS Y g:i A"),false,$b["timeblock"] + $b["initdate"]);
$details->widgets[] = new Label("initdate",false,array("title"=>"Time Left"),false,sprintf("%0.2f Minutes",(($b["initdate"]+$b["timeblock"]) - time()) / 60));
$details->widgets[] = new Label("ipaddr",false,array("title"=>"Server IP Address"),false,"sql:SELECT concat(ip,':',port) as ipaddr FROM hlbook_Servers where serverid='%[serverid]'");
$details->widgets[] = new Label("svpasswd",false,array("title"=>"Password"));

$details->draw();

print br(2);

$players = new SimpleTable("details",
		false,
		array("body.table.width"=>"100%"),
		"sql:SELECT * FROM hlbook_Players WHERE matchid='" . $r["matchid"] . "' && serverid='" . $r["serverid"] . "'");

$players->header = new SimpleText("header","Player Connect History");
$players->widgets[] = new Label("wonid",false,array("title"=>"Wonid"),array("body.td.width"=>"10%"));
$players->widgets[] = new Label("name",false,array("title"=>"Name"));
$players->widgets[] = new Label("frags",false,array("title"=>"Frags","default"=>'0'),array("body.td.align"=>"center","labels.td.align"=>"center"));
$players->widgets[] = new Label("deaths",false,array("title"=>"Deaths","default"=>'0'),array("body.td.align"=>"center","labels.td.align"=>"center"));

$players->draw();

?>
