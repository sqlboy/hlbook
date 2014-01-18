<?php
#
# Adapted from PHP Rcon
# http://www.tuts.net/~titulaer/
#

class Rcon
{
	var $send;
	var $resp;
	var $output;

	var $ip;
	var $port;
	var $game;
	var $pass;

	var $games = array("hl");
	var $errors = array();

	var $rules;
	var $info;
	var $players;

	function Rcon($ip,$port,$pass=false,$game = "hl")
	{
		$this->ip 		= $ip;
		$this->port		= $port;
		$this->pass		= $pass;
		$this->game		= $game;

		return 1;
	}

	function rcon_cmd($command)
	{
		$type = "rcon";

		if(! in_array($this->game,$this->games)) {
			$this->errors[] = "That game type is not supported";
			return 0;
		}

		include BASE_PATH . "phprcon/game_" . $this->game . ".inc.php";
		return 1;

	}

	function status_cmd($query)
	{
		$type = "status";

		if(! in_array($this->game,$this->games)) {
			$this->errors[] = "That game type is not supported";
			return 0;
		}

		include BASE_PATH . "phprcon/game_" . $this->game . ".inc.php";
		return 1;
	}

	function net_u ($ip, $port) {

		unset ($this->output);

		$length = strlen ($this->send);

		if ($length > 240) {
			print ("Command is too long to be executed!\n");
			own_exit (1);
		};

		$connect = fsockopen ("udp://" . $this->ip, $this->port);
		socket_set_timeout ($connect, 1, 000000);

		if (! $connect) {
			print ("Connection could not be established!\n");
			return 0;
		};

		fwrite ($connect, $this->send);
		$output = fread ($connect, 1);

    	// corrected by Hervé Loterie [20/08/02]

		if (! empty ($output)) {
			do {
				$status_pre = socket_get_status ($connect);
				$output = $output . fread ($connect, 1);
				$status_post = socket_get_status ($connect);
			} while ($status_pre[unread_bytes] != $status_post[unread_bytes]);
		};

		fclose ($connect);

		$output = trim (str_replace ("\0", chr (253), $output));
		if (! empty ($this->resp)) {
			$output = ereg_replace ($this->resp, "", $output);
		};

		$output = str_replace (chr (253), "\0", $output);
		return $output;
	}

	function net_t ($ip, $port) {
		global $DEBUG;
		if ($DEBUG) {
			echo "net_t\n";
			$numargs = func_num_args ();
			$arg_list = func_get_args ();
			echo "$numargs argument(s):";
			for ($ijk = 0; $ijk < $numargs; $ijk++) {
				echo " \"$arg_list[$ijk]\"";
			};
			echo "\n";
		};

		global $send;
		global $resp;
		unset ($output);

		$length = strlen ($send);

		if ($length > 240) {
			print ("Command is too long to be executed!\n");
			own_exit (1);
		};

		$connect = @fsockopen ($ip, $port);
		socket_set_timeout ($connect, 1, 000000);

		if (! $connect) {
			print ("Connection could not be established!\n");
			return 0;
		};

		fwrite ($connect, $send);
		$output = fread ($connect, 1);

		if (! empty ($output)) {
			do {
				$status_pre = socket_get_status ($connect);
				$output = $output . fread ($connect, 1);
				$status_post = socket_get_status ($connect);
			} while ($status_pre[unread_bytes] != $status_post[unread_bytes]);
		};

		fclose ($connect);

		$output = trim (str_replace ("\0", chr (253), $output));
		if (! empty ($resp)) {
			$output = ereg_replace ($resp, "", $output);
		};
		$output = str_replace (chr (253), "\0", $output);

		return ($output);
	}
}

?>
