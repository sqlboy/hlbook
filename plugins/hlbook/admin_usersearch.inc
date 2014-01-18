<?

$search = new SimpleSearch("usersearch","auth_Users");
$search->search[] = new TextBox("username",false,array("title"=>"Username:","sticky"=>true,"required"=>false,"search_type"=>"regex"));
$search->search[] = new TextBox("displayname",false,array("title"=>"Display Name","sticky"=>true,"required"=>false,"search_type"=>"regex"));
$search->search[] = new TextBox("email",array("size"=>32,"maxlength"=>64),array("title"=>"Email","search_type"=>"regex"));
$search->search[] = new BitwiseCheckbox("acclevel",array("title"=>"Access Level","sticky"=>true),array("body.td.valign"=>"top"),false,"sql:SELECT acclevel,title FROM auth_Acclevels WHERE plugin='hlbook'"); 
$search->search[] = new Hidden("userid");

$search->footer = new Submit("Submit",false,array("foot.td.align"=>"center"),"Search");

$search->results[] = new Label("del",MODE_URL . "&task=admin_userdelconf&userid=%[userid]",false,array("body.td.width"=>"1"),"Delete");
$search->results[] = new Label("edit",MODE_URL . "&task=admin_useredit&userid=%[userid]",false,array("body.td.width"=>"1"),"Edit");
$search->results[] = new Label("displayname",false,array("title"=>"Displayname"));
$search->results[] = new BoolText("disabled",false,array("title"=>"Disabled?"));
$search->results[] = new BitwiseText("acclevel",false,array("title"=>"Can Book?"),false,false,get_defined_access("hlbook","hlbook"));
$search->results[] = new BitwiseText("acclevel",false,array("title"=>"Admin?"),false,false,get_defined_access("hladmin","hlbook"));
$search->results[] = new Label("email","mailto:%[email]",array("title"=>"Email"));

$search->draw();

?>
