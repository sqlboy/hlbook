<?

$results = new AdvancedTable("mybook",
		array("orderby"=>"initdateD","numpp"=>"25","multipage"=>true,"keycol"=>"matchid","sort_track"=>true,"baseurl"=>SCRIPT_URL . "?mode=hlbook&task=owner_mybookings"),
		array("body.table.width"=>"100%"),
		"sql:SELECT * FROM hlbook_Bookings WHERE userid='" . userid() . "'");

$results->header = new SimpleText("header","My Bookings");

$results->widgets[] = new Label("del",MODE_URL . "&task=owner_bookdel&bookid=%[matchid]",false,array("body.td.width"=>"1"),"Delete");
$results->widgets[] = new Label("edit",MODE_URL . "&task=owner_editbook&bookid=%[matchid]",false,array("body.td.width"=>"1"),"Edit");

$results->widgets[] = new Image("status",false,
		array("src"=>IMAGE_URL . "plugins/hlbook/%[status].png"),
		array("title"=>"Status","sort"=>true),
		array("body.td.align"=>"center","labels.td.align"=>"center")
	);
$results->widgets[] = new Label("servername",MODE_URL . "&task=bookselect&bookid=%[matchid]",
		array("sort"=>"true","title"=>"ServerName","tooltip"=>"%[modid]"));
$results->widgets[] = new Date("initdate",false,array("sort"=>true,"title"=>"Start Date"));
$results->widgets[] = new Label("serverid",false,array("sort"=>true,"title"=>"Server"),
		false,"sql:SELECT CONCAT(hostname,' (',ip,':',port,')') AS value FROM hlbook_Servers WHERE serverid='%[serverid]'");


$results->draw();
print "<br>";
show_status_key();
