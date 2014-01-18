<?
class Session
{
	var $id;
	var $ipaddr;
	var $vars;

	var $expire;
	var $pclean;
	var $verify;

	var $anonymous_count = -1;
	var $login_count = -1;

	function Session()
	{
		$this->pclean			 	= 				get_option("auth","session_cleanup");
		$this->verify 				= 				get_option("auth","session_verify_ip");
		$this->expire				=				get_option("auth","session_expire");

		/* set the session cleanup % if non was set */
		if(!$this->pclean)
			$this->pclean = 50;

		/* Convert expiration time to seconds */
		if($this->expire)
			$this->expire = $this->expire * 3600;
		else
			$this->expire = 3600;

		/* Set the users IP if verify is on */
		if($this->verify > 0)
			$this->ipaddr = $_SERVER["REMOTE_ADDR"];
		else
			$this->ipaddr = 0;	

		/* Start the session */
		$this->start();	
	}

	function start()
	{
		if($_COOKIE["Session"])
		{
			$this->id = $_COOKIE["Session"];
			setcookie("Session",$this->id,time() + $this->expire);
		}
		else
		{
			$this->id = $this->generate_id();
			setcookie("Session",$this->id,time() + $this->expire);
			$this->insert();
		}


		$this->read();
	}

	function renew()
	{
		#expire current cookie
		setcookie("Session",$this->id,time() - 3600);

		#gererate a new cookie ID and set it.
		$this->id = $this->generate_id();
		setcookie("Session",$this->id,time() + $this->expire);

		#insert cookie
		$this->insert();
	}

	function generate_id()
	{
		#this should be random enough and it doesn't evne use time!

		$random1 = crypt(rand(1,65535),salt());
		$random2 = rand(1,65535);

		$id = md5($random1 . $_SERVER["REMOTE_PORT"] . $_SERVER["REMOTE_ADDR"] . $random2);

		return $id;
	}

	function verifyip($ip)
	{
		if($ip != $this->ipaddr)
			$this->renew();
	}

	function close()
	{
		$this->write();
		$this->cleanup();
	}

	function write()
	{
		global $DB;

		if(!$this->id) {
			return true;
		}

		if(!is_array($this->vars) || !count($this->vars)) {
			return true;
		}

		foreach ($this->vars as $key=>$value)
		{
			$data .= $key . "[" . strlen($value) . "]='" . $value . "';|";
		}

		$data = $DB->escape_string($data);

		$result = $DB->query("UPDATE auth_Sessions SET lastactive='" . time() . "',data='" . $data
				. "' WHERE sessionid='" . $this->id . "'");

		return true;
	}	

	function read()
	{
		global $DB;

		$result = $DB->query("SELECT ipaddr,data FROM auth_Sessions WHERE sessionid='" . $this->id . "' LIMIT 1");

		if($DB->num_rows($result) != 1)
		{
			#free the old result
			$DB->free_result($result);

			#renew the cookie
			$this->renew();

			#read the cookue again
			$result = $DB->query("SELECT ipaddr,data FROM auth_Sessions WHERE sessionid='" . $this->id . "' LIMIT 1");
		}	

		list($ip,$data) = $DB->fetch_row($result);
		$DB->free_result($result);

		if($this->verify > 0)
			$this->verifyip($ip);


		if(preg_match_all("/([\w]+)\[([\d]+)\]=\'(.*?)\';\|/",$data,$m,PREG_SET_ORDER))
		{
			for($i=0;$i < count($m);$i++) {

				$key = $m[$i][1];
				$len = $m[$i][2];
				$val = $m[$i][3];

				if(strlen($val) != $len) { continue; }
				$this->vars[$key] = $val;
			}
		}

		unset($ip,$data);
		return true;
	}

	function destroy()
	{
		unset($this->vars);
		unset($_COOKIE);

		global $DB;
		$DB->query("DELETE FROM auth_Sessions WHERE sessionid='" . $this->id . "'",false);
		unset($this->id);

		return true;
	}

