#!/usr/bin/perl -W
use strict;
#
# HLBook - Half-Life server booking system
# Copyright (C) 2001,2002,2003 Playway.net LLC
#
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

my $opt_cfgfile = "./hlbook.ini";
my $VERSION="0.99-openbeta1";

use lib "../libs";
use DBI;
use Time::localtime;
use Getopt::Long;
use Config::IniFiles;
use KKrcon;
use Net::Telnet;

Getopt::Long::Configure ("bundling");

#=========================================================#
# Globals + Defaults
#=========================================================#
my $cfg = new Config::IniFiles( -file => $opt_cfgfile );
my $dbcfg;
my $db_conn;
my $p_servers;

my $opt_run = 0;
my $opt_help = 0;
my $opt_version = 0;
my $opt_logfile = 0;
my $opt_logon = 0;
my $opt_quiet = 0;
my $opt_getserver = 0;
#=========================================================#
# DB Functions
#=========================================================#

sub db_connect
{
	my $uid = $cfg->val("db","user");
	my $pwd = $cfg->val("db","pass");
	my $host = $cfg->val("db","host");
	my $db   = $cfg->val("db","name");

	my $ref = DBI->connect("DBI:mysql:$db:$host",$uid,$pwd)
		or die ("\nCan't connect to database '$db' on '$host'\n" . "Server error: $DBI::errstr\n");

	putlog("db","Connected to MySQL server") if $opt_logon;
	return $ref;
}

sub query
{
	my ($query,$qid) = @_;
	my $result;

	if(!$qid) {
		$qid = "UNKNOWN";
	}

	if($query =~/^SELECT/)
	{
		$result = $db_conn->prepare($query)
			or die("Unable to prepare query:\n$query\n$DBI::errstr\n$qid");

		$result->execute() 
		or die("Unable to execute query:\n$query\n$DBI::errstr\n$qid");

		#putlog("db","Query $qid: $query") if $opt_logon;
	}
	else
	{
		$result = $db_conn->do($query);
	}

	return $result;
}
sub db_disconnect
{
	putlog("db","Disonnected from MySQL server") if $opt_logon;
	$db_conn->disconnect();
	return 1;
}

sub db_quote
{
	my $varQuote = shift;

	$varQuote =~ s/\\/\\\\/g;	# replace \ with \\
	$varQuote =~ s/'/\\'/g;		# replace ' with \'

	return $varQuote;
}

#=========================================================#
# Log functions
#=========================================================#
sub open_log
{
	my $logfile = shift;
	open(LOG,">>$opt_logfile") or return 0;

	return 1;
}

sub putlog
{
	my ($subsystem,$line) = @_;

	if(!$opt_quiet) {
		print "$subsystem\t$line\n";
	}

	if($opt_logfile) {
		my $tm = localtime(time());
		my $date = sprintf("%02d/%02d/%04d %02d:%02d:%02d",$tm->mon+1,$tm->mday,$tm->year+1900,$tm->hour,$tm->min,$tm->sec);
		print LOG "$date\t$subsystem\t$line\n";
	}

	return 1;
}

#=========================================================#
# Booking Utility Functions
#=========================================================#

sub disable_server
{
	my ($serverid,$error) = @_;
	my $p_srv =  $p_servers->{$serverid};

	my $result = query("UPDATE hlbook_Servers set disabled='1',error='" . $error . "' WHERE serverid='" . $serverid . "'");
	if($result) {
		$p_srv->{'disabled'} = 1;
		return 1;
	}

	return 0;
}

sub SetOptions
{
	$opt_logon = $cfg->val("general","logon");
	$opt_logfile = $cfg->val("general","logfile");
	$opt_quiet = $cfg->val("general","quiet");
}

sub load_servers
{
	my $hash_ref = ();
	my ($serverid,$ref);

	my $result = query("SELECT * FROM hlbook_Servers","LOAD SERVERS");
	while($ref = $result->fetchrow_hashref())
	{
		$serverid = $ref->{'serverid'};
		$hash_ref->{$serverid} = $ref;
	}

	$result->finish();
	return $hash_ref;
}

