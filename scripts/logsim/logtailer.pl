#!/usr/bin/perl -W
use strict;
use IO::Socket::INET;

my $logfile = "testlog2.log";
my $localport = 27015;
my $localip = "127.0.0.1";

my $port = 27000;
my $ip = "127.0.0.1";
my $dest = sockaddr_in($port,inet_aton($ip));

my $socket = IO::Socket::INET->new (
		Proto=>'udp',
		LocalPort=>$localport,		#where its coming from
		LocalAddr=>$localip,
		PeerPort=>$port,
		PeerHost=>$ip
	) || die ("I cant be a UDP server on port $ip:$port: $@\n");

print "Opened socket on $ip:$port\n";
open(LOGFILE,$logfile) or die("I'm to stupid to open $logfile: $!\n");
print "Opened Testlog..\n";
while(<LOGFILE>)
{
	chomp;
	print "Sending $_\n";
	$socket->send("log " . $_ . "\n",0);

	#send($socket,"log " . $_ . "\n",0,$dest);
	sleep(2);
}
