<?

$matrix = new Matrix("hlbookoptions",array("rtype"=>"keyval"),
		false,"sql:SELECT keyname,value FROM base_Options WHERE plugin='hlbook'");

$matrix->header = new SimpleText("header","HLBook Screenshot Options");

$matrix->widgets[] = new Checkbox("scrn_allow_uploads",
		array("notes"=>"Allow screen shot uploads  Must have world writable screen shot folder and PHP compiled with GD 2.x.","title"=>"Allow Uploads"));

$matrix->widgets[] = new TextBox("scrn_max_bytes",array("maxlength"=>10),
		array("regexpreset"=>"num","notes"=>"Max file fize in bytes. Set to 0 for unlimited (dangerous).","title"=>"Max Screenshot File Size","required"=>false));

$matrix->widgets[] = new TextBox("scrn_max_size",array("maxlength"=>9),
		array("regexfilter"=>"^[\d]{1,4}x[\d]{1,4}$","notes"=>"Max resolution of uploaded screen shots. Must be in correct format","title"=>"Max Screenshot Resolution","required"=>false));

$matrix->widgets[] = new TextBox("scrn_full_size",array("maxlength"=>9),
		array("regexfilter"=>"^[\d]{1,4}x[\d]{1,4}$","notes"=>"Convert uploaded screenshots to this size.","title"=>"Full size screenshot size","required"=>false));

$matrix->widgets[] = new TextBox("scrn_thumb_size",array("maxlength"=>9),
		array("regexfilter"=>"^[\d]{1,4}x[\d]{1,4}$","notes"=>"Screenshot thumbnail size","title"=>"Thumb screenshot size","required"=>false));

$matrix->widgets[] = new Select("scrn_format",false,
		array("notes"=>"Uploaded screenshots will be converted to this file type.  All file types supported by your GD library are listed.","title"=>"Screenshot Format","required"=>true),
		false,
		false,
		get_supported_image_types()
	);

$matrix->widgets[] = new Select("scrn_mimetype",false,
		array("novalue"=>"all","nolabel"=>"Any Listed","notes"=>"Only allow uploads of this type.  Set to All to allow any type of image supported by your GD library.","title"=>"Screenshot Mimetype","required"=>false),
		false,
		false,
		get_supported_mime_types()
	);

$matrix->widgets[] = new TextBox("scrn_upload_count",array("maxlength"=>2),
		array("regexpreset"=>"num","notes"=>"Number of screenshots a user can upload at once","title"=>"ScreenShot Upload Count","default"=>1,"required"=>false));

$matrix->widgets[] = new TextBox("scrn_full_path",array("size"=>"40","maxlength"=>128),
		array("regexpreset"=>"path","notes"=>"File system location of full size screen shots","title"=>"Full Size Screenshot Location","required"=>false));

$matrix->widgets[] = new TextBox("scrn_thumb_path",array("size"=>"40","maxlength"=>128),
		array("regexpreset"=>"path","notes"=>"File system location of thumb size screen shots","title"=>"Thumb Size Screenshot Location","required"=>false));

$matrix->widgets[] = new TextBox("scrn_full_url",array("size"=>"40","maxlength"=>128),
		array("notes"=>"URL location of full size screen shot folder","title"=>"Full Size Screenshot URL","required"=>false));

$matrix->widgets[] = new TextBox("scrn_thumb_url",array("size"=>"40","maxlength"=>128),
		array("notes"=>"URL location of thumb size screen shot folder","title"=>"Thumb Size Screenshot URL","required"=>false));

$matrix->footer = new Submit("Submit",false,array("foot.td.align"=>"center"),"Apply");

if($_POST["Submit"])
{
	$input = new KeyValInput(&$matrix->widgets,"base_Options","keyname","value",false);

	if($input->update()) {
		$matrix->rows[] = new Message("ok","ok","HLBook Options Updated");
	}

	else
		$matrix->rows[] = new Message("caution","caution",$input->error());
}

print form("hlbooksoptions");
$matrix->draw();
print "</form>";
?>
