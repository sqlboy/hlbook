<?

$servername = get_servername($_GET["serverid"]);
if(!$servername) {
	print message("caution","That server does not exist");
	return 0;
}

$matrix = new Matrix("adminservereditopts",false,false,"sql:SELECT * FROM hlbook_ServerProps WHERE serverid='" . $_GET["serverid"] . "'");
$matrix->header = new SimpleText("header","Edit Server Props for $servername");

$matrix->widgets[] = new Select("os",false,array("title"=>"Operating System","sticky"=>true,"required"=>true),false,false,array("Linux"=>"Linux","Windows"=>"Windows","FreeBSD"=>"FreeBSD"));
$matrix->widgets[] = new TextBox("cpu",array("maxlength"=>32),array("title"=>"CPU","sticky"=>true,"required"=>false));
$matrix->widgets[] = new TextBox("connection",array("maxlength"=>"32"),array("title"=>"Connection","sticky"=>true));
$matrix->widgets[] = new TextBox("location",array("maxlength"=>"32"),array("title"=>"Location","sticky"=>true));
$matrix->footer = new Submit("Submit",false,array("foot.td.align"=>"center"),"Apply");

if($_POST["Submit"])
{
	$input = new RowInput(&$matrix->widgets,"hlbook_ServerProps","serverid",$_GET["serverid"]);

	if($input->update())
		$matrix->rows[] = new Message("ok","ok","Server config updated");
	else
		$matrix->rows[] = new Message("caution","caution",$input->error());
}

print form("addserver");
$matrix->draw();
print "</form>";
?>
