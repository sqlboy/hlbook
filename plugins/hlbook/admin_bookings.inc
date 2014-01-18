<?

$search = new SimpleSearch("bookingsearch","hlbook_Bookings");

$search->search[] = new Select("modid",false,array("title"=>"Mod/Game","nolabel"=>"Any","sticky"=>true,"required"=>false,"search_type"=>"exact"),false,false,"sql:SELECT modid,title FROM hlbook_Mods");
$search->search[] = new Select("userid",false,array("title"=>"User","nolabel"=>"Any","sticky"=>true,"required"=>false,"search_type"=>"exact"),false,false,"sql:SELECT userid,displayname FROM auth_Users");
$search->search[] = new Select("serverid",false,array("title"=>"Server","nolabel"=>"Any","sticky"=>true,"required"=>false,"search_type"=>"exact"),false,false,"sql:SELECT serverid,hostname FROM hlbook_Servers");
$search->search[] = new Select("status",false,array("title"=>"Status","nolabel"=>"Any","sticky"=>true,"required"=>false,"search_type"=>"exact"),false,false,array("P"=>"In Progress","R"=>"Restarting","E"=>"Errored","S"=>"Scheduled","C"=>"Closed"));

$search->search[] = new InputDate("initdate",array("title"=>"Booked After Date:","sticky"=>true,"search_type"=>"gteq"),false,time() - 86400);
$search->footer = new Submit("Submit",false,array("foot.td.align"=>"center"),"Search");

$search->results[] = new Label("del",MODE_URL . "&task=admin_bookdel&bookid=%[matchid]",false,array("body.td.width"=>"1"),"Delete");
$search->results[] = new Label("edit",MODE_URL . "&task=admin_bookedit&bookid=%[matchid]",false,array("body.td.width"=>"1"),"Edit");

$search->results[] = new Image("status",false,
		array("src"=>IMAGE_URL . "plugins/hlbook/%[status].png"),
		array("title"=>"Status","sort"=>true),
		array("body.td.align"=>"center","labels.td.align"=>"center","body.td.width"=>"5%")
	);

$search->results[] = new Label("servername",MODE_URL . "&task=bookselect&bookid=%[matchid]",
		array("title"=>"ServerName","sort"=>true));
$search->results[] = new Label("modid",false,array("title"=>"Mod","sort"=>true),false,"sql:SELECT title FROM hlbook_Mods WHERE modid='%[modid]'");
$search->results[] = new Date("initdate",false,array("title"=>"Start Date","sort"=>true));
$search->results[] = new Label("userid",false,array("title"=>"Booked By","sort"=>true),
		false,"sql:SELECT displayname FROM auth_Users WHERE userid='%[userid]'");
$search->results[] = new Label("serverid",false,array("title"=>"Server","sort"=>true),
		false,"sql:SELECT CONCAT(hostname,' (',ip,':',port,')') AS value FROM hlbook_Servers WHERE serverid='%[serverid]'");

$search->draw();
print "<br>";
show_status_key();
?>