sub get_server
{
	my ($serverid) = shift;	
	my $p_srv =  $p_servers->{$serverid};

	return $p_srv;
}

sub get_server_element
{
	my ($serverid,$element) = @_;
	return $p_servers->{$serverid}{$element};
}

sub set_status
{
	my ($bookid,$status) = @_;

	if ($status !~/^([CSPRE])$/) {
		return 0;
	}

	my $result = query("UPDATE hlbook_Bookings SET status='" . $status . "' WHERE matchid='" . $bookid . "'");

	if($result > 0) {
		return 1;
	}

	return 0;
}

sub load_Books
{
	my($query,$qid) = @_;
	my $p_hash = ();
	my $ref;
	my $bookid;

	my $result = query($query,$qid);

	if(!$result) {
		return 0;
	}

	while ($ref = $result->fetchrow_hashref ())
	{
		$bookid = $ref->{'matchid'};
		$p_hash->{$bookid} = $ref;
	}

	$result->finish();

	return $p_hash;
}

sub update_rcon
{
	my ($serverid,$rpass) = @_;
	my $p_srv = get_server($serverid);

	#put a thing to chop off all chars past 16th
	$p_srv->{'rcon'} = $rpass;

	my $result = query("UPDATE hlbook_Servers SET rcon='" . $rpass . "' WHERE serverid='" . $serverid . "'","UPDATE_RCON");

	if($result) {
		return 1;
	}	
	return 0;
}

sub get_valid_map_by_mod
{
	my $modid = shift;
	my $result = query("SELECT name FROM hlbook_Maps WHERE modid='" . $modid . "' LIMIT 1");

	if(!$result) {
		return 0;
	}

	my ($map) = $result->fetchrow_array();
	return $map;
}

sub get_valid_config_by_mod
{
	my $modid = shift;
	my $result = query("SELECT config FROM hlbook_Configs WHERE modid='" . $modid . "' LIMIT 1");

	if(!$result) {
		return 0;
	}

	my ($config) = $result->fetchrow_array();
	return $config;
}

sub make_random_string
{
	my $size = shift;
	my @chars = ("A" .. "Z","a" .. "z",0 .. 9);
	my $string = join("",@chars[ map { rand @chars } ( 1 .. $size ) ]);

	return $string;
}

sub rcon_connect
{
	my ($serverid,$pwd,$ip,$port,$type) = @_;
	my $p_srv = get_server($serverid);
	my ($struct_rcon,$p_rcon);

	if(!$type) {
		$type =  "new";
	}

	$p_rcon = new KKrcon("Password"=>$pwd,"Host"=>$ip,"Port"=>$port,"Type"=>$type);
	$struct_rcon = [$serverid,$p_rcon];

	putlog("rcon","Created rcon session for: $ip:$port type: $type") if $opt_logon;
	return $struct_rcon;
}

sub rcon_exec
{
	my ($struct_rcon,$cmd) = @_;

	my $serverid = $struct_rcon->[0];
	my $p_rcon = $struct_rcon->[1];

	my $p_srv = get_server($serverid);
	my $host = $p_srv->{'ip'} . ":" . $p_srv->{'port'};

	my $result = $p_rcon->execute($cmd);
	my $error = $p_rcon->error();

	putlog("rcon","executing: $cmd on $host") if $opt_logon;
	#putlog("console","raw result from $host: $result") if $opt_logon;

	if($error eq "Bad Password")
	{
		my $recovery = rcon_recover($struct_rcon,$cmd);

		if($recovery) {
			return 1;
		}
		else {
			return 0;
		}	
	}
	elsif($error eq "Rcon timeout")
	{
		putlog("rcon","Connection to " . $p_srv->{'ip'} . ":" . $p_srv->{'port'} . " has timed out") if $opt_logon;
		return 0;
	}
	elsif($error eq "No challenge response")
	{
		putlog("rcon","Connection to " . $p_srv->{'ip'} . ":" . $p_srv->{'port'} . " has replied with challenge error") if $opt_logon;
		return 0;
	}

	return 1;
}

