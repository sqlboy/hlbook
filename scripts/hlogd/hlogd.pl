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

my $opt_configfile = "hlogd.ini";
my $VERSION = "0.99-openbeta1";

use lib "../libs";
use IO::Socket::INET;
use IO::Select;
use Config::IniFiles;
use KKrcon;
use GameServer;
use CmdParserX;
use Fcntl;
use POSIX;
use DBI;
use FileHandle;
use Booking;
use POSIX;

#=========================================================#
# Globals + Defaults
#=========================================================#
$SIG{INT} = $SIG{TERM} = $SIG{HUP} =  \&terminate;
my $cfg = new Config::IniFiles( -file => $opt_configfile );
my $opt_quiet = $cfg->val("general","quiet");
my $cpm = new CmdParserX();
my $db_conn;
my %SERVER;
my %BOOKING;
my %logbuffer;
my %inbuffer;
my %outbuffer;
my %authed;
my @filters;
my $LOG;
#=========================================================#
# Fork stuff
#=========================================================#

if($cfg->val("general","daemon") == 1) {

	print "Staring Hlogd $VERSION as daemon\n";
	my $pid = fork;
	exit if $pid;
	die "Count not fork!: $!" unless defined($pid);

	POSIX::setsid()
	or die("Can't start a new session $!");

	close(STDIN);
	close(STDOUT);
	close(STDERR);
	$opt_quiet = 1;
}

#=========================================================#
# Shprockets
#=========================================================#
my $updselect = IO::Select->new();
my $tcpselect = IO::Select->new();

my $logsock = IO::Socket::INET->new(
		Proto=>"udp",
		LocalAddr=>$cfg->val("logsocket","ip"),
		LocalPort=>$cfg->val("logsocket","port"),
		Resuse=>1
	) or die ("\nCan't setup Log socket $!\n");

my $cmdsock = IO::Socket::INET->new(
		Listen=>1024,
		Proto=>"tcp",
		LocalAddr=>$cfg->val("cmdsocket","ip"),
		LocalPort=>$cfg->val("cmdsocket","port"),
		Reuse=>1
	) or die ("\nCan't setup command socket $!\n");

nonblock($logsock);
nonblock($cmdsock);

$updselect->add($logsock);
$tcpselect->add($cmdsock);

#=========================================================#
# Main
#=========================================================#
if($cfg->val("general","logon")) {
	open_log_file();
}

register_commands();
db_connect();
load_servers();
load_bookings();
load_filters();

wlog("HLogd $VERSION started");

while(1)
{
	read_udpsock();
	command_socket();

	#why?
	select (undef,undef,undef,0.001);
}

