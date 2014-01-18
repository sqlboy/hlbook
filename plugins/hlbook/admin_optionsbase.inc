<?

$matrix = new Matrix("baseoptions",array("rtype"=>"keyval"),
		false,"sql:SELECT keyname,value FROM base_Options WHERE plugin='base'");

$matrix->header = new SimpleText("header","Base PHPStep Options");
$matrix->widgets[] = new TextBox("page_title",array("size"=>"64","maxlength"=>64),
		array("notes"=>"The title that appears in on your web browser","title"=>"Page Title","required"=>true));

$matrix->widgets[] = new TextBox("page_contact_name",array("size"=>"64","maxlength"=>64),
		array("notes"=>"The sites contact name","title"=>"Page Contact","required"=>true));

$matrix->widgets[] = new TextBox("page_contact_email",array("size"=>"64","maxlength"=>64),
		array("regexpreset"=>"email","notes"=>"The sites contact email address","title"=>"Page Contact Email","required"=>true));

$matrix->widgets[] = new Select("page_style",false,array("title"=>"The page style","required"=>true),false,false,array("orange"=>"Orange","black"=>"Black"));

$matrix->footer = new Submit("Submit",false,array("foot.td.align"=>"center"),"Apply");

if($_POST["Submit"])
{
	$input = new KeyValInput(&$matrix->widgets,"base_Options","keyname","value",false);

	if($input->update()) {
		$matrix->rows[] = new Message("ok","ok","PHPStep Base Options Updated");
	}

	else
		$matrix->rows[] = new Message("caution","caution",$input->error());
}

print form("baseoptions");
$matrix->draw();
print "</form>";
?>