	function log_user_agent()
	{
		if(!get_option("auth","session_log_user_agent"))
			return true;

		global $DB;

		$agent = strtolower($_SERVER["HTTP_USER_AGENT"]);

		if(strstr($agent,"opera"))
			$browser = "opr";
		elseif(strstr($agent,"msie"))
			$browser = "mse";
		elseif(strstr($agent,"netscape"))
			$browser = "com";
		elseif(strstr($agent,"nautilus"))
			$browser = "not";
		elseif(strstr($agent,"konqueror"))
			$browser = "kon";
		elseif(strstr($agent,"galeon"))
			$browser = "gal";
		elseif(strstr($agent,"mozilla"))
			$browser = "moz";
		else
			$browser = "oth";

		$DB->query("UPDATE auth_BrowserLog SET count=count+1 WHERE id='" . $browser . "'",false);
	}

	function log_user_os()
	{
		if(!get_option("auth","session_log_user_os"))
			return true;

		global $DB;

		$agent = strtolower($_SERVER["HTTP_USER_AGENT"]);

		if(strstr($agent,"win"))
		{
			if(strstr($agent,"windows 2000") || strstr($agent,"nt5.0") || strstr($agent,"nt 5.0"))
				$os = "w2k";
			elseif(strstr($agent,"windows xp") || strstr($agent,"nt5.1") || strstr($agent,"nt 5.1"))
				$os = "wxp";
			elseif(strstr($agent,"win98") || strstr($agent,"windows 98"))
				$os = "w98";
			elseif(strstr($agent,"win95") || strstr($agent,"windows 95"))
				$os = "w95";
			elseif(strstr($agent,"nt"))
				$os = "wnt";
			elseif(strstr($agent,"winme") || strstr($agent,"windows me"))
				$os = "wme";
			else
				$os = "oth";
		}
		elseif(strstr($agent,"mac"))
			$os = "mac";
		elseif(strstr($agent,"linux"))
			$os = "lin";
		elseif(strstr($agent,"sunos"))
			$os = "sun";
		elseif(strstr($agent,"freebsd"))
			$os = "fbd";
		elseif(strstr($agent,"beos"))
			$os = "bos";
		else
			$os = "oth";

		$DB->query("UPDATE auth_OSLog SET count=count+1 WHERE id='" . $os . "'",false);
	}


	function insert()
	{
		global $DB;

		$DB->query("INSERT INTO auth_Sessions (sessionid,ipaddr,lastactive)
				VALUES ('" . $this->id . "','" . $this->ipaddr . "','" . time() . "')");

		$this->log_user_agent();
		$this->log_user_os();

		return true;
	}

	function update()
	{
		global $DB;

		$DB->query("UPDATE auth_Sessions SET lastactive='" . time() . "' WHERE sessionid='" . $this->id . "'",false);

		if($DB->dberror())
		{
			$this->renew();
			return false;
		}
		else
			return true;
	}

	function cleanup()
	{
		global $DB;
		$rand = rand(1,100);

		if($this->pclean < $rand)
			return true;

		$DB->query("DELETE FROM auth_Sessions WHERE lastactive + $this->expire < " . time(),false);

		return true;
	}

	/* PUBLIC */

	function set($varname,$value)
	{
		$this->vars[$varname] = $value;
	}

	function exists($varname)
	{
		if(isset($this->vars[$varname]))
			return true;

		return false;
	}

	function get($varname)
	{
		return $this->vars[$varname];
	}

	function del($varname)
	{
		unset($this->vars[$varname]);
	}

	function get_id()
	{
		return $this->id;
	}

	function count_anonymous_sessions()
	{
		global $DB;

		if($this->anonymous_count != -1)
			return $this->anonymous_count;

		$result = $DB->query("SELECT count(userid) as count FROM auth_Sessions WHERE userid='0'");
		list($this->anonymous_count) = $DB->fetch_row($result);
		$DB->free_result($result);

		return $this->anonymous_count;
	}

	function count_authed_sessions()
	{
		global $DB;

		if($this->login_count != -1)
			return $this->login_count;

		$result = $DB->query("SELECT count(userid) as count FROM auth_Sessions WHERE userid!='0'");
		list($this->login_count) = $DB->fetch_row($result);
		$DB->free_result($result);

		return $this->login_count;
	}
}
