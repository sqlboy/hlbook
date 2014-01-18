<?
class Layout extends XMLParser
{
	var $p_plugin;			// original mode
	var $p_task;			// original task

	var $pdata;				//	the loaded plugin's data
	var $tdata;				// the task's data

	var $options;

	var $rtags = array(
			"LAYOUT",
			"HEAD",
			"CSS",
			"JSCRIPT",
			"BODY",
			"INCLUDE",
			"TINCLUDE",
			"CONTENT",
			"SPACER",
			"FORCE",
			"BR",
			"BLOCK",
			"LOGO"
		);

	function Layout()
	{
		$this->strip_http_get_vars();
		$this->strip_http_post_vars();

		if($_GET["mode"])
		{
			$this->p_plugin = $_GET["mode"];
			if($_GET["task"])
				$this->p_task   = $_GET["task"];
			else
				$this->p_task = false;
		}
		else
		{
			$this->p_plugin = get_option("base","page_defaultmode");
		}	
	}

	/*
	draw, a wrapper function that encapsulates all page and access checks
	then starts the stepml parser.
	*/

	function draw()
	{
		global $SESSION;

		// if I can't load the page,error out
		if(!$this->load($this->p_plugin,$this->p_task))
		{
			header("Location: " . BASE_URL);
			return 0;
		}

		if(is_authed() && $SESSION->exists("auth_forward"))
		{
			$forward = $SESSION->get("auth_forward");
			$SESSION->del("auth_forward");

			header("location: " . $forward);
			return 1;
		}

		// if the users is logged in but goes to the login page, then redirect to main page
		if(is_authed() && $this->p_plugin == "auth" && $this->p_task == "login")
		{
			header("Location: " . BASE_URL);
			return 0;
		}

		// if we can't verify the user is logged in we send them to loging page
		if(!$this->verify_login())
		{
			#we should tr yo show the login page first, then when they login they are
			#in the same place
			session_set("auth_forward","http://" . $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"]);
			header("Location: " . SCRIPT_URL . "?mode=auth&task=login");
			return 0;
		}

		// if the user is logged in but is attempting to access a page that does not exist
		// or a page he does not have access to, we show them the page not found
		if(!$this->verify_acclevel() || !$this->verify_exists())
		{
			header("Location:" . SCRIPT_URL);
			return 0;
		}

		define("PLUGIN",$this->p_plugin);
		define("TASK",$this->p_task);

		define("FULL_PATH",PLUGIN_PATH . PLUGIN . "/");

		define("FULL_URL","http://" . $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"]);
		define("TASK_URL",SCRIPT_URL . "?mode=" . PLUGIN . "&task=" . TASK);
		define("MODE_URL",SCRIPT_URL . "?mode=" . PLUGIN);

		/* all the good stuff */
		$libpath = PLUGIN_PATH . PLUGIN . "/lib" . PLUGIN . ".inc";
		if(file_exists($libpath))
			include_once($libpath);

		/* load up user config for this plugin, if any*/
		if($this->pdata["userconfigtable"]) {
			global $AUTH;
			$AUTH->load_plugin_userconfig(PLUGIN,$this->pdata["userconfigtable"]);
		}

		// if the page is a redirect, we don't execute the layout
		if($this->tdata["redirect"])
			$this->load_content();
		else
		{
			$this->set_template();
			$this->parse_template();
		}
	}

	function strip_http_get_vars()
	{
		foreach($_GET as $key=>$value)
		{
			$value = preg_replace("/^[\.\-\\\\\/]+/","",$value);
			$value = urlencode($value);
			$_GET[$key] = urldecode($value);
		}
	}

	function strip_http_post_vars()
	{
		if(!$_POST)
			return true;

		foreach($_POST as $key=>$value)
		{
			$value = preg_replace("/([^a-zA-Z0-9\.[[:space:]]]+)/","",$value);
			$_POST[$key] = $value;
		}
	}

	function set_template()
	{
		if($this->tdata['layout']) {
			$template = $this->tdata['layout'];
		}elseif($this->pdata['layout']) {
			$template = $this->pdata["layout"];
		}else { $template = get_option("base","page_template"); }

		if(!$template) {
			print message("stop","Unable to load default template");
			exit();
		}

		if(file_exists(THEME_PATH . $template)) {
			$this->template = THEME_PATH . $template;
		}
		else {
			$this->template = LAYOUT_PATH . $template;
		}
	}

	/* Loads data from file or database */
	function load($mode,$task)
	{
		global $DB;

		/* Load the plugin data */
		$result = $DB->query("SELECT plugin,layout,login,acclevel,deftask,userconfigtable FROM base_Plugins WHERE plugin='" . $mode . "' && disabled=0");
		$this->pdata = $DB->fetch_assoc($result);
		$DB->free_result($result);

		if(!is_array($this->pdata))
		{
			error("Plugin was not found");
			return false;
		}	

		/* if no task was sent or the sent task does not exist, set the default task */
		if(isset($task) && $task)
			$this->p_task = $task;
		else
			$this->p_task = $this->pdata["deftask"];

		/* pull the task data */
		$result = $DB->query("SELECT page,layout,login,acclevel,title,redirect FROM base_Registry WHERE page='" . $this->p_task . "' && plugin ='" . $this->pdata["plugin"] . "' && disabled=0",false);
		$this->tdata = $DB->fetch_assoc($result);
		$DB->free_result($result);

		if(!is_array($this->pdata) || !is_array($this->tdata))
			return false;
		else
			return true;
	}

	function verify_login()
	{
		if(_AUTH_ == 3)
		{
			if($this->p_plugin == "auth" && $this->p_task == "login")
				return true;

			if($this->p_plugin == "base" && $this->p_task == "pagenotfound")
				return true;

			if(is_authed() == true)
				return true;

			return false;
		}
		elseif(_AUTH_ == 2 || _AUTH_ == 1)
		{
			if(check_auth($this->pdata["login"]) && check_auth($this->tdata["login"])) {
				return true;
			}
			return false;
		}
		elseif(_AUTH_ == 0)
			return true;

		return false;
	}

	function verify_acclevel()
	{
		global $AUTH;

		if(_AUTH_ == 3 || _AUTH_ == 2)
		{
			if(check_access($this->pdata["acclevel"]) && check_access($this->tdata["acclevel"])) {
				return true;
			}

			return false;
		}
		elseif(_AUTH_ == 1 || _AUTH_ == 0)
			return true;

		return false;
	}

	function get_tdata($key)
	{
		return $this->tdata[$key];
	}

	function get_pdata($key)
	{
		return $this->pdata[$key];
	}

	function verify_exists()
	{
		if(!file_exists(PLUGIN_PATH . $this->p_plugin . "/" . $this->p_task . ".inc"))
			return false;

		return true;
	}

	function load_theme_css()
	{
		print "<link REL=\"StyleSheet\" type=\"text/css\" HREF=\"" . ATHEME_URL . THEME . ".css\">\n";
	}

	function load_content()
	{
		/* we've already checked it exists */
		$path = PLUGIN_PATH . PLUGIN . "/" . TASK . ".inc";	
		$this->increment_page_views();
		require_once($path);
	}

	function increment_page_views()
	{
		global $DB;
		global $AUTH;

		if(!get_option("base","page_log_admin_views") && $AUTH->get_cfgvar("acclevel") & 65536)
			return true;

		if(get_option("base","page_log_task_views"))
			$DB->query("UPDATE base_Registry SET pageviews=pageviews+1 WHERE plugin='" . PLUGIN . "' && page='" . $this->task . "'",false);

		if(get_option("base","page_log_plugin_views"))
			$DB->query("UPDATE base_Plugins SET pageviews=pageviews+1 WHERE plugin='" . PLUGIN . "'",false);
	}

	function tag_open($parser,$name,$attrs)
	{
		if(!in_array($name,$this->rtags)) {
			$this->print_tag($name,$attrs);
			return 1;
		}

		switch($name)
		{
			case "BR":
				print "<br>";
				break;

			case "BODY":

				global $STYLE;
				$merge = false;

				#process the merge attribute
				if($attrs["MERGE"])
				{
					foreach (explode(";", $attrs["MERGE"]) as $v)
					{
						if (ereg("=", $v))
						{
							list($a, $b) = explode("=", $v);
							$merge[$a] = $b;
						}
					}
				}

				#tag,container,region
				print $STYLE->tag("body","Page","body",$merge);
				break;


			case "INCLUDE":

				if(preg_match("/\.\./",$attrs["SRC"]))
					break;

				if(file_exists(BASE_PATH . $attrs["SRC"]))
					require(BASE_PATH . $attrs["SRC"]);
				else
					print "File: " . BASE_PATH . $attrs["SRC"] . " does not exist";

				break;

			case "FORCE":
				if(!$attrs["WIDTH"])
					$attrs["WIDTH"] = 1;

				if(!$attrs["HEIGHT"])
					$attrs["HEIGHT"] = 1;

				print "<img src=\"" . IMAGE_URL . "space.gif\" width=\"" . $attrs["WIDTH"] . "\" height=\"" . $attrs["HEIGHT"] . "\">";
				break;

			case "CONTENT":
				print font("Page");
				$this->load_content();
				break;

			case "LOGO":
				include_once(THEME_PATH . get_option("base","page_style") . "/logo.html");
				break;

			case "TINCLUDE":
				include_once(THEME_PATH . get_option("base","page_style") . "/" . $attrs["SRC"]);
				break;
		}

	}

	function tag_close($parser,$name)
	{
		if(!in_array($name,$this->rtags)) {
			print "</$name>\n";
			return 1;
		}

		switch($name)
		{
			case "HEAD":

				$title = get_option("base","page_title") . " | " . $this->tdata["title"];

				print "<head>\n";
				print "<title>$title</title>\n";

				print get_option("base","page_additional_headers");
				$this->load_theme_css();

				print "</head>";
				break;

			case "BODY":
				print "</body>";
				break;

			case "LOGO":
				include_once(THEME_PATH . get_option("base","page_style") . "/endlogo.html");
				break;		

		}
	}

	function cdata_handler($parser,$data)
	{
		print $data;
	}
}
