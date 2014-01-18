<?
function do_config(&$OPTIONS)
{
	global $DB;

	$result = $DB->query("SELECT plugin,keyname,value FROM base_Options");

	while(list($plugin,$key,$value) = $DB->fetch_row($result))
	{
		$OPTIONS[$plugin][$key] = $value;
	}
}

/*
Prototype:
bool = check_access(int,int)

Uses:
bitwise & returns true or false
*/

function check_access($req_access)
{
	if(!$req_access || $req_access == 0)
		return true;

	if($req_access & acclevel())
		return true;
	else
		return false;
}

function check_auth($authed)
{
	if($authed == false) {
		return true;
	}

	if($authed && is_authed()) {
		return true;
	}

	return false;
}

/*
Prototype:
array = parse_ini_section

Use:
Look for a specific section and return an array
*/
function parse_ini_section($file,$section)
{
	if(!file_exists($file))
		return false;

	if(!$fp = fopen($file,"r"))
		return false;

	while(!feof($fp))
	{
		$buffer = fgets($fp,1024);

		$buffer=str_replace("\t","",$buffer);
		$buffer=str_replace("\"","",$buffer);
		$buffer=str_replace("\'","",$buffer);

		if($buffer)
		{
			if($in == false && preg_match("/^\[($section)\]$/",$buffer))
			{
				$in = true;
				continue;
			}

			if($in == true)
			{
				if(preg_match("/^\[/",$buffer))
				{
					$in = false;
					break;
				}

				if(preg_match("/^([^;].*?)=(.*?);$/",$buffer,$match_b))
				{
					$key = $match_b[1];
					$val = $match_b[2];

							/* trim spaces from edges */
					$key = trim($key);
					$val = trim($val);
					$data[$key] = $val;
				}
			}
		}
	}

	if($data)
		return $data;
	else
		return false;
}

function convert_file_size($bytes)
{
	if($bytes < 1000)
		$size = $bytes . "b";
	elseif($bytes > 1000 && $bytes < 1000000)
	{
		$size = $bytes / 1000;
		$size = sprintf("%01.2f",$size);
		$size .= "Kb";
	}
	elseif($bytes > 1000000)
	{
		$size = $bytes / 1000000;
		$size = sprintf("%01.2f",$size);
		$size .= "Mb";
	}

	return $size;
}

#---------------------------------------------------
# Time functions
#---------------------------------------------------

function apply_offset()
{
	global $AUTH;

	if(get_option("base","apply_timezone") == 0) {
		return 0;
	}

	$offset = $AUTH->get_cfgvar("toffset");

	if($offset == "none") {
		return 0;
	}
	elseif(is_numeric($offset) && $offset != "none") {
		return ($offset * 3600);
	}
	else
	{
		$offset = get_option("base","default_timezone");
		if($offset == "none" || !$offset) {
			return 0;
		}

		return ($offset * 3600);
	}
}

function toffset()
{
	global $AUTH;

	if(get_option("base","apply_timezone") == 0) {
		return 0;
	}

	$offset = $AUTH->get_cfgvar("toffset");

	if($offset == "none") {
		return 0;
	}
	elseif(is_numeric($offset) && $offset != "none") {
		return ($offset * 3600);
	}
	else
	{
		$offset = get_option("base","default_timezone");
		if($offset == "none" || !$offset) {
			return 0;
		}

		return ($offset * 3600);
	}
}

function midnight($timestamp)
{
	list($month,$day,$year) = split(",",date("n,j,Y",$timestamp));
	$midnight = mktime(0,0,0,$month,$day,$year);

	return $midnight;
}

#---------------------------------------------------
# access functions
#---------------------------------------------------

function get_option($plugin,$key)
{
	global $OPTIONS;
	return $OPTIONS[$plugin][$key];
}

function get_pagetitle()
{
	global $LAYOUT;
	return $LAYOUT->get_tdata("title");
}

function userid()
{
	global $AUTH;
	return $AUTH->get_cfgvar("userid");
}

function acclevel()
{
	global $AUTH;
	return $AUTH->get_cfgvar("acclevel");
}

function is_authed()
{
	global $AUTH;
	return $AUTH->ok;
}

function session_del($varname)
{
	global $SESSION;
	$SESSION->del($varname);
}

function session_set($varname,$value)
{
	global $SESSION;
	$SESSION->set($varname,$value);

	return true;
}

function session_get($varname)
{
	global $SESSION;
	return $SESSION->get($varname);
}

function session_exists($varname)
{
	global $SESSION;

	if($SESSION->exists($varname))
		return true;

	return false;
}

function get_defined_access($name,$plugin)
{
	global $DB;

	$result = $DB->query("SELECT acclevel FROM auth_Acclevels WHERE accname='" . $name . "' && plugin='" . $plugin . "' ORDER BY acclevel asc LIMIT 1");
	list($acclevel) = $DB->fetch_row($result);
	$DB->free_result($result);

	if(!$acclevel)
		$acclevel = 65536;

	return $acclevel;
}

function salt() {

	if(_SALT_ == "DES" && CRYPT_STD_DES == 1) {
		$length = 2;
		$prefix = "";
	}
	elseif(_SALT_ == "MD5" && CRYPT_MD5 == 1) {
		$length = 9;
		$prefix = "$1$";
	}
	else {
		$lenth = 2;
		$prefix = "";
	}

	$salt = "";	
	for($x=0;$x<$length;$x++) {
		$salt .= substr(crypt(rand(1,65536)),3,1);
	}

	return $prefix . $salt;
}

