<?

$matrix = new Matrix("adminmapadd");
$matrix->header = new SimpleText("header","Add Map");

$matrix->rows[] = new SimpleText(false,"Adding a map to the database will allow users to specify the map that their booking starts on.  In the future users will be able to search and download maps from HLBook");
$matrix->widgets[] = new TextBox("name",array("maxlength"=>"32"),array("title"=>"Map Name","sticky"=>true,"required"=>true));
$matrix->widgets[] = new Select("modid",false,array("title"=>"Mod","sticky"=>true,"required"=>true),false,false,"sql:SELECT modid,title FROM hlbook_Mods");

$matrix->footer = new Submit("Submit",false,array("foot.td.align"=>"center"),"Apply");

if($_POST["Submit"])
{
	$input = new RowInput(&$matrix->widgets,"hlbook_Maps","name",false);

	if($serverid = $input->update()) {
		$matrix->rows[] = new Message("ok","ok","Map " . $_POST["name"] . " added.");
	}
	else
		$matrix->rows[] = new Message("caution","caution",$input->error());
}

print form("addmap");
$matrix->draw();
print "</form>";
?>
