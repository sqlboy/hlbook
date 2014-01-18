<?
function verify_userid($userid)
{
	global $DB;

	$result = $DB->query("SELECT userid,acclevel,username,displayname FROM auth_Users WHERE userid='" . $userid . "'");
	$row = $DB->fetch_row($result);
	$DB->free_result($result);

	return $row;
}

function get_user_rank($userid)
{
	global $DB;
	$result = $DB->query("SELECT userid FROM auth_Users ORDER BY logins DESC");
	while(list($_userid) = $DB->fetch_row($result))
	{
		$rank++;
		if($userid == $_userid)
			break;
	}
	$DB->free_result($result);
	return $rank;
}

function verify_plugin(&$rowdata,$plugin)
{
	global $DB;

	$result = $DB->query("SELECT * FROM base_Plugins WHERE plugin='" . $plugin . "' LIMIT 1");
	$rowdata = $DB->fetch_assoc($result);

	if(is_array($rowdata))
		return true;
	else
		return false;
}

?>