sub rcon_recover
{
	my ($struct_rcon,$cmd) = @_;

	my $serverid = $struct_rcon->[0];
	my $p_rcon = $struct_rcon->[1];
	my $p_srv = get_server($serverid);
	my $host = $p_srv->{'ip'} . ":" . $p_srv->{'port'};

	my $new_rcon = new KKrcon("Password"=>$p_srv->{'defrcon'},"Host"=>$p_srv->{'ip'},"Port"=>$p_srv->{'port'},"Type"=>$p_srv->{'protocol'});

	putlog("rcon","Connection to $host has returned bad password") if $opt_logon;
	putlog("rcon","Attempting to recover password for $host") if $opt_logon;

	my $result = $new_rcon->execute($cmd);
	my $error = $new_rcon->error();

	if($error eq "Bad Password")
	{
		disable_server($serverid,"Bad Rcon Password");
		putlog("rcon","Password recovery failed for $host") if $opt_logon;
		putlog("rcon","Disabling $host") if $opt_logon;
		return 0
	}

	putlog("rcon","Password was recovered for $host") if $opt_logon;
	putlog("rcon","Executed $cmd on $host") if $opt_logon;

	#reset struct rcon pointer
	$struct_rcon->[1] = $new_rcon;

	return 1;
}

#=========================================================#
# Booking Warning Functions
#=========================================================#
sub send_log_start_message
{
	my ($matchid,$serverid) = @_;
	my $t = new Net::Telnet (Errmode=>"return",Binmode=>1,Telnetmode=>0,Timeout => 3);

	if(!$t->open(Host=>$cfg->val("cmdsocket","ip"),Port=>$cfg->val("cmdsocket","port")))
	{
		putlog("hlogd","Could not start log capturing for match #$matchid on server #$serverid");
		return 0;
	}

	$t->print("start " . $cfg->val("cmdsocket","adminpass") . " $matchid $serverid");
	$t->waitfor('/#->/');
	$t->print("exit");
	$t->close();

	putlog("hlogd","started log capturing for match #$matchid on server #$serverid");
	return 1;
}

sub send_log_stop_message
{
	my ($matchid) = @_;

	my $t = new Net::Telnet (Errmode=>"return",Binmode=>1,Telnetmode=>0,Timeout => 3);

	if(!$t->open(Host=>$cfg->val("cmdsocket","ip"),Port=>$cfg->val("cmdsocket","port")))
	{
		putlog("hlogd","could not stop log capturing for match #$matchid");
		return 0;
	}

	$t->print("stop " . $cfg->val("cmdsocket","adminpass") . " $matchid");
	$t->waitfor('/#->/');
	$t->print("exit");
	$t->close();	

	putlog("hlogd","stopped log capturing for match #$matchid");
	return 1;
}

sub server_pulse
{
	my $books = load_Books("SELECT * FROM hlbook_Bookings WHERE status='P'","PULSE QUERY");

	my $bhash;
	my $b;
	my $rcon;
	my $p_srv;

	foreach $bhash (keys(%{$books}))
	{
		$b					= $books->{$bhash};
		$p_srv			= get_server($b->{'serverid'});
		$rcon = rcon_connect($b->{'serverid'},$p_srv->{'rcon'},$p_srv->{'ip'},$p_srv->{'port'},$p_srv->{'protocol'});
		rcon_exec($rcon,"sv_password " . $b->{'svpasswd'});
		rcon_exec($rcon,"logaddress " . $cfg->val("logsocket","ip") . " " . $cfg->val("logsocket","port"));
	}
}

