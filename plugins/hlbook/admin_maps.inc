<?
$search = new SimpleSearch("mapsearch","hlbook_Maps");

$search->search[] = new Select("modid",false,array("title"=>"Mod/Game","nolabel"=>"Any","novalue"=>"0","sticky"=>true,"required"=>false,"search_type"=>"exact"),false,false,"sql:SELECT modid,title FROM hlbook_Mods");
$search->search[] = new TextBox("name",false,array("title"=>"Map Name","sticky"=>true,"required"=>false,"search_type"=>"regex"));
$search->footer = new Submit("Submit",false,array("foot.td.align"=>"center"),"Search");

$search->results[] = new Label("del",MODE_URL . "&task=admin_mapsdel&mapname=%[name]",false,array("body.td.width"=>"1"),"Delete");
$search->results[] = new Label("name",false,
		array("title"=>"Map Name","sort"=>true));
$search->results[] = new Label("modid",false,array("title"=>"Mod","sort"=>true),false,"sql:SELECT title FROM hlbook_Mods WHERE modid='%[modid]'");

$search->draw();
?>
