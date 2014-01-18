<?
if(!config_exists($_GET["cfgid"])) {
	print message("caution","That config does not exist");
	return 0;
}

$matrix = new Matrix("admincfgedit",array("left_width"=>"20%"),false,"sql:SELECT * FROM hlbook_Configs WHERE configid='" . $_GET["cfgid"] . "'");
$matrix->header = new SimpleText("header","Edit Server Config");

$matrix->widgets[] = new TextBox("title",array("maxlength"=>"32"),array("title"=>"Config Title","sticky"=>true,"required"=>true));
$matrix->widgets[] = new TextBox("filename",array("maxlength"=>"32"),array("title"=>"Config File","sticky"=>true,"required"=>true));
$matrix->widgets[] = new Select("modid",false,array("title"=>"Mod","sticky"=>true,"required"=>true),false,false,"sql:SELECT modid,title FROM hlbook_Mods");
$matrix->widgets[] = new TextBox("website",array("maxlength"=>"64"),array("title"=>"Homepage","sticky"=>true,"required"=>true,"regexpreset"=>"url"));
$matrix->widgets[] = new Textarea("config",array("cols"=>65,"rows"=>25,"wrap"=>"off"),array("linebreaks"=>0,"title"=>"Configuration","sticky"=>false,"required"=>false),array("body.td.valign"=>"top"));
$matrix->widgets[] = new Hidden("lastupdate",false,false,time());

$matrix->footer = new Submit("Submit",false,array("foot.td.align"=>"center"),"Apply");

if($_POST["Submit"])
{
	$input = new RowInput(&$matrix->widgets,"hlbook_Configs","configid",$_GET["configid"]);

	if($serverid = $input->update()) {
		$matrix->rows[] = new Message("ok","ok","Config " . $_POST["title"] . " saved");
	}
	else
		$matrix->rows[] = new Message("caution","caution",$input->error());
}

print form("addconfig");
$matrix->draw();
print "</form>";
?>
