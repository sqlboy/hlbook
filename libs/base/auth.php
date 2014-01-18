<?
Class Auth
{
	var $ok = false;
	var $username;
	var $password;
	var $userdata;
	var $error;
	var $userid;
	var $crypt;

	function Auth()
	{
		global $SESSION;

		$this->crypt = _CRYPT_;

		if(_AUTH_ == 0)
		{
			$this->ok = true;
		}
		elseif(_AUTH_ > 0)
		{
			if($_POST["auth_username"] && $_POST["auth_password"])
			{
				$this->username = $_POST["auth_username"];
				$this->password = $_POST["auth_password"];

				if($this->authenticate())
				{
					$this->update_login_stats();	
				}	
			}
			elseif ($SESSION->exists("username") && $SESSION->exists("password"))
			{
				$this->username = $GLOBALS["SESSION"]->get("username");
				$this->password = $GLOBALS["SESSION"]->get("password");

				if($this->crypt) { $this->crypt = 0;}
				$this->authenticate();
			}

			unset($this->username);
			unset($this->password);
		}
	}

	function authenticate()
	{
		global $DB;

		$result = $DB->query("SELECT userid,password FROM auth_Users
						WHERE username='" . $this->username . "' && disabled=0 LIMIT 1");

		list($userid,$upass) = $DB->fetch_row($result);
		$DB->free_result($result);

		if($upass == "" || !$upass)
		{
			$this->error  = "Your login information is incorrect.";
			return false;
		}

		if($this->crypt)
		{
			if(crypt($this->password,$upass) == $upass)
				$this->ok = $this->register_user_online($userid);
			else
			{
				$this->error  = "Your login information is incorrect.";
				return false;
			}
		}
		else
		{
			if($upass == $this->password)
				$this->ok = $this->register_user_online($userid);
			else
			{
				$this->error = "Your login information is incorrect.";
				return false;
			}	
		}

		return $this->ok;
	}

	function register_user_online($userid)
	{
		global $DB,$SESSION;

		$result = $DB->query("SELECT * FROM auth_Users WHERE userid='" . $userid . "'",false,"Register User Online");
		$this->userdata = $DB->fetch_assoc($result);
		$DB->free_result($result);

		if(is_array($this->userdata))
		{
			$this->userid = $userid;
			$this->update_session_userid();
			$SESSION->set("username",$this->username);
			$SESSION->set("password",$this->userdata["password"]);
			return true;
		}
		else
			return false;
	}

	function load_plugin_userconfig($plugin,$table)
	{
		global $DB;

		if(!$this->ok) { return 0; }
		if($this->userdata[$plugin]) { return 0; }

		$result = $DB->query("SELECT * FROM " . $table . " WHERE userid='" . $this->userdata["userid"] . "' LIMIT 1");
		$rowdata = $DB->fetch_assoc($result);
		$DB->free_result($result);

		$this->userdata[$plugin] = $rowdata;
	}

	function update_session_userid()
	{
		if(!get_option("auth","session_track_userid"))
			return true;

		global $DB;
		$DB->query("UPDATE auth_Sessions SET userid='" . $this->userid . "' WHERE sessionid='" . $GLOBALS["SESSION"]->get_id() . "'",false);

		return true;
	}

	function update_login_stats()
	{
		global $DB;

		$DB->query("UPDATE auth_Users SET logins=logins+1,lastlogin='" . time() . "' WHERE userid='" . $this->userid . "'");
		if($DB->dberror())
			return false;
		else
			return true;
	}

	function logout()
	{
		if($this->ok)
		{
			$GLOBALS["SESSION"]->destroy();
			unset($this->username);
			unset($this->password);
			unset($this->ok);
		}	
	}

	function insert($key,$value)
	{
		if($this->userdata[$key]) { return  false; }
		$this->userdata[$key] = $value;

		return true;
	}

	function set($key,$value)
	{
		if(!$this->userdata[$key]) { return false; }
		$this->userdata[$key] = $value;

		return true;
	}

	function is_authed()
	{
		return $this->ok;
	}

	function get_user_stats()
	{
		if($this->ok)
		{
			return array($this->userid,$this->userdata["acclevel"]);
		}
		else
			return false;
	}

	function get_user()
	{
		if($this->ok) { return $this->userdata; }

		return false;
	}

	# gets a base CFG var
	function get_cfgvar($key)
	{
		return $this->userdata[$key];
	}

	# gets a plugin cfg var
	function get_pcfgvar($plugin,$key)
	{
		return $this->userdata[$plugin][$key];
	}

	#gets a plugin cfgvar from current plugin
	function get_ccfgvar($key)
	{
		return $this->userdata[PLUGIN][$key];
	}

	function show_login_page()
	{
		include(PLUGIN_PATH . "auth/loginpage.inc");
	}
}
