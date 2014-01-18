<?php
switch ($type) {
	case "rcon":

		if (! empty ($command)) {
			$this->send      = "ÿÿÿÿchallenge rcon\0";
			$this->resp      = "ÿÿÿÿchallenge rcon ";
			$challenge = trim ($this->net_u ($this->ip, $this->port));

        // fixed by Fabian Lucas [16/10/02]
			$this->send = "ÿÿÿÿrcon ${challenge} \"$this->pass\" ${command}\0";
			$this->resp = "ÿÿÿÿl|þÿÿÿ.{1}";
			$rcon = $this->net_u ($this->ip,$this->port);
		};
		break;

	case "status":
		$this->send   = "ÿÿÿÿinfostring\0";
		$this->resp   = "ÿÿÿÿinfostringresponse";
		$result = $this->net_u ($this->ip, $this->port);

		if ($query == "info") {
			if (! empty ($result)) {
				$status_array = explode ("\\", $result);

				if ($VIEW) {
					if (in_array ("map", $STATES)) {
						$map_index = array_search ("map", $status_array);
						$status    = $status . $status_array[$map_index+1] . "\n";
					};

					if (in_array ("player", $STATES)) {
						$cur_index = array_search ("players", $status_array);
						$max_index = array_search ("max", $status_array);
						$status    = $status . $status_array[$cur_index+1] . "/" . $status_array[$max_index+1] . "\n";
					};

					if (in_array ("name", $STATES)) {
						$host_index = array_search ("hostname", $status_array);
						$status     = $status . $status_array[$host_index+1] . "\n";
					};
				} else {
					$cur_index  = array_search ("players", $status_array);
					$host_index = array_search ("hostname", $status_array);
					$max_index  = array_search ("max", $status_array);
					$game_index = array_search ("gamedir", $status_array);
					$map_index  = array_search ("map", $status_array);
					$pass_index = array_search ("password", $status_array);
					$password   = intval ($status_array[$pass_index+1]);

					if ($password == 1) {
						$pass = "Yes";
					} else {
						$pass = "No";
					};

					$this->info = array (
							'Server IP'=>"$this->ip:$this->port",
							'Hostname'=>$status_array[$host_index+1],
							'map'=>$status_array[$map_index+1],
							'Mod'=>$status_array[$game_index+1],
							'Password'=>$pass,
							'Cur. player'=>$status_array[$cur_index+1],
							'Max. player'=>$status_array[$max_index+1]
						);
				};
			};
		};

		if ($query == "rules") {
			$send  = "ÿÿÿÿrules\0";
			$resp  = "þÿÿÿ.{5}|ÿÿÿÿ...|\r";
			$rules = net_u ($ip, $queryport);

			if (! empty ($rules)) {
				$rules_array = explode ("\0", $rules);
				$rules       = array ();
				$j           = 0;

				array_pop ($rules_array);

				if (! empty ($RULE)) {
					for ($i = 0; $i < count ($rules_array); $i+=2) {
						if (in_array ($rules_array[$i], $RULE)) {
							$rules[$j] = array ($rules_array[$i], $rules_array[$i+1]);
							$j++;
						};
					};
				} else {
					for ($i = 0; $i < count ($rules_array); $i+=2) {
						$rules[$j] = array ($rules_array[$i], $rules_array[$i+1]);
						$j++;
					};
				};
			};
		};

		if ($query == "players") {
			$this->send = "ÿÿÿÿplayers\0";
			$this->resp = "ÿÿÿÿD";
			$player = $this->net_u ($this->ip, $this->port);

			if (! empty ($player)) {
				$header = array (
						'player' => array (
							'name'  => 'Player',
							'width' => '%-30s'
						),

						'time'   => array (
							'name'  => 'Time',
							'width' => '%-8s'
						),

						'frags'  => array (
							'name'  => 'Frags',
							'width' => '%4d'
						)
					);

				$player_count = 0;
				$player_array = array ();

          // Thanks to Henrik Beige from www.PHPRcon.net for this code snippet
          // I could not make it better then him :)

				$player = substr($player, 1);

				for ($player_counter = 1; strlen ($player) > 0; $player_counter++) {
					$player_end = strpos ($player, "\0");
					$player_array[$player_counter][player] = substr ($player, 1, $player_end-1);

					$time                                  = @unpack ('ftime', substr ($player, $player_end + 5, 4));
					$player_array[$player_counter][time]   = date ('i:s', round ($time['time'], 0) + 82800);

					$player_array[$player_counter][frags]  = ord ($player[$player_end+1]) +
						(ord ($player[$player_end+2]) << 8) +
						(ord ($player[$player_end+3]) << 16) +
						(ord ($player[$player_end+4]) << 24);

					$player = substr ($player, $player_end + 9);
				};
				$this->players = &$player_array;	
			};
		};

		break;
};
?>