sub warn_booking_close
{
	my $warntime = time() - $cfg->val("warnings","start_at") * 60;
	my $books = load_Books("SELECT * FROM hlbook_Bookings WHERE (initdate + timeblock) < $warntime && status = 'P'","WARNING QUERY");
	my ($b,$bhash);
	my ($timeleft,$nextdate);
	my $rcon;
	my $p_srv;
	my $host;

	if(!$books) {
		return 1;
	}

	foreach $bhash (keys(%{$books}))
	{
		$b					= $books->{$bhash};
		$nextdate 	= $b->{'initdate'} + $b->{'timeblock'};
		$p_srv			= get_server($b->{'serverid'});

		if(!$p_srv) {
			putlog("booking","warn booking failed to find settings for serverid " . $b->{'serverid'}) if $opt_logon;
			next;
		}

		#check to see if the next match belongs to the same users,if not, warn the dudes that the match is going to close
		if(!is_same_owner($nextdate,$b->{'serverid'},$b->{'userid'}))
		{
			$timeleft = sprintf("%0.2f",($nextdate - time()) / 60);
			$host				= $p_srv->{'ip'} . ":" . $p_srv->{'port'};

			$rcon = rcon_connect($b->{'serverid'},$p_srv->{'rcon'},$p_srv->{'ip'},$p_srv->{'port'},$p_srv->{'protocol'});
			if(rcon_exec($rcon,"say This booking will close in " . $timeleft . " minutes.")) {
				putlog("booking","Warning players on $host that the match # " . $b->{'matchid'} . "is going to close in $timeleft minutes") if $opt_logon;		
			}
			else
			{
				putlog("error","Unable to execute warning for match " . $b->{'matchid'}) if $opt_logon;
				set_status($b->{'matchid'},"E");
				disable_server($b->{'serverid'},"Bad Rcon Password");

			}
		}
	}

	return 1;	
}

sub is_same_owner
{
	my ($date,$serverid,$userid) = @_;
	my $result = query("SELECT count(*) FROM hlbook_Bookings WHERE userid='" . $userid. "' && serverid='" . $serverid . "' && initdate='" . $date . "'","FUNC_IS_SAME_OWNER");
	my ($count) = $result->fetchrow_array();

	$result->finish();

	return $count;
}

sub next_slot_taken
{
	my ($date,$serverid) = @_;
	my $result = query("SELECT count(*) FROM hlbook_Bookings WHERE serverid='" . $serverid . "' && initdate='" . $date . "'","FUNC_NEXT_SLOT_TAKEN");
	my ($count) = $result->fetchrow_array();

	return $count;
}


#=========================================================#
# Booking Functions
#=========================================================#