sub register_commands
{
	#User Commands
	#user can oly request logs to himself
	#udp log request user style, specify's the server he wants and the port to receive the logs on
	$cpm->new_command(Cmd=>"getudp#[0-9\.]+:[0-9]+#[0-9]{1,5}",Help=>"Request a server server log stream via UDP. Arg1i is the servers ip:port,arg2 destination port.",Callback=>sub { main::callback_open_user_log_mirror(); });

	$cpm->new_command(Cmd=>"list#servers|bookings",Help=>"Shows a list.  Required args: servers",Callback=>sub { main::callback_show(); });
	$cpm->new_command(Cmd=>"exit",Help=>"close connection",Callback=>sub { main::callback_exit(); });	

	#Admin Commands
	#log to disk commands
	$cpm->new_command(Cmd=>"disklog#[A-Za-z0-9]+#[0-9\.:]+",Help=>"Log server logs to path.  Arg1 path, Arg 2 serverid",Password=>$cfg->val("cmdsocket","adminpass"),Callback=>sub { main::callback_open_match_log(); });
	$cpm->new_command(Cmd=>"nodisklog#[A-Za-z0-9]+#[0-9\.:]+",Help=>"Shut off server disk log. Arg1 path, Arg 2 serverid",Password=>$cfg->val("cmdsocket","adminpass"),Callback=>sub { main::callback_close_match_log(); });
	#log to telnet console
	$cpm->new_command(Cmd=>"log#[0-9\.:]+",Help=>"Mirror logs to console. Arg1 is server ip:port",Password=>$cfg->val("cmdsocket","adminpass"),Callback=>sub { main::callback_getlogs(); });
	$cpm->new_command(Cmd=>"nolog#[0-9\.:]+",Help=>"Shut off server logs to console.  Arg1 is server id",Password=>$cfg->val("cmdsocket","adminpass"),Callback=>sub { main::callback_nologs(); });

	# for the hlbook process
	#start a booking.
	#passwd matchid serverip:port 
	#stop matchid
	#
	# as soon as a match is started, the log file is created and certain events
	# are logged
	$cpm->new_command(Cmd=>"start#[0-9]+#[0-9]+",Help=>"Start a booked match.  Args matchid and serverid",Password=>$cfg->val("cmdsocket","adminpass"),Callback=>sub { main::callback_start_booking(); });
	$cpm->new_command(Cmd=>"stop#[0-9]+",Help=>"Stop a booked match",Password=>$cfg->val("cmdsocket","adminpass"),Callback=>sub { main::callback_stop_booking(); });

	#udp log admin style (choose destination)
	$cpm->new_command(Cmd=>"udplog#[0-9\.]+:[0-9]{1,5}#[0-9\.]+:[0-9]{1,5}",Password=>$cfg->val("cmdsocket","adminpass"),Callback=>sub { main::callback_open_udp_log_mirror(); });

	#admin show
	$cpm->new_command(Cmd=>"show#servers|conns|udp|bookings|lh",Help=>"Shows a list of servers,bookings,conns,active udp mirrors, or open log files",Password=>$cfg->val("cmdsocket","adminpass"),Callback=>sub { main::callback_show(); });

	#help
	$cpm->new_command("Cmd"=>"help","Help"=>"Show command menu",Callback=>sub { main::callback_help(); });

}

