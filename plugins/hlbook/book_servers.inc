<?

$baseurl = SCRIPT_URL . "?mode=hlbook&task=book_servers";
$title = "All";

if($_GET["filter"] && $title = get_mod($_GET["filter"]))
{
	$where .= " WHERE modid='" . $_GET["filter"] . "'";
	$baseurl = SCRIPT_URL . "?mode=hlbook&task=book_servers&filter=" . $_GET["filter"];
}

$query = "SELECT serverid,(timeblock/3600) as tblock,hostname,CONCAT(ip,':',port) as ipaddr,modid FROM hlbook_Servers$where";

$table = new AdvancedTable(
		"hlbook_servers",
		array("keycol"=>"serverid","orderby"=>"modidA","sort_track"=>true,"baseurl"=>$baseurl),
		array("body.table.width"=>"100%"),
		"sql:$query"
	);

$table->header = new SimpleText("title",$title . " Servers");
$table->widgets[] = new BoolImage("available",false,false,
		array("title"=>"Ready","image_url"=>IMAGE_URL . "plugins/hlbook/","false_image"=>"P.png","true_image"=>"S.png"),
		array("labels.td.width"=>"5%","body.td.align"=>"center","labels.td.align"=>"center"),"callback:is_server_available_now('%[serverid]')");

$table->widgets[] = new Label("hostname",
		SCRIPT_URL . "?mode=hlbook&task=book_date&serverid=%[serverid]",
		array("sort"=>true,"title"=>"Name"));
$table->widgets[] = new Label("modid",false,array("title"=>"Mod","sort"=>true),false,"sql:SELECT title FROM hlbook_Mods WHERE modid='%[modid]'");
$table->widgets[] = new Label("ipaddr",false,array("sort"=>true,"title"=>"IP"));
$table->widgets[] = new Label("tblock",false,array("sort"=>true,"title"=>"TimeSlot","out_sprintf"=>"%0.1f Hours(s)"));

#########################

$table->draw();
print "<br>";
show_booking_status_key();