sub do_bookings
{
	my $time = time();
	my $books = load_Books("SELECT hlbook_Bookings.* FROM hlbook_Bookings,hlbook_Servers WHERE 
											hlbook_Bookings.initdate < $time && (hlbook_Bookings.status='R' || hlbook_Bookings.status='S')
											&& hlbook_Bookings.serverid=hlbook_Servers.serverid && hlbook_Servers.disabled=0","DO_BOOKINGS");
	my ($b,$bhash);
	my $rcon;
	my $lastdate;
	my $p_srv;
	my $host;

	foreach $bhash (keys(%{$books}))
	{
		$b					= $books->{$bhash};
		$lastdate 				= $b->{'initdate'} - $b->{'timeblock'};
		$p_srv					= get_server($b->{'serverid'});

		if(!$p_srv) {
			putlog("booking","exec booking failed to find settings for serverid " . $b->{'serverid'}) if $opt_logon;
			next;
		}

		#set a host shortcut
		$host				= $p_srv->{'ip'} . ":" . $p_srv->{'port'};

		#Fire up rcon so we can change the rcon password
		$rcon = rcon_connect($b->{'serverid'},$p_srv->{'rcon'},$p_srv->{'ip'},$p_srv->{'port'},$p_srv->{'protocol'});

		#if its the same dude, we don't want to kick him, 
		#we'll let him know we're rolling over
		#but,say the the match died out, then they booked, we have to make the
		#changes, so, return false here if the bookdate of the match in
		#question is older than the iniitdate

		if($b->{'posted'} < $b->{'initdate'} && is_same_owner($lastdate,$b->{'serverid'},$b->{'userid'}))
		{
			rcon_exec($rcon,"say rolling over to next booking.");
			rcon_exec($rcon,"say server password and rcon password will stay the same.");
		}
		else
		{
			# else, we need to change the rcon password for the upcoming match
			if(rcon_exec($rcon,"rcon_password \"" . $b->{'rcon'} . "\"")) {
				update_rcon($b->{'serverid'},$b->{'rcon'});
			}
			else {

				#if we can't change tcon, log it, then set status to "E" (error) and return 0;
				putlog("error","Unable to change rcon pass on $host") if $opt_logon;
				set_status($b->{'matchid'},"E");
				disable_server($b->{'serverid'},"Bad Rcon Password");

				next;
			}

			#get new rcon pointer
			$rcon = rcon_connect($b->{'serverid'},$p_srv->{'rcon'},$p_srv->{'ip'},$p_srv->{'port'},$p_srv->{'protocol'});

			#execute the match settingings
			rcon_exec($rcon,"sv_password \"" . $b->{'svpasswd'} . "\"");
			rcon_exec($rcon,"hostname \"" . $b->{'servername'} . "\"");

			#set the config
			if($b->{'config'}) {
				rcon_exec($rcon,"servercfgfile " . $b->{'config'});
			}

			#if some how there is not map, guess one
			if(!$b->{'map'}) { 
				$b->{'map'} = get_valid_map_by_mod($b->{'modid'});
			}

			rcon_exec($rcon,"map \"" . $b->{'map'} . "\"");
			#now start logging
		}

		send_log_start_message($b->{'matchid'},$b->{'serverid'});
		putlog("booking","Setting book id " . $b->{'matchid'} . " on $host to InProgress");
		set_status($b->{'matchid'},"P");
	}
}

sub close_bookings
{
	my $time = time();
	my $books = load_Books("SELECT * FROM hlbook_Bookings WHERE (initdate + timeblock) < $time && status!='C' && status!='E'","CLOSE_BOOKINGS");
	my ($bhash,$b);
	my $rcon;
	my $nextdate;
	my $mode;
	my $p_srv;
	my $host;

	foreach $bhash (keys(%{$books}))
	{
		$b 					= $books->{$bhash};
		$nextdate 	= $b->{'initdate'} + $b->{'timeblock'};
		$p_srv			= get_server($b->{'serverid'});
		$host				= $p_srv->{'ip'} . ":" . $p_srv->{'port'};

		if($p_srv == 0) {
			putlog("booking","match #". $b->{'matchid'} . " was booked on a non existant server" . $b->{'serverid'} . " Host: $host") if $opt_logon;
			set_status($b->{'matchid'},"E");
			next;
		}

		if($p_srv->{'disabled'} > 0) {
			putlog("booking","Closing match " . $b->{'matchid'} . " on $host but server is disabled") if $opt_logon;
			set_status($b->{'matchid'},"C");
			send_log_stop_message($b->{'matchid'});
			next;
		}

		if(is_same_owner($nextdate,$b->{'serverid'},$b->{'userid'})) {
			set_status($b->{'matchid'},"C");
			putlog("booking","Closing match " . $b->{'matchid'} . " on $host (rolling over)") if $opt_logon;	
			send_log_stop_message($b->{'matchid'});

		}
		elsif(next_slot_taken($nextdate,$b->{'serverid'})) {
			set_status($b->{'matchid'},"C");
			putlog("booking","Closeing match " . $b->{'matchid'} . " on $host (new user)") if $opt_logon;
			send_log_stop_message($b->{'matchid'});
		}
		else
		{
			#if this fails then that means the admin did not setup the default mode.
			#Thats ok.
			send_log_stop_message($b->{'matchid'});

			$mode = get_mode_by_time($b->{'serverid'},time());
			if(!$mode)
			{
				putlog("booking","Error: Mode not found for server: $host") if $opt_logon;	
				putlog("booking","Generating random values for sv_password and rcon for $host") if $opt_logon;	

				$mode->{'rcon'} = make_random_string(16);
				$mode->{'svpasswd'} = make_random_string(16);
			}

			#create this to exec mode settings
			$rcon = rcon_connect($b->{'serverid'},$p_srv->{'rcon'},$p_srv->{'ip'},$p_srv->{'port'},$p_srv->{'protocol'});

			#if we fail to exec the rcon we'll try to go to default, if that fails
			# then we'll set the status to E and then go on to the next match
			if(rcon_exec($rcon,"rcon_password \"" . $mode->{'rcon'} . "\"")) {
				update_rcon($b->{'serverid'},$mode->{'rcon'});
			}
			else {
				#if we can't change rcon, log it, then set status to "E" (error) and go to next
				# the server will automatically be disabled by recover_rcon()
				putlog("error","Unable to change rcon pass on $host while closing matchid " . $b->{'matchid'}) if $opt_logon;
				set_status($b->{'matchid'},"E");
				disable_server($b->{'serverid'},"Bad Rcon Password on Booking Close");
				next;
			}

			$rcon = rcon_connect($b->{'serverid'},$p_srv->{'rcon'},$p_srv->{'ip'},$p_srv->{'port'},$p_srv->{'protocol'});

			rcon_exec($rcon,"hostname \"" . $cfg->val("general","site") . " bookable server\"");
			rcon_exec($rcon,"sv_password \"" . $mode->{'svpasswd'} . "\"");

			#perma set the config
			if($mode->{'config'}) {
				rcon_exec($rcon,"servercfgfile " . $mode->{'config'});
				rcon_exec($rcon,"exec hlbook.cfg");
			}

			if(!$mode->{'map'}) { 
				$mode->{'map'} = get_valid_map_by_mod($b->{'modid'});
			}

			rcon_exec($rcon,"map \"" . $mode->{'map'} . "\"");
			set_status($b->{'matchid'},"C");	
		}

		undef($mode);
	}
}

#=========================================================#
# Mode Functions
#=========================================================#

sub get_mode_by_time
{
	my ($serverid,$date) = @_;
	my $tm = localtime($date);
	my $stamp = sprintf("%02d:%02d:%02d",$tm->hour,$tm->min,$tm->sec);

	my $result = query("SELECT * FROM hlbook_ServerModes WHERE serverid='" . $serverid . "' && start<='$stamp' ORDER BY start desc LIMIT 1");

	if(!$result) {
		return 0;
	}

	my $ref = $result->fetchrow_hashref();
	$result->finish();
	return $ref;
}

#=========================================================#
# Main
#=========================================================#

my $usage = <<EOT
\nUsage hlbook.pl [OPTIONS]

Collects clan match settings from SQL server which are used to configure 
one or more servers remotely using RCON protocol.

			-h, --help					display this screen.
			-v, --version				display verion information and quit. 
			-r, --run						executing bookings engine
			-l, --logon					turn on logging
			-f, --logfile				specify log file
			-q, --quiet					do not log output to STDOUT

http://www.playway.net
EOT
;

SetOptions();
GetOptions(
	"run|r"		=> \$opt_run,
	"help|h"	=> \$opt_help,
	"version|v"	=> \$opt_version,
	"logon|l"=> $opt_logon,
	"logfile|f=s"=> \$opt_logfile,
	"quiet|q"=>\$opt_quiet

) or die($usage);

if($opt_logon)
{
	if(!open_log($opt_logfile)) {
		$opt_logon = 0;
	}

	putlog("debug","HLBook started") if $opt_logon;
}

if($opt_version)
{
	print "HL-Bookings $VERSION\n"
		. "Real-time server booking system for the Half-Life+Quake dedicated servers\n"
		. "Copyright (C) 2001-2002 Playway.net LLC, Licensed under the GPL\n";
	exit(0);
}

if($opt_help)
{
	print $usage;
	exit(0);
}

if($opt_run)
{
	$db_conn = db_connect();
	$p_servers = load_servers();

	warn_booking_close();
	close_bookings();

	if($cfg->val("general","svpulse")) {
		server_pulse();
	}

	do_bookings();	
	db_disconnect();
}

putlog("debug","HLBook finished") if $opt_logon;
exit(0);