#=========================================================#
# DB Functions
#=========================================================#
sub db_connect
{
	my $uid = $cfg->val("db","user");
	my $pwd = $cfg->val("db","pass");
	my $host = $cfg->val("db","host");
	my $db   = $cfg->val("db","name");

	$db_conn = DBI->connect("DBI:mysql:$db:$host",$uid,$pwd)
		or die ("\nCan't connect to database '$db' on '$host'\n" . "Server error: $DBI::errstr\n");
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
	}
	else
	{
		$result = $db_conn->do($query);
	}

	return $result;
}
sub db_disconnect
{
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
# Socket Functions
#=========================================================#
sub read_udpsock
{
	my $client;
	my $rv;
	my $data;
	my $logline;
	my $sindex;
	my $ip;
	my $port;

	foreach $client ($updselect->can_read(0))
	{
		$data = '';
		$rv = $client->recv($data,1024,0);
		$sindex = $client->peerhost() . ":" . $client->peerport();

		if(!exists($SERVER{$sindex})) {
			print "Server does not exist!: $sindex\n";
			next;
		}

		$logbuffer{$client} .= $data;

		while ($logbuffer{$client} =~s/^(.*)\n//)
		{
			$logline = $1;
			$logline =~s/[\xFF\r\n\0]+//g;		#strip out strangeness
			$logline =~s/^.{4}//;							#strip out "log "

			#before we filter, we should send it to the parsers so
			#we can get the rcon stuff out.
			if(exists($BOOKING{"$sindex"})) {
				$logline =~s/^.{25}//;					#strip off timestamp
				log_handle($client,$logline);
			}

			log_filter(\$logline);

			#if there is anything left
			if($logline)
			{
				#send log line into the server object
				$SERVER{$sindex}->addline($logline . "\n",\%outbuffer);
			}
		}
	}

	return 1;
}

sub command_socket
{
	my $client;
	my $newclient;
	my $data;
	my $rv;

	foreach $client ($tcpselect->can_read(0))
	{
		if($client == $cmdsock)
		{
			$newclient = $cmdsock->accept();
			nonblock($newclient);
			$tcpselect->add($newclient);
			$outbuffer{$newclient} = message("success",$cfg->val("general","motd"));
			$inbuffer{$newclient} = undef;

			wlog("received connection from " . $newclient->peerhost());

		}
		else
		{
			$data = '';
			$rv = $client->recv($data,128,0);

			unless (defined($rv) && length($data))
			{
				wlog($client->peerhost() . " disconnected");

				delete $inbuffer{$client};
				delete $outbuffer{$client};
				$tcpselect->remove($client);
				close($client);
				next;
			}

			$inbuffer{$client} .= $data;

			while ($inbuffer{$client} && $inbuffer{$client} =~s/(.*)\n//)
			{
				wlog($client->peerhost() . " sent command: $1");
				cmd_handle($client,$1);
			}	
		}
	}

	foreach $client ($tcpselect->can_write(0))
	{
		next unless exists $outbuffer{$client};
		$outbuffer{$client} .= "#->";

		wlog("Out: " . $client->peerhost() . ":" . $outbuffer{$client});

		$rv = $client->send($outbuffer{$client},0);

		unless(defined $rv) {
			next;
		}

		if($rv == length($outbuffer{$client}) || $! == POSIX::EWOULDBLOCK)
		{
			substr($outbuffer{$client},0,$rv) = '';
			delete $outbuffer{$client} unless length $outbuffer{$client};
		}
		else
		{
			delete $inbuffer{$client};
			delete $outbuffer{$client};
			$tcpselect->remove($client);
			close($client);
			next;
		}
	}
}

sub nonblock
{
	my $socket = shift;
	my $flags;

	$flags = fcntl($socket,F_GETFL,0)
		or die ("Can't get flags for socket: $!\n");

	fcntl($socket,F_SETFL,$flags | O_NONBLOCK)
		or die ("Can't make socket nonblocking: $!\n");
}

sub cmd_handle
{
	my ($client,$cmd) = @_;

	if(!$cpm->execute($cmd,[$client]))
	{
		$outbuffer{$client} = $cpm->error() . "\n";
	}
}

sub log_handle
{
	# Some parsing is here even though it doesn't do anything yet

	my ($client,$logline) = @_;
	my $ip = $client->peerhost();
	my $port = $client->peerport();
	my ($team,$player,$event1,$noun1,$event2,$noun2,$properties);

	if ($logline =~ /^\/\//)
	{
    # matches comments
    # you should skip to the next log line
	}
	elsif ($logline =~ /^"([^"]+)" ([^"\(]+) "([^"]+)" ([^"\(]+) "([^"]+)"(.*)$/)
	{
		#print "Matched events: 057,058,059,066\n";
		$team = "";
		$player = $1; # parse out name, uid and team later
		$event1 = $2; # event type - "killed", "attacked", etc.
		$noun1 = $3; # victim name/objective code, etc.
		$event2 = $4; # "with", etc.
		$noun2 = $5; # weapon/victim name, etc.
		$properties = $6; # parse out keys and values later
	}
	elsif ($logline =~ /^"([^"]+)" ([^"\(]+) "([^"]+)"(.*)$/)
	{
		#print "Matched Events: 050,053,054,055,056,060,063a,063b,068,069\n";
		$team = "";
		$player = $1;
		$event1 = $2;
		$noun1 = $3; # weapon/team code/objective code, etc.
		$event2 = "";
		$noun2 = "";
		$properties = $4;

		if($event1 eq "connected, address") {
			if($BOOKING{"$ip:$port"}->add_player($player,$noun1)) {
				wlog("added player $player to server $ip:$port");
			}
		}
	}
	elsif ($logline =~ /^"([^"]+)" ([^\(]+)(.*)$/)
	{
		#print "Matched Events: 051,052\n";
		$team = "";
		$player = $1;
		$event1 = $2;
		$noun1 = "";
		$event2 = "";
		$noun2 = "";
		$properties = $3;
	}
	elsif ($logline =~ /^Team "([^"]+)" ([^"\(]+) "([^"]+)"(.*)$/)
	{
		#print "Matched Events 061,064\n";
		$team = $1; # Team code
		$player = 0;
		$event1 = $2;
		$noun1 = $3;
		$event2 = "";
		$noun2 = "";
		$properties = $4;
	}
	elsif ($logline =~ /^([^"\(]+) "([^"]+)"(.*)$/)
	{
		#print "Matched Events: 062,003a,003b,005,006\n"; 
		$team = "";
		$player = 0;
		$event1 = $1;
		$noun1 = $2;
		$event2 = "";
		$noun2 = "";
		$properties = $3;

		if($properties=~/rcon_password\s(.*)\"\sfrom\s\"(.*)\"$/) {
			if($BOOKING{"$ip:$port"}->update_rcon($1)) {
				wlog("server rcon password changed by $2");
			}
		}
	}
}
#=========================================================#
# Utility Functions
#=========================================================#

sub repeat_char
{
	my ($char,$num) = @_;
	my $buffer;
	my $i;

	for($i=0;$i<=$num;$i++)
	{
		$buffer .= $char;
	}

	return $buffer;
}

sub open_log_file
{
	if($cfg->val("general","logon") < 1) {
		return 1;
	}

	my $filename = $cfg->val("general","logfile");
	my $fh = new FileHandle ">>$filename";
	if(defined($fh)) {
		$fh->autoflush(1);
		$LOG = $fh;
		return 1;
	}
	return 0;
}

sub wlog
{
	if($cfg->val("general","logon") < 1) { return 1;}

	my ($message) = shift;
	my $date = localtime(time());

	if($cfg->val("general","logfile")) {
		print $LOG $date . "\t\t" . $message . "\n";
	}

	if($opt_quiet < 1) {
		print $message . "\n";
	}
}

sub message
{
	my ($type,$message) = @_;
	my $xml = "<message type=\"" . $type . "\" value=\"" . $message . "\"/>\n";

	return $xml;
}

# array getPlayerInfo (string player)
sub getPlayerInfo
{
	my $player = shift;
	#name,conn id,wonid,team
	if ($player =~ /^(.+)<(\d+)><(\d+)><(.*)>$/)
	{
		return ($1, $2, $3, $4);
	}
	else
	{
		return ("", 0, 0, "");
	}
}

# hash getProperties (string propstring)
sub getProperties
{
	my $propstring = shift;
	my %properties;

	while ($propstring =~ s/^\s*\((\S+)(?: "([^"]+)")?\)//)
	{
		if ($2)
		{
			$properties{$1} = $2;
		}
		else
		{
			$properties{$1} = 1; # for boolean properties per Note (4)
		}
	}

	return %properties;
}

sub load_filters
{
	my $file = $cfg->val("logsocket","filterfile");
	open(FILTERS,$file) || return 0;

	while(<FILTERS>)
	{
		chomp;
		wlog("loading log filter: $_");
		push(@filters,$_);
	}

	close(FILTERS);
	return 1;
}

sub log_filter
{
	my $log_ref = shift;

	foreach my $filter (@filters)
	{
		if(${$log_ref} =~/$filter/)
		{
			${$log_ref} = 0;
		}
	}

	return 1;
}

sub terminate
{
	wlog("Shutting down hlogd");
	db_disconnect();
	exit(0);
}

#=========================================================#
# Server Functions
#=========================================================#
sub load_servers
{
	my $result = query("SELECT ip,port FROM hlbook_Servers","LOAD SERVERS");
	while(my($ip,$port) = $result->fetchrow_array()) {
		$SERVER{"$ip:$port"} = new GameServer(Host=>$ip,Port=>$port);
	}

	$result->finish();
	return 1;
}

#
#uif hlogd crashes we should pick up on the already in progress matches.
#
sub load_bookings
{
	my $result = query("SELECT 
			hlbook_Servers.ip,hlbook_Servers.port,
			hlbook_Bookings.userid,hlbook_Bookings.matchid,hlbook_Bookings.serverid,hlbook_Bookings.servername
			FROM hlbook_Servers,hlbook_Bookings
			WHERE hlbook_Bookings.status='P' 
				&& hlbook_Bookings.logopen='1'
					&& hlbook_Bookings.serverid=hlbook_Servers.serverid");

		while(my($ip,$port,$userid,$matchid,$serverid,$servername) = $result->fetchrow_array()) {

			$BOOKING{"$ip:$port"} = new Booking(
					MatchId=>$matchid,
					ServerId=>$serverid,
					UserId=>$userid,
					Hostname=>$servername,
					IP=>$ip,
					Port=>$port
				);

			if(!$SERVER{"$ip:$port"}->start_disk_log($cfg->val("logsocket","tmplogpath") . "/match_" . $matchid . ".log")) {
				wlog("error creating tmp log file for matchid $matchid on host $ip $port");
				return 0;
			}	

			wlog("init existing match #$matchid on $ip $port");	
}

$result->finish();
return 1;
}

sub package_log_file
{
	my ($srcfile,$destfile) = @_;

	my @zip = (
			$cfg->val("general","zip"),
			"-9",
			"-j",
			"-m",
			$cfg->val("logsocket","permlogpath") . "/" . $destfile,
			$cfg->val("logsocket","tmplogpath") . "/" . $srcfile
		);


	my $rc = 0xffff & system(@zip);

	if($rc != 0) {
		print "failed to package log file: $srcfile\n";
		return 0;
	}

	print "packaged log file: $destfile\n";
	return 1;
}

sub update_logopen
{
	my ($matchid,$value) = @_;
	my $result = query("UPDATE hlbook_Bookings SET logopen='" . $value . "' WHERE matchid='" . $matchid . "'");

	if($result == 1) {
		return 1;
	}

	return 0;
}

#=========================================================#
# Callbacks
#=========================================================#
sub callback_start_booking
{
	my ($client,$matchid,$serverid) = $cpm->return_args();

	#verify the booking exists
	my $result = query("SELECT 
					hlbook_Servers.ip,hlbook_Servers.port,
					hlbook_Bookings.servername
					FROM hlbook_Servers,hlbook_Bookings
					WHERE hlbook_Bookings.matchid='" . $matchid . "' 
						&& hlbook_Bookings.serverid='" . $serverid . "'
							&& hlbook_Bookings.logopen='0'
								&& hlbook_Bookings.status='P'
			&& hlbook_Bookings.serverid=hlbook_Servers.serverid LIMIT 1");

	my($ip,$port,$servername) = $result->fetchrow_array();

	if($ip) {

		if(exists($BOOKING{"$ip:$port"})) {
			$outbuffer{$client}.= message("error","could not create match #$matchid, already exists");
			return 0;
		}

		$BOOKING{"$ip:$port"} = new Booking(
				MatchId=>$matchid,
				ServerId=>$serverid,
				Hostname=>$servername,
				IP=>$ip,
				Port=>$port
			);

		if(!$SERVER{"$ip:$port"}->start_disk_log($cfg->val("logsocket","tmplogpath") . "/match_" . $matchid . ".log")) {
			$outbuffer{$client} .= message("error","could not start disk log for $matchid");
			return 0;
		}

		#hopefully, this does not fail
		if(!update_logopen($matchid,1)) {
			$outbuffer{$client} .= message("error","the log was started but I could not update logopen");
			return 0
		}

	}
	else
	{
		$outbuffer{$client} .= message("error","could not creat disk log for $matchid, query failed");
		return 0
	}

	$outbuffer{$client}.= message("success","started log session for matchid #$matchid");
	return 1;
}

sub callback_stop_booking
{
	my ($client,$matchid) = $cpm->return_args();

	#verify the booking exists
	my $result = query("SELECT
			hlbook_Servers.ip,hlbook_Servers.port
			FROM hlbook_Servers,hlbook_Bookings
			WHERE hlbook_Bookings.matchid='" . $matchid . "'
			&& hlbook_Bookings.serverid=hlbook_Servers.serverid 
			&& hlbook_Bookings.logopen='1' LIMIT 1");

	my($ip,$port) = $result->fetchrow_array();

	if(!$ip || !$port) {
		$outbuffer{$client} .= message("error","could not stop disk log for $matchid, query failed");
		return 0;
	}

	if(exists($BOOKING{"$ip:$port"})) {

		if(!$SERVER{"$ip:$port"}->end_disk_log($cfg->val("logsocket","tmplogpath") . "/match_" . $matchid . ".log")) {
			$outbuffer{$client} .= message("error","could not stop disk log for $matchid");
			return 0;
		}

		if(!package_log_file("match_" . $matchid . ".log","match_" . $matchid . ".zip")) {
			$outbuffer{$client} .= message("error","could not package/move match #$matchid log file");
		}

		$outbuffer{$client}.=message("success","closed,packed,and moved log for match #$matchid");

		delete($BOOKING{"$ip:$port"});
		return 1;
	}
	else
	{
		$outbuffer{$client} .= message("error","that match does not exist or the log is not open");
		return 0;
	}
}

sub callback_open_udp_log_mirror
{
	my ($client,$server,$dest) = $cpm->return_args();
	my ($dest_ip,$dest_port) = split(":",$dest);
	my $socket;

	if(length($dest_ip) > 15 || length($dest_port) > 5) {
		$outbuffer{$client} .= "Error: Format error\n";
	}

	if(!exists($SERVER{$server})) {
		$outbuffer{$client} .="Error: That server does not exist\n";
		return 0;
	}

	unless($socket = $SERVER{$server}->create_udp_log_mirror($cfg->val("logsocket","udpbindip"),undef,$dest_ip,$dest_port)) {
		$outbuffer{$client} .= $SERVER{$server}->error();
		return 0;
	}

	$outbuffer{$client} .= "Success: created socket on " . $socket->sockhost() . ":" . $socket->sockport() . "\n";
	return 1;
}

sub callback_open_user_log_mirror
{
	my ($client,$server,$port) = $cpm->return_args();
	my $socket;

	if(!exists($SERVER{$server})) {
		$outbuffer{$client} .="Error: That server does not exist\n";
		return 0;
	}

	unless($socket = $SERVER{$server}->create_udp_log_mirror($cfg->val("logsocket","udpbindip"),undef,$client->peerhost(),$port)) {
		$outbuffer{$client} .= $SERVER{$server}->error() . "\n";
		return 0;
	}

	$outbuffer{$client} .= "Success: created log mirror socket to " . $client->peerhost() . "\n";
	return 1;

}

sub callback_open_match_log
{
	my ($client,$filename,$host) = $cpm->return_args();

	if(!exists($SERVER{$host})) {
		$outbuffer{$client} .="Error: That server does not exist\n";
		return 0;
	}

	if($SERVER{$host}->start_disk_log($cfg->val("logsocket","tmplogpath") . "/" . $filename)) {
		$outbuffer{$client} .="Log file created: $filename\n";
		return 1;
	}

	$outbuffer{$client} .= "Error: cannot create log file\n";
	return 0;
}

sub callback_close_match_log
{
	my ($client,$filename,$host) = $cpm->return_args();

	if(!$SERVER{$host}->disk_log_exists($cfg->val("logsocket","tmplogpath") . "/" . $filename)) {
		$outbuffer{$client} .= "Error: Log file does not exist: $filename\n";
		return 0;
	}

	$SERVER{$host}->end_disk_log($cfg->val("logsocket","tmplogpath") . "/" . $filename);

	$outbuffer{$client} .= "Log file closed: $filename\n";
	return 1;
}

sub callback_show
{
	my ($client,$arg) = $cpm->return_args();
	my $saddr;
	my $server;

	if($arg eq "servers")
	{
		$outbuffer{$client} .= "<serverlist>\n";
		foreach (keys(%SERVER))
		{
			$server= $SERVER{$_};
			$outbuffer{$client} .= "<server ip=\"" . $server->get('ip') . "\" port=\"" . $server->get('port') . "\"/>\n";
		}

		$outbuffer{$client} .= "</serverlist>\n";
		return 1;
	}
	elsif($arg eq "bookings") {
		$outbuffer{$client} .="<bookings>\n";

		foreach (keys(%BOOKING)) {
			$server = $BOOKING{$_};
			$outbuffer{$client} .= "<book name=\"" . $server->{'hostname'} . "\" ip=\"" . $server->{'ip'} . "\" port=\"" . $server->{'port'} . "\">\n";
		}

		$outbuffer{$client} .="</bookings>\n";
		return 1;
	}
	elsif($arg eq "conns")
	{
		$outbuffer{$client} .="<tcpconns>\n";
		foreach ($tcpselect->handles())
		{
			if($_->peerhost()) {
				$outbuffer{$client} .= "<client ip=\"" . $_->peerhost() . "\"/>\n";
			}	
		}
		$outbuffer{$client} .="</tcpconns>\n";
		return 1;
	}
	elsif($arg eq "udp")
	{
		$outbuffer{$client} .="<active_mirrors>\n";

		my $server;
		my $sockets;
		my $socket;

		foreach (keys(%SERVER))
		{
			$server = $SERVER{$_};
			$sockets = $server->get("udpsocks");

			foreach my $key (keys(%{$sockets}))
			{
				$socket = $sockets->{$key};
				$outbuffer{$client} .= "\t<udpmirror>\n";
				$outbuffer{$client} .= "\t\t<server ip=\"" . $server->get('ip') . "\" port=\"" . $server->get('port') . "\"/>\n";
				$outbuffer{$client} .= "\t\t<middleman ip=\"" . $socket->sockhost() . "\" port=\"" . $socket->sockport() . "\"/>\n";
				$outbuffer{$client} .= "\t\t<client ip=\"" . $socket->peerhost() . "\" port=\"" . $socket->peerport() . "\"/>\n";
				$outbuffer{$client} .= "\t</udpmirror>\n";
			}	
		}

		$outbuffer{$client} .="</active_mirrors>\n";
		return 1;
	}
	elsif($arg eq "lh")
	{
		my $server;
		my $handles;

		$outbuffer{$client} .="<opened_logfiles>\n";

		foreach (keys(%SERVER)) {

			$server = $SERVER{$_};
			$handles = $server->get("lh");

			foreach my $key (keys(%{$handles})) {

				$outbuffer{$client} .= "\t<disklog>\n";
				$outbuffer{$client} .= "\t\t<server ip=\"" . $server->get('ip') . "\" port=\"" . $server->get('port') . "\"/>\n";
				$outbuffer{$client} .= "\t\t<file name=\"" . $key . "\"/>\n";
				$outbuffer{$client} .= "\t</disklog>\n";
			}
		}

		$outbuffer{$client} .= "</opened_logfiles>\n";
		return 1;
	}
}

sub callback_getlogs
{
	my ($client,$arg) = $cpm->return_args();

	if(!exists($SERVER{$arg})) {
		$outbuffer{$client} .="Error: That server does not exist\n";
		return 0;
	}

	$outbuffer{$client} .="<message text=\"opened console log for " . $arg . "\"/>\n";
	$SERVER{$arg}->addclient($client);
	return 1;
}

sub callback_nologs
{
	my ($client,$arg) = $cpm->return_args();

	if(!exists($SERVER{$arg})) {
		$outbuffer{$client} .="Error: That server does not exist\n";
		return 0;
	}

	$SERVER{$arg}->remove_client($client);
}

sub callback_exit
{
	my ($client) = $cpm->return_args();

	delete $inbuffer{$client};
	delete $outbuffer{$client};
	$tcpselect->remove($client);
	close($client);
}

sub callback_help
{
	my ($client) = $cpm->return_args();
	my $cmds = $cpm->return_cmds();
	my $cmd;
	my $help;
	my $spaces;

	foreach (keys(%{$cmds})) {
		$cmd = $cmds->{$_};
		if(!$cmd->{'help'}) { next; }

		$spaces = 20 - length($cmd->{'cmd'});
		if($spaces < 1) { $spaces = 1; }
		$outbuffer{$client} .= $cmd->{'cmd'} . repeat_char(' ',$spaces) . $cmd->{'help'} . "\n";
	}
	return 1;
}
