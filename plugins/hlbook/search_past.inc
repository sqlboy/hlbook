<?
$search = new SimpleSearch("ubsrch","hlbook_Bookings",array("orderby"=>"initdateD"));

$search->header = new SimpleText("header","Search for Past Bookings");
$search->search[] = new Select("modid",false,array("title"=>"Mod/Game:","nolabel"=>"Any","sticky"=>true,"required"=>false,"search_type"=>"exact"),false,false,"sql:SELECT modid,title FROM hlbook_Mods");
$search->search[] = new Select("userid",false,array("title"=>"User:","nolabel"=>"Any","sticky"=>true,"required"=>false,"search_type"=>"exact"),false,false,"sql:SELECT userid,displayname FROM auth_Users");
$search->search[] = new Select("serverid",false,array("title"=>"Server:","nolabel"=>"Any","sticky"=>true,"required"=>false,"search_type"=>"exact"),false,false,"sql:SELECT serverid,hostname FROM hlbook_Servers");
$search->search[] = new InputDate("initdate",array("title"=>"After Date:","sticky"=>true,"search_type"=>"gteq"),false,time() - 86400);
$search->footer = new Submit("Submit",false,array("foot.td.align"=>"center"),"Search");

$search->results[] = new Label("view",MODE_URL . "&task=bookselect&bookid=%[matchid]",
		array("title"=>""),array("body.td.align"=>"Center"),"View");

$search->results[] = new Image("status",false,
		array("src"=>IMAGE_URL . "plugins/hlbook/%[status].png"),
		array("title"=>"Status","sort"=>true),
		array("body.td.align"=>"center","labels.td.align"=>"center","body.td.width"=>"5%")
	);
$search->results[] = new Label("shots",false,
		array("title"=>"Shots","sort"=>true),array("labels.td.align"=>"center","body.td.align"=>"center"));
$search->results[] = new Label("servername",false,
		array("title"=>"ServerName","sort"=>true,"tooltip"=>"%[modid]"));
$search->results[] = new Date("initdate",false,array("title"=>"Start Date","sort"=>true));
$search->results[] = new Label("userid",false,array("title"=>"Booked By","sort"=>true),
		false,"sql:SELECT displayname FROM auth_Users WHERE userid='%[userid]'");
$search->results[] = new Label("serverid",false,array("title"=>"Server","sort"=>true),
		false,"sql:SELECT CONCAT(hostname,' (',ip,':',port,')') AS value FROM hlbook_Servers WHERE serverid='%[serverid]'");


$search->draw();
show_status_key();
