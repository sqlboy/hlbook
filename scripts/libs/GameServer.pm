package GameServer;
use strict;
use FileHandle;
use IO::Socket::INET;
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

sub new
{
	my ($class_name) = shift;
	my %params = @_;

	my ($self) = {};
	my ($clients) 			= {};
	my ($loghandles)		= {};
	my ($udpsocks)		  = {};

	bless($self, $class_name);
	bless($clients,$class_name);
	bless($loghandles,$class_name);
	bless($udpsocks,$class_name);

	$self->{'ip'} 				= $params{'Host'};
	$self->{'port'}    		= $params{'Port'};
	$self->{'error'}			= "";
	$self->{'message'}		=	"";

	$self->{'clients'}	 	= $clients;
	$self->{'lh'}					=	$loghandles;
	$self->{'udpsocks'}		= $udpsocks;

	return $self;
}

#add the lines directly into the client buffers
sub addline
{
	my ($self,$data,$buffer) = @_;
	my $handles = $self->{'lh'};
	my $fh;
	my $udpout = $self->{'udpsocks'};

	#open tcp clients
	foreach (keys(%{$self->{'clients'}})) {
		$buffer->{$_} .= $data;
	}

	#open file hanldes
	foreach (keys(%{$handles})) {
		$fh = $handles->{$_};
		print $fh $data;
	}

	#open udp sockets
	foreach (keys(%{$udpout})) {
		$udpout->{$_}->send("\xFF\xFF\xFF\xFFlog $data",0);
	}

}

sub get
{
	my ($self,$key) = @_;

	if(exists($self->{$key})) {
		return $self->{$key};
	}

	return 0;
}

##################################
# TCP Client Stuff
##################################

sub addclient
{
	my $self = shift;
	my $client = shift;

	$self->{'clients'}{$client} = 1;
	return 1;
}

sub client_exists
{
	my ($self,$client) = @_;

	if(exists($self->{'clients'}{$client})) {
		return 1;
	}

	return 0;
}

sub remove_client
{
	my ($self,$client) = @_;
	delete($self->{'clients'}{$client});
}

##################################
# Local Disk Log stuff
##################################
sub start_disk_log
{
	my ($self,$filename) = @_;
	my $handles = $self->{'lh'};

	my $fh = new FileHandle ">>$filename";
	if(defined($fh)) {
		$fh->autoflush(1);
		$handles->{$filename} = $fh;
		return 1;
	}
	return 0;
}

sub end_disk_log
{
	my ($self,$filename) = @_;
	my $handles = $self->{'lh'};

	my $fh = $handles->{$filename};
	delete($handles->{$filename});
	$fh->close();
}

sub disk_log_exists
{
	my ($self,$filename) = @_;
	my $handles = $self->{'lh'};

	if(!-e $filename) {
		return 0;
	}

	if(defined($handles->{$filename})) {
		return 1;
	}

	return 0;
}

##################################
# UDP Mirror Stuff
##################################
#setting local port to undef will randomly choose a port

sub create_udp_log_mirror
{
	my($self,$local_ip,$local_port,$dest_ip,$dest_port) = @_;
	my $udpout = $self->{'udpsocks'};

	if($local_port && $local_port > 65535) {
		$self->{'error'} = "Error creating UDP socket";
		return 0;
	}

	if($dest_port > 65535) {
		$self->{'error'} = "Error creating UDP socket";
		return 0;
	}

	my $socket = IO::Socket::INET->new (
			Proto=>'udp',
			LocalAddr=>$local_ip,
			LocalPort=>$local_port,
			PeerPort=>$dest_port,
			PeerHost=>$dest_ip
		);

	if(!$socket) {
		$self->{'error'} = "Error creating UDP socket on $local_ip";
		return 0;
	}

	$local_port = $socket->sockport();
	$udpout->{"$local_ip:$local_port"} = $socket;

	return $socket;

}

sub udp_log_mirror_exists
{
	my ($self,$local_ip,$local_port) = @_;
	my $udpout = $self->{'udpsocks'};

	if(exists($udpout->{"$local_ip:$local_port"})) {
		return 1;
	}

	return 0;
}

sub close_udp_log_mirror
{
	my ($self,$local_ip,$local_port) = @_;
	my $udpout = $self->{'udpsocks'};

	if(!$self->udp_log_mirror_exists($local_ip,$local_port)) {
		$self->{'error'} = "A UDP mirror to that address does not exist";
		return 0;
	}

	my $socket = $udpout->{"$local_ip:$local_port"};
	close($socket);
	delete($udpout->{"$local_ip:$local_port"});

	return 1;
}

sub error
{
	my $self = shift;
	return $self->{'error'};
}

sub message
{
	my $self = shift;
	return $self->{'message'};
}

1;
