<?
$login= new VList("vlist",array("class"=>"Table"),array("body.table.width"=>"300"));
$login->header = new SimpleText("header",get_option("base","page_title") . " Login");

$login->widgets[] = new SimpleText("username","Username:");
$login->widgets[] = new TextBox("auth_username",array("size"=>16,"maxlength"=>32));

$login->widgets[] = new SimpleText("username","Password:");
$login->widgets[] = new Password("auth_password",array("size"=>16,"maxlength"=>32));

$login->widgets[] = new Submit("user_login",false,false,"Login");
?>
<center><? 
print form("Login");
$login->draw();
print "</form>";?>

</center>
