<?

$matrix = new Matrix("admincfgadd",array("left_width"=>"20%"));
$matrix->header = new SimpleText("header","Add Server Config");

$matrix->rows[] = new SimpleText(false,"Adding configs to the database will allow users to specify the server configuration file.  To use the config mirroring utility, cut and past the config into the box below.  Be sure to remove any settings that could stop HLBook from working properly.");
$matrix->widgets[] = new TextBox("title",array("maxlength"=>"32"),array("title"=>"Config Title","sticky"=>true,"required"=>true));
$matrix->widgets[] = new TextBox("filename",array("maxlength"=>"32"),array("title"=>"Config File","sticky"=>true,"required"=>true));
$matrix->widgets[] = new Select("modid",false,array("title"=>"Mod","sticky"=>true,"required"=>true),false,false,"sql:SELECT modid,title FROM hlbook_Mods");
$matrix->widgets[] = new TextBox("website",array("maxlength"=>"64"),array("title"=>"Homepage","sticky"=>true,"required"=>true,"regexpreset"=>"url"));
$matrix->widgets[] = new Textarea("config",array("cols"=>65,"rows"=>25,"wrap"=>"off"),array("linebreaks"=>0,"title"=>"Configuration","sticky"=>false,"required"=>false),array("body.td.valign"=>"top"));
$matrix->widgets[] = new Hidden("lastupdate",false,false,time());

$matrix->footer = new Submit("Submit",false,array("foot.td.align"=>"center"),"Apply");

if($_POST["Submit"])
{
	$input = new RowInput(&$matrix->widgets,"hlbook_Configs","configid",false);

	if($serverid = $input->update()) {
		$matrix->rows[] = new Message("ok","ok","Config " . $_POST["title"] . " added");
	}
	else
		$matrix->rows[] = new Message("caution","caution",$input->error());
}

print form("addconfig");
$matrix->draw();
print "</form>";
?>
