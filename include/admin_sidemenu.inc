<?
/* the side menu on the options screen */

if(check_access(get_defined_access("hladmin","hlbook")))
{	
	$admin = new VList("admin_menu",array("class"=>"List"),array("body.td.align"=>"left","body.table.width"=>"175"));
	$admin->header = new SimpleText("header","Admin Menu");

	$admin->widgets[]= new HButton("users",
			MODE_URL . "&task=admin_stats",
			array("src"=>ATHEME_URL . "square.png"),
			array("tooltip"=>"HLBook Stats"),
			false,"Stats");

	$admin->widgets[]= new HButton("users",
			MODE_URL . "&task=admin_usersearch",
			array("src"=>ATHEME_URL . "square.png"),
			array("tooltip"=>"Administrate HLBook users"),
			false,"Users");

	$admin->widgets[]= new HButton("servers",
			MODE_URL . "&task=admin_servers",
			array("src"=>ATHEME_URL . "square.png"),
			array("tooltip"=>"Create,Edit,Delete a bookable server"),
			false,"Servers");

	$admin->widgets[]= new HButton("bookings",
			MODE_URL . "&task=admin_bookings",
			array("src"=>ATHEME_URL . "square.png"),
			array("tooltip"=>"Make changes to existing bookings"),
			false,"Bookings");	

	$admin->widgets[]= new HButton("options",
			MODE_URL . "&task=admin_options",
			array("src"=>ATHEME_URL . "square.png"),
			array("tooltip"=>"HLBook setup and options"),
			false,"Setup");	

	$admin->widgets[]= new HButton("maps",
			MODE_URL . "&task=admin_maps",
			array("src"=>ATHEME_URL . "square.png"),
			array("tooltip"=>"Available Map List"),false,"Maps");	

	$admin->widgets[]= new HButton("configs",
			MODE_URL . "&task=admin_configs",
			array("src"=>ATHEME_URL . "square.png"),
			array("tooltip"=>"Available Config List"),false,"Configs");	


	$admin->draw();
	print "<br>";
}
if(check_accesS(get_defined_access("hlbook","hlbook")))
{
	$admin = new VList("admin_menu",array("class"=>"List"),array("body.td.align"=>"left","body.table.width"=>"175"));
	$admin->header = new SimpleText("header","User Menu");

	$admin->widgets[]= new HButton("profile",MODE_URL . "&task=owner_profile",array("src"=>ATHEME_URL . "square.png"),false,false,"My Profile");
	$admin->widgets[]= new HButton("addr",MODE_URL . "&task=owner_addressbook",array("src"=>ATHEME_URL . "square.png"),false,false,"Address Book");
	$admin->widgets[]= new HButton("addr",MODE_URL . "&task=owner_mybookings",array("src"=>ATHEME_URL . "square.png"),false,false,"My Bookings");

	$admin->draw();

}
?>
