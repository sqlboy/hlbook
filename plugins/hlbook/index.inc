<?

$bigjoin = "SELECT
hlbook_Bookings.matchid,hlbook_Bookings.status,hlbook_Bookings.initdate, hlbook_Bookings.servername,hlbook_Bookings.modid,hlbook_Bookings.serverid,
CONCAT(hlbook_Servers.hostname,' (',hlbook_Servers.ip,':',hlbook_Servers.port,')') as ipaddr, (hlbook_Servers.timeblock/3600) as tblock,auth_Users.displayname,
hlbook_Mods.title as modtitle,hlbook_Mods.url as modurl
FROM hlbook_Bookings,hlbook_Servers,hlbook_Mods,auth_Users
WHERE
	hlbook_Bookings.status != 'C'
	&& hlbook_Bookings.status != 'E'
	&& hlbook_Bookings.modid=hlbook_Mods.modid
	&& hlbook_Bookings.serverid=hlbook_Servers.serverid
	&& hlbook_Bookings.userid=auth_Users.userid";

$table = new AdvancedTable("match_table",
		array("keycol"=>"matchid","orderby"=>"initdateA","sort_track"=>true,"baseurl"=>TASK_URL),
		array("body.table.width"=>"100%"),
		"sql:$bigjoin");

$table->header = new SimpleText("header","Scheduled Matches");

$table->widgets[] = new Label("view",MODE_URL . "&task=bookselect&bookid=%[matchid]",false,array("body.td.width"=>"1"),"View");
$table->widgets[] = new Image("status",false,
		array("src"=>IMAGE_URL . "plugins/hlbook/%[status].png"),
		array("title"=>"Status","sort"=>true),
		array("body.td.align"=>"center","labels.td.align"=>"center","body.td.width"=>"5%")
	);
$table->widgets[] = new Label("servername",
		false,
		array("sort"=>true,"title"=>"Hostname"));
$table->widgets[] = new Label("modtitle",MODE_URL . "&task=book_servers&filter=%[modid]",array("sort"=>true,"tooltip"=>"View all %[modtitle] servers","title"=>"Mod/Game"));
$table->widgets[] = new Label("displayname",false,array("sort"=>true,"title"=>"Booked By"));
$table->widgets[] = new Date("initdate",false,array("title"=>"StartTime","sort"=>true,"format"=>"l, M d Y h:i A"));
$table->widgets[] = new Label("tblock",false,array("title"=>"TimeBlock","sort"=>true,"out_sprintf"=>"%0.1f Hours(s)"));
$table->widgets[] = new Label("ipaddr",MODE_URL . "&task=book_date&serverid=%[serverid]",array("sort"=>true,"title"=>"Server"));

$table->draw();
print br(2);
show_status_key();
