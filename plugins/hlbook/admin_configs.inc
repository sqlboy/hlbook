<?
$search = new SimpleSearch("acsrch","hlbook_Configs");

$search->header = new SimpleText("header","Search for Past Bookings");
$search->search[] = new TextBox("title",false,array("title"=>"Title","search_type"=>"regex"));
$search->search[] = new TextBox("filename",false,array("title"=>"Filename","search_type"=>"regex"));
$search->search[] = new TextBox("website",false,array("title"=>"Website","search_type"=>"regex"));
$search->search[] = new Select("modid",false,array("title"=>"Mod/Game:","nolabel"=>"Any","sticky"=>true,"required"=>false,"search_type"=>"exact"),false,false,"sql:SELECT modid,title FROM hlbook_Mods");
$search->footer = new Submit("Submit",false,array("foot.td.align"=>"center"),"Search");

$search->results[] = new Label("del",MODE_URL . "&task=admin_configdel&cfgid=%[configid]",false,array("body.td.width"=>"1"),"Delete");
$search->results[] = new Label("edit",MODE_URL . "&task=admin_configedit&cfgid=%[configid]",false,array("body.td.width"=>"1"),"Edit");
$search->results[] = new Label("title",false,
		array("title"=>"Title","sort"=>true));
$search->results[] = new Label("modid",false,
		array("title"=>"Mod","sort"=>true),false,"sql:SELECT title FROM hlbook_Mods WHERE modid='%[modid]'");
$search->results[] = new Label("filename",false,
		array("title"=>"Filename","sort"=>true));

$search->draw();
show_status_key();
