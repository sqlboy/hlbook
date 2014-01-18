<?
$baseurl = SCRIPT_URL . "?mode=hlbook&task=admin_servers";
$query = "SELECT serverid,(timeblock/3600) as tblock,hostname,CONCAT(ip,':',port) as ipaddr,modid,disabled FROM hlbook_Servers$where";

$table = new AdvancedTable(
		"hlbook_servers",
		array("keycol"=>"serverid","orderby"=>"modidA","sort_track"=>true,"baseurl"=>$baseurl),
		array("body.table.width"=>"100%"),
		"sql:$query"
	);

$table->header = new SimpleText("title","Bookable Servers");
$table->widgets[] = new Label("del",MODE_URL . "&task=admin_serverdelconf&serverid=%[serverid]",false,array("body.td.width"=>"1"),"Delete");
$table->widgets[] = new Label("edit",MODE_URL . "&task=admin_serveredit&serverid=%[serverid]",false,array("body.td.width"=>"1"),"Edit");
$table->widgets[] = new BoolImage("disabled",false,false,array("sort"=>true,"reverse"=>true,"title"=>"Enabled?"),
		array("labels.td.width"=>"5%","body.td.align"=>"center","labels.td.align"=>"center"));
$table->widgets[] = new Label("hostname",false,array("sort"=>true,"title"=>"Name"));
$table->widgets[] = new Label("modid",false,array("title"=>"Mod","sort"=>true));
$table->widgets[] = new Label("ipaddr",false,array("sort"=>true,"title"=>"IP"));
$table->widgets[] = new Label("tblock",false,array("sort"=>true,"title"=>"TimeSlot","out_sprintf"=>"%0.1f Hours(s)"));

#########################

$table->draw();
