<?
/*  
	 HLBook - Half-Life server booking system
    Copyright (C) 2001,2002,2003 Playway.net LLC

     This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

*/
// the version stuff
define("_VERSION_","0.4.6");
define("HLB_VERSION","0.99-openbeta1");
/*------------------------------------------
Configuration
------------------------------------------*/
// the database connection
define("DB_NAME", "hlbook");
define("DB_USER", "hlbook");
define("DB_PASS", "hlbook");
define("DB_ADDR", "localhost");
define("DB_PCONNECT", 0);

// 0 logins off, no security what so ever, everyone admin
// 1 logins on but no security levels (everyone logged in is admin)
// 2 logins on, security levels on (leave HLBook on this setting)
// 3 logins required,security levels on (if you want all pages to required a login)

define("_AUTH_",2);

//If you are attempting to run this on windows, set this to 0.
define("_CRYPT_",1);
// DES or MD5.  md5 is more secure, DES is probably good enough
define("_SALT_","DES");

// some stupid things I use
define("_FALSE_",-1);
define("_TRUE_",1);

// the name of the db class to load
define("_DB_","mysql");

// the base file system path for your hlbook installation
define("BASE_PATH","/home/hlbook/");

// The url where the contents of the "webdocs" is stored
define("BASE_URL","http://localhost/hlbook/");

// The file system path where the contents of "webdocs" is stored
define("WWW_PATH",BASE_PATH . "webdocs/");

// this other stuff will usually just fall into place.  If your moving dirs
// around thoough you might have to change it.

#URLS
define("SCRIPT_URL",BASE_URL . "index.php");
define("IMAGE_URL",BASE_URL . "images/");
define("THEME_URL",BASE_URL. "themes/");

#FILE SYSTEM PATHS
define("IMAGE_PATH",WWW_PATH . "images/");
define("THEME_PATH",WWW_PATH . "themes/");
define("LIB_PATH",BASE_PATH . "libs/");
define("LAYOUT_PATH",BASE_PATH . "layouts/");
define("CONF_PATH",BASE_PATH . "conf/");
define("PLUGIN_PATH",BASE_PATH . "plugins/");

/*------------------------------------------
End Configuration

You should stop here unless your curious
------------------------------------------*/

function error($string,$title = "Fatal Error",$exit=true)
{
?>
	<table cellspacing="1" cellpadding="2" border="1" width="50%">
	<tr bgcolor="#FF0000"><td align="center"><font color="#000000">
		<b><? echo $title;?></b>
	</td></tr>
	<tr bgcolor="#FFFFFF"><td valign="top">
	<font color="#000000"><? echo $string;?></font>
	</td></tr></table>
<?
	if($exit)
		die();
}

if(!is_readable(LIB_PATH)) {
	error("Cannot find your PHPStep library path:<br><br> " . LIB_PATH);
	exit();
}

// include required functions
require(LIB_PATH . "base/xmlparser.php");
require(LIB_PATH . "base/fileuploads.php");
require(LIB_PATH . "base/widgets.php");
require(LIB_PATH . "base/containers.php");
require(LIB_PATH . "base/utils.php");
require(LIB_PATH . "base/styles.php");
require(LIB_PATH . "base/" . _DB_ . ".php");
require(LIB_PATH . "base/layout.php");
require(LIB_PATH . "base/auth.php");
require(LIB_PATH . "base/session.php");
require(LIB_PATH . "base/input.php");
require(LIB_PATH . "base/email.php");

register_all_widgets();
register_all_containers();
$DB = new db;
do_config(&$OPTIONS);
$SESSION = new Session();
$AUTH = new Auth(_CRYPT_);
$STYLE = new Style($OPTIONS["base"]["page_style"]);
$LAYOUT= new Layout();
$LAYOUT->draw();
$SESSION->close();
exit;
