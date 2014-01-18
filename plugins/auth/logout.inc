<?
global $AUTH;

if(_AUTH_ == 0)
{
	print "The user authentication plugin has been disabled.";
}
elseif($AUTH->is_authed() && _AUTH_ > 0)
{
	$AUTH->logout();
	unset($AUTH);
}

header("location:" . SCRIPT_URL . "?mode=auth&task=login");

?>