#---------------------------------------------------
# Filter functions
#---------------------------------------------------

function is_email($email) {
	if(eregi("^[a-z0-9\._-]+@+[a-z0-9\._-]+\.+[a-z]{2,3}$", $email)) return TRUE;
	else return FALSE;
}

#fix this crap
function is_url($url) {
	if(eregi("^htt(p|ps)://",$url)) return TRUE;
	else return FALSE;
}

function is_ipaddr($ip) {
	if(preg_match("/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/",$ip))
		return TRUE;
	else
		return FALSE;
}

/*
Prototype:
void = debug_array(name string, array array)
*/

function debug_array($name,$array)
{
	print "<b>$name</b><br>\n";
	print "<pre>\n";
	print_r ($array);
	print "</pre>\n";
}

/*
Prototype:
array = search_delimited_file(file,string,delimiter,offset)

Use:
Search a delimited file for ^xxxx and output the array
of values

Notes:
basically used for searching a tab delimited file for a key
and outputting the rest of the data via an array, use the offset
to skip to a specific column.
*/

function search_delimited_file($file,$string,$delimiter,$offset,$return = -1)
{
	if(!file_exists($file))
	{
		return false;
	}	
	if(!$fp = fopen($file,"r"))
	{
		return false;
	}

	while(!feof($fp))
	{
		$buffer = fgets($fp,1024);
		$buffer=str_replace("\n","",$buffer);

		$data = split($delimiter,$buffer);

		foreach($data as $value)
		{
			if($data[$offset] == $string)
			{
				if($return == -1)
					return $data;
				else
					return $data[$return];
			}
		}
	}

	fclose($fp);

}

/* decided not to make the form tag OO.  Not sure why, seems a little too much */

function form($name,$action=false,$method="POST",$enctype="")
{
	if(!$action)
		$action = $_SERVER["REQUEST_URI"];

	if($enctype)
		$_enctype=" enctype=\"" . $enctype . "\"";


	$html = "<form name=\"$name\" ACTION=\"" . $action . "\" METHOD=\"" . $method . "\"$_enctype>";
	return $html;
}

function message($type,$message,$class=false,$clean=true)
{
	$html = "<img src=\"" . IMAGE_URL . "icons/toolbar/" . $type . ".png\" align=\"center\">";

	if($class)
		$html .= font($class);

	$html .= $message;

	if($class)
		$html .= "</font>";

	return $html;
}

function makeQueryString($append,$remove)
{
	foreach($_GET as $k=>$v)
	{
		if(in_array($k,$append) || in_array($k,$remove))
			continue;

		if($qs)
			$qs .= "&";

		$qs .= $k . "=" . $v;
	}

	foreach($append as $k=>$v)
	{
		if($qs)
			$qs .= "&";

		$qs .= $k . "=" . $v;
	}

	return SCRIPT_URL . "?" . $qs;
}

function img_force($width,$height)
{
	$html .= "<img src=\"" . IMAGE_URL . "space.gif\" width=\"" . $width . "\" height=\"" . $height . "\">";

	return $html;
}

function whatis_displayname($userid)
{
	global $DB;

	if(!is_numeric($userid)) {
		return false;
	}

	$result = $DB->query("SELECT displayname FROM auth_Users WHERE userid='" . $userid . "'");
	list($displayname) = $DB->fetch_row($result);
	$DB->free_result($result);

	return $displayname;
}

function get_supported_image_types()
{
	$imgtypes = array("gif"=>IMG_GIF,"png"=>IMG_PNG,"jpg"=>IMG_JPG,"bmp"=>IMG_WBMP);
	$return = array();

	foreach ($imgtypes as $k=>$v) {
		if(imagetypes() & $v){
			$return[$k] = $k;
		}
	}

	return $return;
}

function get_supported_mime_types()
{

	$imgtypes = array("gif"=>IMG_GIF,"png"=>IMG_PNG,"jpeg"=>IMG_JPG,"bmp"=>IMG_WBMP);
	$return = array();

	foreach ($imgtypes as $k=>$v) {
		if(imagetypes() & $v){
			$return[$k] = $k;
		}
	}

	return $return;
}

class Timer
{
	var $start;
	var $stop;
	var $time;

	function Timer()
	{
		$this->start = microtime();
		$time = explode(" ",$this->start);
		$this->start = $time[1] + $time[0];
	}

	function stop()
	{
		$this->stop = microtime();
		$time = explode(" ",$this->stop);
		$this->stop = $time[1] + $time[0];
	}

	function diff()
	{
		$this->time = $this->stop - $this->start;
		return $this->time;
	}
}

function color_hex_to_dec($hex)
{
	$hex = str_replace("#","",$hex);

	if(strlen($hex) == 6)
	{
		$r = hexdec($hex[0] . $hex[1]);
		$g = hexdec($hex[2] . $hex[3]);
		$b = hexdec($hex[4] . $hex[5]);
	}

	$rgb = array($r,$g,$b);

	return $rgb;
}

/*
Prototype:
string = attribute_array_to_string($array)
*/

function aa_to_string($array)
{
	if(!is_array($array))
		return false;

	foreach($array as $k=>$v)
	{
		$html .= " " . $k . "=\"" . $v . "\"";
	}

	return $html;
}

?>
