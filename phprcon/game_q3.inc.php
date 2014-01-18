<?php
  switch ($type) {
    case "rcon":
      if (! empty ($command)) {
        $send = "ÿÿÿÿrcon \"${password}\" ${command}\0";
        $resp = "ÿÿÿÿprint\n";
        $rcon = net_u ($ip, $rconport);
      };
      break;

    case "status":
      $send               = "ÿÿÿÿgetstatus";
      $resp               = "ÿÿÿÿstatusResponse\n\\\\|\^[0-9]";
      $result             = net_u ($ip, $queryport);
      $result_array       = explode ("\n", $result);
      $result_array_count = count ($result_array);

      if ($STATUS) {
        if (! empty ($result)) {
          $status_array = explode ("\\", $result_array[0]);
          $cur          = $result_array_count - 1;

          if ($VIEW) {
            if (in_array ("map", $STATES)) {
              $map_index = array_search ("mapname", $status_array);
              $status    = $status . $status_array[$map_index+1] . "\n";
            };

            if (in_array ("player", $STATES)) {
              $max_index = array_search ("sv_maxclients", $status_array);
              $status    = $status . $cur . "/" . $status_array[$max_index+1] . "\n";
            };

            if (in_array ("name", $STATES)) {
              $host_index = array_search ("hostname", $status_array);
              $status     = $status . $status_array[$host_index+1] . "\n";
            };
          } else {
            $host_index = array_search ("sv_hostname", $status_array);
            $max_index  = array_search ("sv_maxclients", $status_array);
            $game_index = array_search ("gamename", $status_array);
            $map_index  = array_search ("mapname", $status_array);
            $pass_index = array_search ("g_needpass", $status_array);
            $password   = intval ($status_array[$pass_index+1]);

            if ($password == 1) {
              $pass = "Yes";
            } else {
              $pass = "No";
            };
          
            $status = array (
              array ('Server IP', "${ip}:${gameport}"),
              array ('Hostname', $status_array[$host_index+1]),
              array ('current map', $status_array[$map_index+1]),
              array ('Mod', $status_array[$game_index+1]),
              array ('Password', $pass),
              array ('Cur. player', $cur),
              array ('Max. player', $status_array[$max_index+1])
            );
          };
        };
      };

      if ($RULES) {
        if (! empty ($result)) {
          $rules_array = explode ("\\", $result_array[0]);
          $rules       = array ();
          $j           = 0;

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

      if ($PLAYER) {                
        if (! empty ($result)) {    
          $header = array (       
                      'player' => array (
                                    'name'  => 'Player',
                                    'width' => '%-30s'
                                  ),
          
                      'ping'   => array (
                                    'name'  => 'Ping',
                                    'width' => '%3d'
                                  ),
            
                      'frags'  => array (
                                    'name'  => 'Frags',
                                    'width' => '%3d'
                                  )
                    );                       
                                             
          $player         = array ();        
          $player_counter = 0;
                                             
          array_shift ($result_array);     

          for ($i = 0; $i < count ($result_array); $i++) {
            $player_counter          = $player_counter + 1;;
            $player_array            = explode (" ", $result_array[$i], 3);
                
            $player[$player_counter] = array (
                                         'player' => ereg_replace ("\^[0-9]{1}", "", str_replace ("\"", "", $player_array[2])),
                                         'ping'   => intval ($player_array[1]),
                                         'frags'  => intval ($player_array[0]),
                                       );
                
          };
        };
      };
      
      break;
  };
?>
